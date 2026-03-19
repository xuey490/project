/**
 * Sunrise Sunset Times Client Wrapper
 *
 * Provides sunrise, sunset, and twilight times for any geographic location.
 *
 * @example
 * const client = createSunTimesClient();
 * const times = await client.getSunTimes({
 *   latitude: 40.7128,
 *   longitude: -74.006,
 *   date: '2024-01-15',
 *   timeZoneId: 'America/New_York'
 * });
 */

import axios, { AxiosInstance } from 'axios';

export interface SunTimesClientConfig {
  apiKey: string;
  baseURL?: string;
}

export interface GetSunTimesParams {
  /** Latitude (-90 to 90) */
  latitude: number;
  /** Longitude (-180 to 180) */
  longitude: number;
  /** Date in YYYY-MM-DD format */
  date: string;
  /** IANA timezone identifier (e.g., 'America/New_York', 'Asia/Shanghai') */
  timeZoneId: string;
}

export interface SunTimesResponse {
  /** Time when sun rises above horizon */
  sunrise: string;
  /** Time when sun sets below horizon */
  sunset: string;
  /** Time when sun is at highest point */
  solarNoon: string;
  /** Morning civil twilight start */
  civilTwilightMorning: string;
  /** Evening civil twilight end */
  civilTwilightEvening: string;
  /** Morning astronomical twilight start */
  astronomicalTwilightMorning: string;
  /** Evening astronomical twilight end */
  astronomicalTwilightEvening: string;
  /** Morning nautical twilight start */
  nauticalTwilightMorning: string;
  /** Evening nautical twilight end */
  nauticalTwilightEvening: string;
  /** Morning blue hour start */
  blueHourMorning: string;
  /** Evening blue hour start */
  blueHourEvening: string;
  /** Morning golden hour start (best photography light) */
  goldenHourMorning: string;
  /** Evening golden hour start */
  goldenHourEvening: string;
}

export class SunTimesClient {
  private httpClient: AxiosInstance;

  constructor(config: SunTimesClientConfig) {
    const baseURL = config.baseURL || 'https://sunrise-sunset-times.p.rapidapi.com';

    this.httpClient = axios.create({
      baseURL,
      headers: {
        'x-rapidapi-host': 'sunrise-sunset-times.p.rapidapi.com',
        'x-rapidapi-key': config.apiKey,
      },
    });
  }

  /**
   * Get sun times for a specific location and date
   *
   * @param params - Location coordinates, date, and timezone
   * @returns Sun timing data including sunrise, sunset, twilight phases, golden/blue hours
   *
   * @example
   * const times = await client.getSunTimes({
   *   latitude: 40.7128,
   *   longitude: -74.006,
   *   date: '2024-01-15',
   *   timeZoneId: 'America/New_York'
   * });
   */
  async getSunTimes(params: GetSunTimesParams): Promise<SunTimesResponse> {
    // Validate parameters
    if (params.latitude < -90 || params.latitude > 90) {
      throw new Error('Latitude must be between -90 and 90');
    }
    if (params.longitude < -180 || params.longitude > 180) {
      throw new Error('Longitude must be between -180 and 180');
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(params.date)) {
      throw new Error('Date must be in YYYY-MM-DD format');
    }

    const response = await this.httpClient.get<SunTimesResponse>('/getSunTimes', {
      params: {
        latitude: params.latitude,
        longitude: params.longitude,
        date: params.date,
        timeZoneId: params.timeZoneId,
      },
    });

    return response.data;
  }
}

/**
 * Create a SunTimesClient instance with default configuration
 *
 * @param config - Optional configuration overrides
 * @returns Configured SunTimesClient instance
 *
 * @example
 * // Using environment variable
 * const client = createSunTimesClient();
 *
 * // Using explicit config
 * const client = createSunTimesClient({ apiKey: 'your-api-key' });
 */
export function createSunTimesClient(config?: Partial<SunTimesClientConfig>): SunTimesClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: config > env vars > sandbox mock
  const apiKey = config?.apiKey || process.env.RAPIDAPI_KEY || (isSandbox ? 'mock_rapidapi_key' : '');

  if (!apiKey) {
    throw new Error('RAPIDAPI_KEY environment variable is required');
  }

  // Check if using sandbox proxy
  const isMockCredentials = apiKey === 'mock_rapidapi_key';
  const useSandbox = isSandbox && isMockCredentials;

  return new SunTimesClient({
    apiKey,
    baseURL: useSandbox ? 'http://rapidapi.auth-proxy.local' : config?.baseURL,
  });
}
