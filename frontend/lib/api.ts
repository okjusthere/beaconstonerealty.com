/**
 * Compatibility API layer for the legacy PHP backend.
 *
 * The old endpoints are not plain REST:
 * - most detail/list endpoints expect POST bodies, not query strings
 * - requests must carry X-Requested-With: XMLHttpRequest
 * - successful responses are wrapped as { code, message, obj: { data } }
 */

const API_BASE = (process.env.API_BASE_URL || process.env.NEXT_PUBLIC_API_BASE_URL || 'https://beaconstonerealty.com')
  .replace(/\/$/, '');
const API_PATH = '/application/index';

const INTERNAL_ROUTE_PATTERNS: Array<[RegExp, string]> = [
  [/^\/index(?:\.php)?$/i, '/'],
  [/^\/propertyCenter\/\d+$/i, '/properties'],
  [/^\/propertyCenterDetail\/(\d+)$/i, '/properties/$1'],
  [/^\/realEstateBrokerCenter\/\d+$/i, '/brokers'],
  [/^\/realEstateBrokerDetail\/(\d+)$/i, '/brokers/$1'],
  [/^\/sale\/\d+$/i, '/sell-with-us'],
  [/^\/contact\/\d+$/i, '/contact'],
  [/^\/joinUs\/(\d+)$/i, '/joinUs/$1'],
];

interface FetchOptions {
  revalidate?: number;
  cache?: RequestCache;
  method?: 'GET' | 'POST';
  cookieHeader?: string;
}

interface LegacyEnvelope<T> {
  code: number;
  message: string;
  obj?: {
    data?: T;
  };
}

function isAbsoluteUrl(value: string): boolean {
  return /^[a-z][a-z\d+\-.]*:/i.test(value) || value.startsWith('//');
}

function isNonHttpHref(value: string): boolean {
  return value.startsWith('#') || value.startsWith('mailto:') || value.startsWith('tel:') || value.startsWith('javascript:');
}

export function resolveAssetUrl(value: string): string {
  if (!value || isAbsoluteUrl(value) || isNonHttpHref(value)) {
    return value;
  }
  if (value.startsWith('/')) {
    return `${API_BASE}${value}`;
  }
  return value;
}

export function normalizeSitePath(value: string): string {
  if (!value || isAbsoluteUrl(value) || isNonHttpHref(value)) {
    return value;
  }

  const [pathname, queryString] = value.split('?');
  const normalizedPath = INTERNAL_ROUTE_PATTERNS.reduce((result, [pattern, replacement]) => {
    if (result !== pathname || !pattern.test(pathname)) {
      return result;
    }
    return pathname.replace(pattern, replacement);
  }, pathname);

  return queryString ? `${normalizedPath}?${queryString}` : normalizedPath;
}

function rewriteHtmlAssetUrls(html: string): string {
  if (!html) return html;

  return html
    .replace(
    /(src|poster)=["'](\/[^"']+)["']/gi,
    (_match, attribute: string, assetPath: string) => `${attribute}="${resolveAssetUrl(assetPath)}"`,
    )
    .replace(/(<video\b[^>]*>)([^<]*)(<\/video>)/gi, (_match, startTag: string, _text: string, endTag: string) => `${startTag}${endTag}`);
}

function normalizeLegacyData<T>(value: T, key?: string): T {
  if (Array.isArray(value)) {
    return value.map((item) => normalizeLegacyData(item, key)) as T;
  }

  if (value && typeof value === 'object') {
    const normalizedEntries = Object.entries(value).map(([entryKey, entryValue]) => [
      entryKey,
      normalizeLegacyData(entryValue, entryKey),
    ]);
    return Object.fromEntries(normalizedEntries) as T;
  }

  if (typeof value === 'string') {
    if (key === 'thumbnail' || key === 'path') {
      return resolveAssetUrl(value) as T;
    }
    if (key === 'photo_album' || key === 'banner') {
      return resolveAssetUrl(value) as T;
    }
    if (key === 'url') {
      return normalizeSitePath(value) as T;
    }
    if (key === 'content') {
      return rewriteHtmlAssetUrls(value) as T;
    }
  }

  return value;
}

