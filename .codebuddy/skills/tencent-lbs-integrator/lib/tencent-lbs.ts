/**
 * 腾讯位置服务 (LBS) SDK 封装
 * 
 * 提供两种地图展示方式：
 * 1. JavaScript GL API - 动态交互地图（推荐）
 * 2. WebService API - 静态地图、地址解析、POI搜索等
 * 
 * **重要**: 
 * - JavaScript GL API 需要引入外部脚本
 * - WebService API 使用 JSONP 方式解决跨域问题
 * 
 * @example
 * // 动态地图
 * await loadTMapGL();
 * const map = createMap('container', { center: { lat: 39.984120, lng: 116.307484 } });
 * addMarker(map, { lat: 39.984120, lng: 116.307484 }, '会议地点');
 * 
 * // WebService API
 * const client = createClient();
 * const result = await client.geocode('北京市海淀区中关村大街1号');
 */

// ============ 常量配置 ============

/** 硬编码的 Web 端 API Key（需要在控制台配置域名白名单） */
const TENCENT_LBS_KEY = 'CRABZ-EOBK7-SS4XQ-P746J-CDYS6-O4F3O';

/** WebService API 基础 URL */
const BASE_URL = 'https://apis.map.qq.com';

/** JavaScript GL API 脚本 URL */
const GLJS_URL = 'https://map.qq.com/api/gljs';

/** JavaScript GL API 版本 */
const GLJS_VERSION = '1.exp';

/** JSONP 回调计数器 */
let jsonpCallbackCounter = 0;

/** GL API 加载状态 */
let glApiLoaded = false;
let glApiLoading: Promise<void> | null = null;

// ============ 错误码定义 ============

/**
 * 腾讯位置服务 API 错误码
 */
export const LBS_ERROR_CODES = {
  /** 成功 */
  SUCCESS: 0,
  /** 请求来源未被授权 - 需要在控制台配置域名白名单 */
  UNAUTHORIZED_REFERER: 110,
  /** IP 未被授权 */
  UNAUTHORIZED_IP: 112,
  /** 此 Key 未开启 WebService 功能 */
  WEBSERVICE_NOT_ENABLED: 120,
  /** 此功能未被授权 - 需要在控制台申请开通 */
  FEATURE_NOT_AUTHORIZED: 121,
  /** 配额不足 */
  QUOTA_EXCEEDED: 122,
  /** Key 已被停用 */
  KEY_DISABLED: 311,
} as const;

/**
 * LBS API 错误类
 */
export class LBSError extends Error {
  constructor(
    /** 错误状态码 */
    public readonly status: number,
    /** 错误消息 */
    message: string,
    /** 请求来源（如域名或 IP） */
    public readonly requestSource?: string
  ) {
    super(message);
    this.name = 'LBSError';
  }

  /**
   * 获取错误解决建议
   */
  getSolution(): string {
    switch (this.status) {
      case LBS_ERROR_CODES.UNAUTHORIZED_REFERER:
        return `请求来源域名未被授权。请在腾讯位置服务控制台配置域名白名单，添加当前域名: ${this.requestSource || window?.location?.hostname || '未知'}`;
      case LBS_ERROR_CODES.UNAUTHORIZED_IP:
        return '请求 IP 未被授权。请在控制台配置授权 IP 或使用域名白名单方式。';
      case LBS_ERROR_CODES.WEBSERVICE_NOT_ENABLED:
        return '此 Key 未开启 WebService 功能。请在控制台 Key 设置页面勾选 WebService 选项。';
      case LBS_ERROR_CODES.FEATURE_NOT_AUTHORIZED:
        return '此功能未被授权。请在控制台「申请配额」页面提交开通权限申请。';
      case LBS_ERROR_CODES.QUOTA_EXCEEDED:
        return '请求配额已用尽。请在控制台申请更高配额或稍后重试。';
      case LBS_ERROR_CODES.KEY_DISABLED:
        return 'Key 已被停用。请在控制台查看 Key 状态，如需继续使用可点击「恢复」按钮。';
      default:
        return '请检查请求参数或联系技术支持。';
    }
  }
}

