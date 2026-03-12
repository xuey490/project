import Taro from '@tarojs/taro';

// 声明构建时注入的常量（通过 defineConstants 配置）
declare const TARO_APP_API_URL: string;

// API Base URL Configuration
// H5: Use relative path, Vite proxy forwards /api to backend
// MiniProgram: Use full URL to backend server
const getBaseUrl = (): string => {
    // Use Taro.getEnv() for runtime environment detection
    const env = Taro.getEnv();
    // MiniProgram: 优先使用环境变量配置的 API URL，否则使用默认值
    // 可通过 .env 文件配置 TARO_APP_API_URL
    if (typeof TARO_APP_API_URL !== 'undefined' && TARO_APP_API_URL) {
        return TARO_APP_API_URL;
    }
    if (env === Taro.ENV_TYPE.WEB) {
        // H5: Empty base, use full path like '/api/users/list'
        return '';
    }
    return 'http://localhost:3000';
};

const BASE_URL = getBaseUrl();

interface RequestConfig {
  url: string;
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  data?: any;
  header?: Record<string, string>;
}

/**
 * API Client for making HTTP requests
 *
 * URL paths should include '/api' prefix, matching backend routes.
 *
 * @example
 * apiClient.post('/api/users/list', { page: 1 });
 * apiClient.get('/api/health');
 */
class ApiClient {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  private getAuthToken(): string | null {
    try {
      return Taro.getStorageSync('auth_token');
    } catch (error) {
      console.error('Failed to get auth token:', error);
      return null;
    }
  }

  private async request<T>(config: RequestConfig): Promise<T> {
    const { url, method = 'GET', data, header = {} } = config;

    const token = this.getAuthToken();
    if (token) {
      header['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await Taro.request({
        url: `${this.baseURL}${url}`,
        method,
        data,
        header: {
          'Content-Type': 'application/json',
          ...header
        }
      });

      if (response.statusCode === 401) {
        // Token expired or invalid
        Taro.removeStorageSync('auth_token');
        Taro.showToast({
          title: 'Please login again',
          icon: 'none'
        });
        throw new Error('Unauthorized');
      }

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return response.data as T;
      }

      // Extract error message from response
      const errorData = response.data as any;
      const errorMessage = errorData?.error || errorData?.message || `Request failed with status ${response.statusCode}`;
      throw new Error(errorMessage);
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  get<T>(url: string, header?: Record<string, string>): Promise<T> {
    return this.request<T>({ url, method: 'GET', header });
  }

  post<T>(url: string, data?: any, header?: Record<string, string>): Promise<T> {
    return this.request<T>({ url, method: 'POST', data, header });
  }

  put<T>(url: string, data?: any, header?: Record<string, string>): Promise<T> {
    return this.request<T>({ url, method: 'PUT', data, header });
  }

  patch<T>(url: string, data?: any, header?: Record<string, string>): Promise<T> {
    return this.request<T>({ url, method: 'PATCH', data, header });
  }

  delete<T>(url: string, header?: Record<string, string>): Promise<T> {
    return this.request<T>({ url, method: 'DELETE', header });
  }
}

export const apiClient = new ApiClient(BASE_URL);
export default apiClient;