async function fetchLegacyResponse<T>(
  endpoint: string,
  params?: Record<string, string>,
  options?: FetchOptions,
): Promise<{ payload: LegacyEnvelope<T>; response: Response }> {
  const method = options?.method ?? (params && Object.keys(params).length > 0 ? 'POST' : 'GET');
  const url = `${API_BASE}${API_PATH}/${endpoint}`;
  const headers: HeadersInit = {
    'X-Requested-With': 'XMLHttpRequest',
  };

  const requestInit: RequestInit & { next?: { revalidate: number } } = {
    method,
    headers,
    cache: options?.cache,
  };

  if (options?.cookieHeader) {
    headers.Cookie = options.cookieHeader;
  }

  if (method === 'POST') {
    headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    requestInit.body = new URLSearchParams(params ?? {}).toString();
  } else {
    requestInit.next = { revalidate: options?.revalidate ?? 300 };
  }

  const response = await fetch(url, requestInit);

  if (!response.ok) {
    throw new Error(`API error: ${response.status} ${response.statusText} for ${endpoint}`);
  }

  return {
    payload: await response.json() as LegacyEnvelope<T>,
    response,
  };
}

async function apiFetch<T>(endpoint: string, params?: Record<string, string>, options?: FetchOptions): Promise<T> {
  const { payload } = await fetchLegacyResponse<T>(endpoint, params, options);

  if (payload.code !== 200) {
    throw new Error(payload.message || `Legacy API error for ${endpoint}`);
  }

  return normalizeLegacyData((payload.obj?.data ?? null) as T);
}

export async function proxyLegacyRequest<T>(
  endpoint: string,
  params?: Record<string, string>,
  options?: FetchOptions,
): Promise<LegacyEnvelope<T>> {
  const { payload } = await fetchLegacyResponse<T>(endpoint, params, options);
  return {
    ...payload,
    obj: payload.obj
      ? { data: normalizeLegacyData((payload.obj.data ?? null) as T) }
      : payload.obj,
  };
}

export async function proxyLegacyRequestRaw<T>(
  endpoint: string,
  params?: Record<string, string>,
  options?: FetchOptions,
): Promise<{ payload: LegacyEnvelope<T>; setCookie: string | null }> {
  const { payload, response } = await fetchLegacyResponse<T>(endpoint, params, options);
  return {
    payload: {
      ...payload,
      obj: payload.obj
        ? { data: normalizeLegacyData((payload.obj.data ?? null) as T) }
        : payload.obj,
    },
    setCookie: response.headers.get('set-cookie'),
  };
}

// ---------- Types ----------

export interface WebInfo {
  company: string;
  address: string;
  phone: string;
  mobile: string;
  email: string;
  fax: string;
  contact: string;
  qq: string;
  wechat: string;
  whatsapp: string;
  zip: string;
  icp: string;
  icp_police: string;
  weburl: string;
  map: string;
}

export interface MenuItem {
  id: number;
  parentid: number;
  type: number;
  link_id: number;
  title: string;
  sub_title: string;
  url: string;
  remarks: string;
  thumbnail: string;
  banner: string[];
  is_show: boolean;
  children: MenuItem[];
}

export interface PicItem {
  id: number;
  classid: number;
  path: string;
  name: string;
  url: string;
  remarks: string;
}

export interface NewsItem {
  id: number;
  title: string;
  url: string;
  keywords: string;
  description: string;
  thumbnail: string;
  content: string;
  enclosure: string;
  photo_album: string[];
  add_time: number;
  field?: Record<string, string>;
}

export interface NewsClass {
  id: number;
  parentid: number;
  title: string;
  description: string;
  thumbnail: string;
  url: string;
  children: NewsClass[];
}