// ============ 类型定义 ============

/**
 * 坐标位置
 */
export interface Location {
  /** 纬度 */
  lat: number;
  /** 经度 */
  lng: number;
}

/**
 * 行政区划信息
 */
export interface AdInfo {
  /** 国家 */
  nation?: string;
  /** 国家代码 */
  nation_code?: string;
  /** 省份 */
  province?: string;
  /** 城市 */
  city?: string;
  /** 区县 */
  district?: string;
  /** 行政区划代码 */
  adcode?: number | string;
  /** 城市代码 */
  city_code?: string;
}

/**
 * POI（兴趣点）搜索结果
 */
export interface POI {
  /** 唯一标识 */
  id: string;
  /** 名称 */
  title: string;
  /** 地址 */
  address: string;
  /** 电话 */
  tel?: string;
  /** 分类 */
  category?: string;
  /** 类型：0-普通POI，1-公交站，2-地铁站，3-公交线路，4-行政区划 */
  type?: number;
  /** 坐标 */
  location: Location;
  /** 行政区划信息 */
  ad_info?: AdInfo;
  /** 距离（米） */
  _distance?: number;
}

/**
 * 地点搜索选项
 */
export interface SearchPlaceOptions {
  /** 
   * 搜索边界，支持以下格式：
   * - region(城市名[,auto_extend][,lat,lng]) - 城市/区域搜索
   * - nearby(lat,lng,radius[,auto_extend]) - 周边搜索
   * - rectangle(lat1,lng1,lat2,lng2) - 矩形范围搜索
   */
  boundary: string;
  /** 每页条数，最大20，默认10 */
  pageSize?: number;
  /** 页码，从1开始 */
  pageIndex?: number;
  /** 筛选条件，如 category=公交站 */
  filter?: string;
  /** 排序方式，如 _distance（按距离排序） */
  orderby?: string;
}

/**
 * 地点搜索响应
 */
export interface SearchPlaceResponse {
  /** 状态码，0表示成功 */
  status: number;
  /** 状态说明 */
  message: string;
  /** 结果总数 */
  count: number;
  /** POI 数据列表 */
  data: POI[];
}

/**
 * 地址解析响应
 */
export interface GeocodeResponse {
  /** 状态码，0表示成功 */
  status: number;
  /** 状态说明 */
  message: string;
  /** 解析结果 */
  result: {
    /** 标题（已废弃） */
    title?: string;
    /** 解析到的坐标 */
    location: Location;
    /** 地址组成部分 */
    address_components: {
      province: string;
      city: string;
      district: string;
      street: string;
      street_number: string;
    };
    /** 行政区划信息 */
    ad_info: {
      adcode: string;
    };
    /** 可信度，1-10，>=7 较准确 */
    reliability: number;
    /** 解析精度级别，1-11，>=9 精度较高 */
    level: number;
  };
}

/**
 * 逆地址解析选项
 */
export interface ReverseGeocodeOptions {
  /** 是否返回周边 POI：0-不返回（默认），1-返回 */
  getPoi?: 0 | 1;
  /** POI 选项，多个参数用分号分隔 */
  poiOptions?: string;
}

/**
 * 逆地址解析响应
 */
export interface ReverseGeocodeResponse {
  /** 状态码，0表示成功 */
  status: number;
  /** 状态说明 */
  message: string;
  /** 解析结果 */
  result: {
    /** 标准格式化地址 */
    address: string;
    /** 格式化地址 */
    formatted_addresses?: {
      recommend: string;
      rough: string;
    };
    /** 地址组成部分 */
    address_component: {
      nation: string;
      province: string;
      city: string;
      district: string;
      street: string;
      street_number: string;
    };
    /** 行政区划信息 */
    ad_info: AdInfo;
    /** 周边 POI 数量 */
    poi_count?: number;
    /** 周边 POI 列表 */
    pois?: POI[];
  };
}

/**
 * IP 定位响应
 */
