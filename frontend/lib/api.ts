import siteContentRaw from '@/data/site-content.json';

const INTERNAL_ROUTE_PATTERNS: Array<[RegExp, string]> = [
  [/^\/index(?:\.php)?$/i, '/'],
  [/^\/propertyCenter\/\d+$/i, '/properties'],
  [/^\/propertyCenterDetail\/(\d+)$/i, '/properties/$1'],
  [/^\/realEstateBrokerCenter\/\d+$/i, '/brokers'],
  [/^\/realEstateBrokerDetail\/(\d+)$/i, '/brokers/$1'],
  [/^\/sale\/\d+$/i, '/sell-with-us'],
  [/^\/contact\/\d+$/i, '/contact'],
  [/^\/joinUs\/\d+$/i, '/join'],
  [/^\/about\/\d+$/i, '/about'],
  [/^\/legal\/\d+$/i, '/legal'],
];

const LOCAL_SITE_HOST_PATTERN = /^https?:\/\/(?:www\.)?beaconstonerealty\.com/i;
const LOCAL_DEV_HOST_PATTERN = /^https?:\/\/127\.0\.0\.1(?::\d+)?/i;
const NUMERIC_KEYS = new Set([
  'id',
  'parentid',
  'type',
  'link_id',
  'add_time',
  'view',
  'show_type',
  'state',
  'classid',
]);

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
  classid?: number[];
  title: string;
  url: string;
  keywords: string;
  description: string;
  thumbnail: string;
  content: string;
  enclosure: string;
  photo_album: string[];
  add_time: number;
  view?: number;
  field?: Record<string, string>;
}

export interface NewsClass {
  id: number;
  parentid: number;
  title: string;
  description: string;
  thumbnail: string;
  banner?: string[];
  content?: string;
  url: string;
  children: NewsClass[];
}

export interface ProductItem {
  id: number;
  classid?: number[];
  title: string;
  url: string;
  specifications: string;
  origin: string;
  price: string;
  keywords: string;
  description: string;
  thumbnail: string;
  enclosure?: string;
  photo_album: string[];
  add_time: number;
  field?: Record<string, string>;
}

export interface ProductClass {
  id: number;
  parentid: number;
  title: string;
  description: string;
  thumbnail: string;
  banner?: string[];
  content?: string;
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
  customer_service: Array<{ key: string; value: string; description?: string; type?: string; state?: boolean }>;
  links_info: Array<{ id: number; title: string; url: string; thumbnail: string }>;
  links_class_info: Array<{ id: number; title: string; thumbnail: string }>;
}

interface StaticSiteContent {
  generatedAt: string;
  sourceSiteUrl: string;
  globalData: GlobalData;
  newsById: Record<string, NewsItem>;
  newsListsByClassId: Record<string, NewsItem[]>;
  productListsByClassId: Record<string, ProductItem[]>;
}

function isAbsoluteUrl(value: string): boolean {
  return /^[a-z][a-z\d+\-.]*:/i.test(value) || value.startsWith('//');
}

function isNonHttpHref(value: string): boolean {
  return value.startsWith('#') || value.startsWith('mailto:') || value.startsWith('tel:') || value.startsWith('javascript:');
}

function stripLegacyOrigin(value: string): string {
  if (!value) return value;
  if (LOCAL_SITE_HOST_PATTERN.test(value)) {
    return value.replace(LOCAL_SITE_HOST_PATTERN, '');
  }
  if (LOCAL_DEV_HOST_PATTERN.test(value)) {
    return value.replace(LOCAL_DEV_HOST_PATTERN, '');
  }
  return value;
}

