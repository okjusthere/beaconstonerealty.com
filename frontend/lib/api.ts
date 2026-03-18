/**
 * API layer — calls the existing PHP backend
 * PHP backend URL is configured via API_BASE_URL env var
 */

const API_BASE = process.env.API_BASE_URL || 'https://beaconstonerealty.com';
const API_PATH = '/application/index';

interface FetchOptions {
  revalidate?: number;
  cache?: RequestCache;
}

async function apiFetch<T>(endpoint: string, params?: Record<string, string>, options?: FetchOptions): Promise<T> {
  const url = new URL(`${API_BASE}${API_PATH}/${endpoint}`);
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, value);
    });
  }

  const res = await fetch(url.toString(), {
    next: { revalidate: options?.revalidate ?? 300 }, // 5 min cache by default
    cache: options?.cache,
  });

  if (!res.ok) {
    throw new Error(`API error: ${res.status} ${res.statusText} for ${endpoint}`);
  }

  return res.json();
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