export interface IPLocationResponse {
  /** 状态码，0表示成功 */
  status: number;
  /** 状态说明 */
  message: string;
  /** 定位结果 */
  result: {
    ip: string;
    location: Location;
    ad_info: AdInfo;
  };
}

/**
 * 静态地图标注点
 */
export interface StaticMapMarker {
  /** 标注位置 */
  position: Location;
  /** 标注标签（单个字母或数字） */
  label?: string;
  /** 标注颜色 */
  color?: 'blue' | 'red' | 'green' | 'yellow' | 'purple' | 'orange';
}

/**
 * 静态地图选项
 */
export interface StaticMapOptions {
  /** 地图中心点坐标 */
  center?: Location;
  /** 缩放级别，范围 4-18 */
  zoom?: number;
  /** 图片尺寸 */
  size: {
    width: number;
    height: number;
  };
  /** 地图类型 */
  maptype?: 'roadmap' | 'satellite' | 'hybrid';
  /** 清晰度：1-普清，2-高清 */
  scale?: 1 | 2;
  /** 图片格式 */
  format?: 'png' | 'png8' | 'gif' | 'jpg';
  /** 标注点列表 */
  markers?: StaticMapMarker[];
}

/**
 * 客户端配置选项
 */
export interface ClientOptions {
  /** API Key，默认使用内置 Key */
  key?: string;
  /** 请求超时时间（毫秒），默认 10000 */
  timeout?: number;
}

// ============ JavaScript GL API 类型定义 ============

/**
 * 地图配置选项
 */
export interface MapOptions {
  /** 地图中心点坐标 */
  center: Location;
  /** 缩放级别，范围 3-20 */
  zoom?: number;
  /** 俯仰角度，范围 0-80 */
  pitch?: number;
  /** 旋转角度，范围 0-360 */
  rotation?: number;
  /** 是否显示地图控件 */
  showControl?: boolean;
}

/**
 * 标注点样式配置
 */
export interface MarkerStyleOptions {
  /** 宽度（像素） */
  width?: number;
  /** 高度（像素） */
  height?: number;
  /** 图标 URL */
  src?: string;
  /** 锚点位置 */
  anchor?: { x: number; y: number };
}

/**
 * 标注点数据
 */
export interface MarkerGeometry {
  /** 唯一标识 */
  id: string;
  /** 样式 ID */
  styleId?: string;
  /** 坐标位置 */
  position: Location;
  /** 自定义属性 */
  properties?: Record<string, unknown>;
}

/**
 * 信息窗口配置
 */
export interface InfoWindowOptions {
  /** 显示内容（支持 HTML） */
  content: string;
  /** 显示位置 */
  position: Location;
  /** 偏移量 */
  offset?: { x: number; y: number };
}

// ============ TMap 全局类型声明 ============

declare global {
  interface Window {
    TMap: {
      LatLng: new (lat: number, lng: number) => TMapLatLng;
      Map: new (container: HTMLElement, options: TMapMapOptions) => TMapInstance;
      MultiMarker: new (options: TMapMultiMarkerOptions) => TMapMultiMarker;
      MarkerStyle: new (options: TMapMarkerStyleOptions) => TMapMarkerStyle;
      InfoWindow: new (options: TMapInfoWindowOptions) => TMapInfoWindow;
      MultiLabel: new (options: TMapMultiLabelOptions) => TMapMultiLabel;
      LabelStyle: new (options: TMapLabelStyleOptions) => TMapLabelStyle;
    };
  }
}

interface TMapLatLng {
  lat: number;
  lng: number;
  toString(): string;
}

interface TMapMapOptions {
  center: TMapLatLng;
  zoom?: number;
  pitch?: number;
  rotation?: number;
}

interface TMapInstance {
  setCenter(center: TMapLatLng): void;
  setZoom(zoom: number): void;
  setPitch(pitch: number): void;
  setRotation(rotation: number): void;
  destroy(): void;
  on(event: string, handler: (evt: TMapEvent) => void): void;
  off(event: string, handler: (evt: TMapEvent) => void): void;
}