function normalizeLegacyHtmlRoute(value: string): string | null {
  const strippedValue = stripLegacyOrigin(value);

  if (!strippedValue || isAbsoluteUrl(strippedValue) || isNonHttpHref(strippedValue)) {
    return null;
  }

  try {
    const parsed = new URL(strippedValue, 'https://static.local');
    const pathname = parsed.pathname.toLowerCase();
    const id = parsed.searchParams.get('id');

    switch (pathname) {
      case '/':
        return null;
      case '/index.html':
      case '/home.html':
        return '/';
      case '/about.html':
        return id ? `/about/${id}` : null;
      case '/joinus.html':
        return id ? `/joinUs/${id}` : null;
      case '/contact.html':
        return id ? `/contact/${id}` : '/contact';
      case '/sale.html':
        return id ? `/sale/${id}` : '/sell-with-us';
      case '/page.html':
        return id ? `/page/${id}` : null;
      case '/propertycenter.html':
        return id ? `/propertyCenter/${id}` : '/properties';
      case '/propertycenterdetail.html':
        return id ? `/propertyCenterDetail/${id}` : null;
      case '/realestatebrokercenter.html':
        return id ? `/realEstateBrokerCenter/${id}` : '/brokers';
      case '/realestatebrokerdetail.html':
        return id ? `/realEstateBrokerDetail/${id}` : null;
      default:
        return null;
    }
  } catch {
    return null;
  }
}

export function resolveAssetUrl(value: string): string {
  if (!value || isNonHttpHref(value)) {
    return value;
  }

  const strippedValue = stripLegacyOrigin(value);

  if (isAbsoluteUrl(strippedValue)) {
    return strippedValue;
  }

  if (strippedValue.startsWith('/')) {
    return strippedValue;
  }

  return value;
}

export function normalizeSitePath(value: string): string {
  if (!value || isNonHttpHref(value)) {
    return value;
  }

  const strippedValue = stripLegacyOrigin(value);
  if (isAbsoluteUrl(strippedValue)) {
    return strippedValue;
  }

  const normalizedHtmlRoute = normalizeLegacyHtmlRoute(strippedValue);
  if (normalizedHtmlRoute) {
    return normalizeSitePath(normalizedHtmlRoute);
  }

  const [pathname, queryString] = strippedValue.split('?');
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
      /(src|poster)=["']([^"']+)["']/gi,
      (_match, attribute: string, assetPath: string) => `${attribute}="${resolveAssetUrl(assetPath)}"`,
    )
    .replace(
      /(href)=["']([^"']+)["']/gi,
      (_match, attribute: string, hrefValue: string) => `${attribute}="${normalizeSitePath(hrefValue)}"`,
    )
    .replace(/(<video\b[^>]*>)([^<]*)(<\/video>)/gi, (_match, startTag: string, _text: string, endTag: string) => `${startTag}${endTag}`);
}

function normalizeSnapshotValue<T>(value: T, key?: string): T {
  if (Array.isArray(value)) {
    return value.map((item) => normalizeSnapshotValue(item, key)) as T;
  }

  if (value && typeof value === 'object') {
    const normalizedEntries = Object.entries(value).map(([entryKey, entryValue]) => [
      entryKey,
      normalizeSnapshotValue(entryValue, entryKey),
    ]);
    return Object.fromEntries(normalizedEntries) as T;
  }

  if (typeof value === 'string') {
    if (key && NUMERIC_KEYS.has(key) && /^-?\d+$/.test(value)) {
      return Number(value) as T;
    }
    if (key === 'thumbnail' || key === 'path' || key === 'enclosure' || key === 'weburl') {
      return resolveAssetUrl(value) as T;
    }
    if (key === 'photo_album' || key === 'banner') {
      return resolveAssetUrl(value) as T;
    }
    if (key === 'url') {
      return normalizeSitePath(value) as T;
    }
    if (key === 'content' || key === 'description') {
      return rewriteHtmlAssetUrls(value) as T;
    }
  }

  return value;
}

const siteContent = normalizeSnapshotValue(siteContentRaw as unknown) as StaticSiteContent;

function cloneValue<T>(value: T): T {
  return structuredClone(value);
}

function flattenMenus(menus: MenuItem[]): MenuItem[] {
  return menus.flatMap((menu) => [menu, ...flattenMenus(menu.children || [])]);
}