export interface ProductItem {
  id: number;
  title: string;
  url: string;
  specifications: string;
  origin: string;
  price: string;
  keywords: string;
  description: string;
  thumbnail: string;
  photo_album: string[];
  add_time: number;
}

export interface ProductClass {
  id: number;
  parentid: number;
  title: string;
  description: string;
  thumbnail: string;
  url: string;
  children: ProductClass[];
}

export interface GlobalData {
  web_control: { state: number; tips: string };
  web_info: WebInfo;
  web_code: Array<{ state: number; code: string }>;
  pic_info: PicItem[];
  menu_info: MenuItem[];
  news_class_info: NewsClass[];
  product_class_info: ProductClass[];
  customer_service: Array<{ key: string; value: string }>;
  links_info: Array<{ id: number; title: string; url: string; thumbnail: string }>;
  links_class_info: Array<{ id: number; title: string; thumbnail: string }>;
}

// ---------- API Functions ----------

/** Get all global site data (nav, web info, pics, etc.) */
export async function getGlobalData(): Promise<GlobalData> {
  return apiFetch<GlobalData>('global.php');
}

/** Get a single news/article detail by ID */
export async function getNewsDetail(id: number): Promise<NewsItem> {
  return apiFetch<NewsItem>('news_detail.php', { id: String(id) });
}

/** Get news/article list by class ID */
export async function getNewsList(classId: number, top: number = -1, showType: number = 1): Promise<NewsItem[]> {
  return apiFetch<NewsItem[]>('news_list.php', {
    id: String(classId),
    top: String(top),
    type: String(showType),
  });
}

/** Get news class list */
export async function getNewsClassList(parentId: number = 0): Promise<NewsClass[]> {
  return apiFetch<NewsClass[]>('inner_newsclass.php', { id: String(parentId) });
}

/** Get product/property detail by ID */
export async function getProductDetail(id: number): Promise<ProductItem> {
  return apiFetch<ProductItem>('product_detail.php', { id: String(id) });
}

/** Get product/property list by class ID */
export async function getProductList(classId: number = 0, top: number = -1): Promise<ProductItem[]> {
  return apiFetch<ProductItem[]>('product_list.php', {
    id: String(classId),
    top: String(top),
  });
}

/** Get news items for inner page display */
export async function getInnerNews(classId: number, page: number = 1): Promise<{ list: NewsItem[]; total: number }> {
  return apiFetch('inner_news.php', {
    id: String(classId),
    page: String(page),
  });
}

/** Submit contact/message form */
export async function submitMessage(formData: Record<string, string>): Promise<{ code: number; msg: string }> {
  const url = `${API_BASE}${API_PATH}/inner_message.php`;
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(formData).toString(),
  });
  return res.json();
}

/** Get product attributes/filters */
export async function getProductAttributes(classId: number): Promise<Record<string, string[]>> {
  return apiFetch('product_attribute.php', { id: String(classId) });
}

/** Helper: get a specific pic by classid from global data */
export function getPicByClassId(pics: PicItem[] | undefined | null, classId: number): PicItem | undefined {
  if (!pics || !Array.isArray(pics)) return undefined;
  return pics.find(p => p.classid === classId);
}

/** Helper: find menu item by path */
export function findMenuByPath(menus: MenuItem[] | undefined | null, path: string): MenuItem | undefined {
  if (!menus || !Array.isArray(menus)) return undefined;
  for (const menu of menus) {
    if (menu.url === path) return menu;
    if (menu.children && menu.children.length > 0) {
      const found = findMenuByPath(menu.children, path);
      if (found) return found;
    }
  }
  return undefined;
}

/** Helper: find menu item by ID */
export function findMenuById(menus: MenuItem[] | undefined | null, id: number): MenuItem | undefined {
  if (!menus || !Array.isArray(menus)) return undefined;
  for (const menu of menus) {
    if (menu.id === id) return menu;
    if (menu.children && menu.children.length > 0) {
      const found = findMenuById(menu.children, id);
      if (found) return found;
    }
  }
  return undefined;
}