interface TMapEvent {
  geometry?: {
    id: string;
    position: TMapLatLng;
    properties?: Record<string, unknown>;
  };
  latLng?: TMapLatLng;
}

interface TMapMarkerStyleOptions {
  width?: number;
  height?: number;
  src?: string;
  anchor?: { x: number; y: number };
}

interface TMapMarkerStyle {}

interface TMapMultiMarkerOptions {
  id?: string;
  map: TMapInstance;
  styles?: Record<string, TMapMarkerStyle>;
  geometries?: TMapMarkerGeometry[];
}

interface TMapMarkerGeometry {
  id: string;
  styleId?: string;
  position: TMapLatLng;
  properties?: Record<string, unknown>;
}

interface TMapMultiMarker {
  add(geometries: TMapMarkerGeometry[]): void;
  remove(ids: string[]): void;
  updateGeometries(geometries: TMapMarkerGeometry[]): void;
  setGeometries(geometries: TMapMarkerGeometry[]): void;
  on(event: string, handler: (evt: TMapEvent) => void): void;
  off(event: string, handler: (evt: TMapEvent) => void): void;
}

interface TMapInfoWindowOptions {
  map: TMapInstance;
  position: TMapLatLng;
  content?: string;
  offset?: { x: number; y: number };
}

interface TMapInfoWindow {
  open(): void;
  close(): void;
  setPosition(position: TMapLatLng): void;
  setContent(content: string): void;
}

interface TMapLabelStyleOptions {
  color?: string;
  size?: number;
  offset?: { x: number; y: number };
  angle?: number;
  alignment?: string;
  verticalAlignment?: string;
}

interface TMapLabelStyle {}

interface TMapMultiLabelOptions {
  id?: string;
  map: TMapInstance;
  styles?: Record<string, TMapLabelStyle>;
  geometries?: TMapLabelGeometry[];
}

interface TMapLabelGeometry {
  id: string;
  styleId?: string;
  position: TMapLatLng;
  content: string;
}

interface TMapMultiLabel {
  add(geometries: TMapLabelGeometry[]): void;
  remove(ids: string[]): void;
  setGeometries(geometries: TMapLabelGeometry[]): void;
}

// ============ JavaScript GL API 实现 ============

/**
 * 加载腾讯地图 JavaScript GL API
 * 
 * 在使用动态地图功能前必须先调用此函数
 * 
 * @param options - 加载选项
 * @returns Promise，加载完成后 resolve
 * 
 * @example
 * await loadTMapGL();
 * // 现在可以使用 TMap 全局对象
 */
export async function loadTMapGL(options?: {
  /** API Key，默认使用内置 Key */
  key?: string;
  /** 附加库，如 'visualization,tools' */
  libraries?: string;
}): Promise<void> {
  // 如果已加载，直接返回
  if (glApiLoaded && window.TMap) {
    return;
  }
  
  // 如果正在加载，等待加载完成
  if (glApiLoading) {
    return glApiLoading;
  }
  
  // 开始加载
  glApiLoading = new Promise((resolve, reject) => {
    const key = options?.key ?? TENCENT_LBS_KEY;
    
    // 构建脚本 URL
    let scriptUrl = `${GLJS_URL}?v=${GLJS_VERSION}&key=${key}`;
    if (options?.libraries) {
      scriptUrl += `&libraries=${options.libraries}`;
    }
    
    // 创建 script 元素
    const script = document.createElement('script');
    script.src = scriptUrl;
    script.async = true;
    script.charset = 'utf-8';
    
    script.onload = () => {
      if (window.TMap) {
        glApiLoaded = true;
        resolve();
      } else {
        reject(new LBSError(-3, '腾讯地图 GL API 加载失败：TMap 对象未定义'));
      }
    };
    
    script.onerror = () => {
      glApiLoading = null;
      reject(new LBSError(-2, '腾讯地图 GL API 脚本加载失败。请检查网络连接和 API Key 配置。'));
    };
    
    document.head.appendChild(script);
  });
  
  return glApiLoading;
}