function extractRouteIds(urls: string[], prefix: string): string[] {
  const ids = new Set<string>();

  for (const url of urls) {
    if (!url.startsWith(prefix)) {
      continue;
    }

    const id = url.slice(prefix.length).split(/[/?#]/)[0];
    if (id) {
      ids.add(id);
    }
  }

  return Array.from(ids).sort((a, b) => Number(a) - Number(b));
}

export function getMenuRouteIds(prefix: string): string[] {
  return extractRouteIds(flattenMenus(siteContent.globalData.menu_info).map((item) => item.url), prefix);
}

export function getNewsRouteIds(prefix: string): string[] {
  return extractRouteIds(Object.values(siteContent.newsById).map((item) => item.url), prefix);
}

export function getAllNewsDetails(): NewsItem[] {
  return cloneValue(Object.values(siteContent.newsById).sort((left, right) => left.id - right.id));
}

export async function getGlobalData(): Promise<GlobalData> {
  return cloneValue(siteContent.globalData);
}

export async function getNewsDetail(id: number): Promise<NewsItem> {
  const item = siteContent.newsById[String(id)];

  if (!item) {
    throw new Error(`Static content missing news detail for ID ${id}`);
  }

  return cloneValue(item);
}

export async function getNewsList(classId: number, top: number = -1, showType: number = 1): Promise<NewsItem[]> {
  void showType;
  const list = siteContent.newsListsByClassId[String(classId)] || [];
  return cloneValue(top > 0 ? list.slice(0, top) : list);
}

export async function getNewsClassList(parentId: number = 0): Promise<NewsClass[]> {
  const classes = parentId === 0
    ? siteContent.globalData.news_class_info
    : flattenNewsClasses(siteContent.globalData.news_class_info).filter((item) => item.parentid === parentId);

  return cloneValue(classes);
}

function flattenNewsClasses(classes: NewsClass[]): NewsClass[] {
  return classes.flatMap((item) => [item, ...flattenNewsClasses(item.children || [])]);
}

export async function getProductDetail(id: number): Promise<ProductItem> {
  const item = (siteContent.productListsByClassId.all || []).find((entry) => entry.id === id);

  if (!item) {
    throw new Error(`Static content missing product detail for ID ${id}`);
  }

  return cloneValue(item);
}

export async function getProductList(classId: number = 0, top: number = -1): Promise<ProductItem[]> {
  const key = classId > 0 ? String(classId) : 'all';
  const list = siteContent.productListsByClassId[key] || [];
  return cloneValue(top > 0 ? list.slice(0, top) : list);
}

export async function getInnerNews(classId: number, page: number = 1): Promise<{ list: NewsItem[]; total: number }> {
  const list = await getNewsList(classId);
  const pageSize = 10;
  const startIndex = Math.max(page - 1, 0) * pageSize;

  return {
    list: list.slice(startIndex, startIndex + pageSize),
    total: list.length,
  };
}

export async function submitMessage(): Promise<{ code: number; msg: string }> {
  return {
    code: 501,
    msg: 'Static export does not support direct form submission.',
  };
}

export async function getProductAttributes(): Promise<Record<string, string[]>> {
  return {};
}

export function getPicByClassId(pics: PicItem[] | undefined | null, classId: number): PicItem | undefined {
  if (!pics || !Array.isArray(pics)) return undefined;
  return pics.find((item) => item.classid === classId);
}

export function findMenuByPath(menus: MenuItem[] | undefined | null, path: string): MenuItem | undefined {
  if (!menus || !Array.isArray(menus)) return undefined;

  for (const menu of menus) {
    if (menu.url === path) {
      return menu;
    }

    if (menu.children && menu.children.length > 0) {
      const found = findMenuByPath(menu.children, path);
      if (found) {
        return found;
      }
    }
  }

  return undefined;
}

export function findMenuById(menus: MenuItem[] | undefined | null, id: number): MenuItem | undefined {
  if (!menus || !Array.isArray(menus)) return undefined;

  for (const menu of menus) {
    if (menu.id === id) {
      return menu;
    }

    if (menu.children && menu.children.length > 0) {
      const found = findMenuById(menu.children, id);
      if (found) {
        return found;
      }
    }
  }

  return undefined;
}