/**
 * 检查 GL API 是否已加载
 */
export function isGLApiLoaded(): boolean {
  return glApiLoaded && !!window.TMap;
}

/**
 * 创建地图实例
 * 
 * @param container - 地图容器元素或元素 ID
 * @param options - 地图配置选项
 * @returns 地图实例
 * 
 * @example
 * await loadTMapGL();
 * const map = createMap('container', {
 *   center: { lat: 39.984120, lng: 116.307484 },
 *   zoom: 15
 * });
 */
export function createMap(
  container: HTMLElement | string,
  options: MapOptions
): TMapInstance {
  if (!isGLApiLoaded()) {
    throw new LBSError(-3, '请先调用 loadTMapGL() 加载地图 API');
  }
  
  const containerEl = typeof container === 'string' 
    ? document.getElementById(container)
    : container;
    
  if (!containerEl) {
    throw new LBSError(-4, `找不到地图容器元素: ${container}`);
  }
  
  const center = new window.TMap.LatLng(options.center.lat, options.center.lng);
  
  return new window.TMap.Map(containerEl, {
    center,
    zoom: options.zoom ?? 15,
    pitch: options.pitch ?? 0,
    rotation: options.rotation ?? 0
  });
}

/**
 * 创建标注点图层
 * 
 * @param map - 地图实例
 * @param markers - 标注点数据数组
 * @param style - 标注点样式（可选）
 * @returns 标注点图层实例
 * 
 * @example
 * const markerLayer = createMarkerLayer(map, [
 *   { id: '1', position: { lat: 39.984120, lng: 116.307484 }, properties: { title: '会议地点' } }
 * ]);
 */
export function createMarkerLayer(
  map: TMapInstance,
  markers: MarkerGeometry[],
  style?: MarkerStyleOptions
): TMapMultiMarker {
  if (!isGLApiLoaded()) {
    throw new LBSError(-3, '请先调用 loadTMapGL() 加载地图 API');
  }
  
  const styles: Record<string, TMapMarkerStyle> = {
    'default': new window.TMap.MarkerStyle({
      width: style?.width ?? 25,
      height: style?.height ?? 35,
      src: style?.src,
      anchor: style?.anchor ?? { x: 12, y: 35 }
    })
  };
  
  const geometries = markers.map(m => ({
    id: m.id,
    styleId: m.styleId ?? 'default',
    position: new window.TMap.LatLng(m.position.lat, m.position.lng),
    properties: m.properties
  }));
  
  return new window.TMap.MultiMarker({
    map,
    styles,
    geometries
  });
}

/**
 * 创建信息窗口
 * 
 * @param map - 地图实例
 * @param options - 信息窗口配置
 * @returns 信息窗口实例
 * 
 * @example
 * const infoWindow = createInfoWindow(map, {
 *   content: '<h3>会议地点</h3><p>北京市海淀区中关村大街1号</p>',
 *   position: { lat: 39.984120, lng: 116.307484 }
 * });
 * infoWindow.open();
 */
export function createInfoWindow(
  map: TMapInstance,
  options: InfoWindowOptions
): TMapInfoWindow {
  if (!isGLApiLoaded()) {
    throw new LBSError(-3, '请先调用 loadTMapGL() 加载地图 API');
  }
  
  return new window.TMap.InfoWindow({
    map,
    position: new window.TMap.LatLng(options.position.lat, options.position.lng),
    content: options.content,
    offset: options.offset ?? { x: 0, y: -32 }
  });
}

/**
 * 便捷函数：在地图上添加单个标注点并显示信息窗口
 * 
 * @param map - 地图实例
 * @param position - 标注位置
 * @param title - 标题
 * @param content - 信息窗口内容（支持 HTML）
 * @returns 包含标注图层和信息窗口的对象
 * 
 * @example
 * const { markerLayer, infoWindow } = addMarkerWithInfo(
 *   map,
 *   { lat: 39.984120, lng: 116.307484 },
 *   '会议地点',
 *   '<p>北京市海淀区中关村大街1号</p>'
 * );
 */
export function addMarkerWithInfo(
  map: TMapInstance,
  position: Location,
  title: string,
  content?: string
): { markerLayer: TMapMultiMarker; infoWindow: TMapInfoWindow } {
  // 创建标注点
  const markerLayer = createMarkerLayer(map, [
    { id: 'marker-1', position, properties: { title } }
  ]);
  
  // 创建信息窗口
  const infoWindow = createInfoWindow(map, {
    content: content ?? `<div style="padding: 8px;"><strong>${title}</strong></div>`,
    position
  });
  
  // 点击标注时显示信息窗口
  markerLayer.on('click', (evt) => {
    if (evt.geometry) {
      infoWindow.setPosition(evt.geometry.position);
      infoWindow.open();
    }
  });
  
  // 默认打开信息窗口
  infoWindow.open();
  
  return { markerLayer, infoWindow };
}

// ============ JSONP 实现 ============

/**
 * 使用 JSONP 方式发送请求（解决浏览器跨域问题）
 */
function jsonpRequest<T>(url: string, timeout: number = 10000): Promise<T> {
  return new Promise((resolve, reject) => {
    const callbackName = `__tencentLbsCallback_${Date.now()}_${++jsonpCallbackCounter}`;
    
    const separator = url.includes('?') ? '&' : '?';
    const jsonpUrl = `${url}${separator}output=jsonp&callback=${callbackName}`;
    
    const script = document.createElement('script');
    script.src = jsonpUrl;
    script.async = true;
    
    const timeoutId = setTimeout(() => {
      cleanup();
      reject(new LBSError(-1, `请求超时 (${timeout}ms)。请检查网络连接或稍后重试。`));
    }, timeout);
    
    const cleanup = () => {
      clearTimeout(timeoutId);
      delete (window as unknown as Record<string, unknown>)[callbackName];
      if (script.parentNode) {
        script.parentNode.removeChild(script);
      }
    };
    
    (window as unknown as Record<string, unknown>)[callbackName] = (data: T & { status: number; message: string }) => {
      cleanup();
      
      if (data.status !== 0) {
        const sourceMatch = data.message?.match(/来源[为是]?[:：]?\s*([^\s,，)）]+)/);
        const requestSource = sourceMatch ? sourceMatch[1] : undefined;
        reject(new LBSError(data.status, data.message, requestSource));
      } else {
        resolve(data);
      }
    };
    
    script.onerror = () => {
      cleanup();
      reject(new LBSError(-2, '网络请求失败。请检查网络连接。'));
    };
    
    document.head.appendChild(script);
  });
}

// ============ WebService API 客户端 ============

/**
 * 腾讯位置服务 WebService API 客户端
 * 
 * 提供地点搜索、地址解析、逆地址解析、IP定位和静态地图功能
 */
export class TencentLBSClient {
  private key: string;
  private timeout: number;

  constructor(options: ClientOptions = {}) {
    this.key = options.key ?? TENCENT_LBS_KEY;
    this.timeout = options.timeout ?? 10000;
  }

  private buildUrl(endpoint: string, params: Record<string, string | number | undefined>): string {
    const url = new URL(endpoint, BASE_URL);
    
    Object.entries(params).forEach(([k, v]) => {
      if (v !== undefined) {
        url.searchParams.append(k, String(v));
      }
    });
    
    url.searchParams.append('key', this.key);
    return url.toString();
  }

  private async request<T>(endpoint: string, params: Record<string, string | number | undefined>): Promise<T> {
    const url = this.buildUrl(endpoint, params);
    return jsonpRequest<T>(url, this.timeout);
  }

  /**
   * 地点搜索
   * 
   * @param keyword - 搜索关键词
   * @param options - 搜索选项
   * @returns POI 搜索结果
   */
  async searchPlace(keyword: string, options: SearchPlaceOptions): Promise<SearchPlaceResponse> {
    return this.request<SearchPlaceResponse>('/ws/place/v1/search', {
      keyword,
      boundary: options.boundary,
      page_size: options.pageSize,
      page_index: options.pageIndex,
      filter: options.filter,
      orderby: options.orderby
    });
  }

  /**
   * 地址解析（地址转坐标）
   * 
   * @param address - 结构化地址，建议包含省市区信息
   * @returns 解析结果
   */
  async geocode(address: string): Promise<GeocodeResponse> {
    return this.request<GeocodeResponse>('/ws/geocoder/v1/', {
      address
    });
  }

  /**
   * 逆地址解析（坐标转地址）
   * 
   * @param lat - 纬度
   * @param lng - 经度
   * @param options - 解析选项
   * @returns 解析结果
   */
  async reverseGeocode(lat: number, lng: number, options?: ReverseGeocodeOptions): Promise<ReverseGeocodeResponse> {
    return this.request<ReverseGeocodeResponse>('/ws/geocoder/v1/', {
      location: `${lat},${lng}`,
      get_poi: options?.getPoi,
      poi_options: options?.poiOptions
    });
  }

  /**
   * IP 定位
   * 
   * @param ip - 要定位的 IP 地址，不传则定位当前请求者
   * @returns 定位结果
   */
  async ipLocation(ip?: string): Promise<IPLocationResponse> {
    return this.request<IPLocationResponse>('/ws/location/v1/ip', {
      ip
    });
  }

  /**
   * 生成静态地图 URL
   * 
   * @param options - 地图配置选项
   * @returns 静态地图图片 URL
   */
  getStaticMapUrl(options: StaticMapOptions): string {
    const url = new URL('/ws/staticmap/v2/', BASE_URL);
    
    url.searchParams.append('size', `${options.size.width}*${options.size.height}`);
    url.searchParams.append('key', this.key);
    
    if (options.center) {
      url.searchParams.append('center', `${options.center.lat},${options.center.lng}`);
    }
    
    if (options.zoom !== undefined) {
      url.searchParams.append('zoom', String(options.zoom));
    }
    
    if (options.maptype) {
      url.searchParams.append('maptype', options.maptype);
    }
    
    if (options.scale) {
      url.searchParams.append('scale', String(options.scale));
    }
    
    if (options.format) {
      url.searchParams.append('format', options.format);
    }
    
    if (options.markers && options.markers.length > 0) {
      const markerStrings = options.markers.map(marker => {
        const parts: string[] = [];
        if (marker.color) parts.push(`color:${marker.color}`);
        if (marker.label) parts.push(`label:${marker.label}`);
        parts.push(`${marker.position.lat},${marker.position.lng}`);
        return parts.join('|');
      });
      url.searchParams.append('markers', markerStrings.join(';'));
    }
    
    return url.toString();
  }

  /**
   * 根据地址生成带标注的静态地图 URL
   * 
   * @param address - 地址字符串
   * @param options - 地图配置选项
   * @returns 地图 URL、坐标和格式化地址
   */
  async getMapByAddress(address: string, options?: Partial<Omit<StaticMapOptions, 'center' | 'markers'>>): Promise<{
    mapUrl: string;
    location: Location;
    formattedAddress: string;
  }> {
    const geocodeResult = await this.geocode(address);
    const location = geocodeResult.result.location;
    
    const mapUrl = this.getStaticMapUrl({
      center: location,
      zoom: options?.zoom ?? 16,
      size: options?.size ?? { width: 600, height: 400 },
      maptype: options?.maptype,
      scale: options?.scale ?? 2,
      format: options?.format,
      markers: [{
        position: location,
        label: 'A',
        color: 'red'
      }]
    });
    
    const components = geocodeResult.result.address_components;
    const formattedAddress = components 
      ? `${components.province}${components.city}${components.district}${components.street}${components.street_number}`
      : address;
    
    return { mapUrl, location, formattedAddress };
  }
}

/**
 * 创建腾讯位置服务 WebService API 客户端
 * 
 * @param options - 客户端配置选项
 * @returns 客户端实例
 */
export function createClient(options?: ClientOptions): TencentLBSClient {
  return new TencentLBSClient(options);
}

// 导出默认客户端实例
export default createClient();
