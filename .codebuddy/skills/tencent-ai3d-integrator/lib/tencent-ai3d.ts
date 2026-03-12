/**
 * Tencent Cloud AI3D SDK Wrapper
 * 
 * Production-ready TypeScript wrapper for Tencent Cloud Hunyuan 3D Generation service.
 * Provides a clean, type-safe API supporting text-to-3D and image-to-3D functionality.
 * 
 * @example
 * ```typescript
 * import { TencentAI3DClient } from './tencent-ai3d';
 * 
 * const client = new TencentAI3DClient({
 *   secretId: process.env.TENCENTCLOUD_SECRET_ID,
 *   secretKey: process.env.TENCENTCLOUD_SECRET_KEY,
 *   region: 'ap-guangzhou'
 * });
 * 
 * // Text-to-3D
 * const { jobId } = await client.submitTextTo3D('A little cat');
 * const result = await client.waitForTaskCompletion(jobId);
 * console.log('Model URL:', result.files[0].url);
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1804
 * @version 1.0.0
 */

import tencentcloud from 'tencentcloud-sdk-nodejs';

const Ai3dClient = (tencentcloud as any).ai3d.v20250513.Client;

/**
 * Tencent Cloud AI3D client configuration
 */
export interface TencentAI3DConfig {
  /** Tencent Cloud SecretId */
  secretId: string;
  /** Tencent Cloud SecretKey */
  secretKey: string;
  /** Region, default ap-guangzhou */
  region?: string;
  /** Custom endpoint (optional) */
  endpoint?: string;
  /** Request timeout (milliseconds), default 60000 */
  timeout?: number;
}

/**
 * 3D model generation format
 */
export type ModelFormat = 'OBJ' | 'GLB' | 'STL' | 'USDZ' | 'FBX' | 'MP4';

/**
 * Task status
 */
export type TaskStatus = 'WAIT' | 'RUN' | 'FAIL' | 'DONE';

/**
 * Optional parameters for submitting tasks
 */
export interface SubmitTaskOptions {
  /** Whether to enable PBR material generation, default false */
  enablePBR?: boolean;
  /** Number of faces for generated 3D model, supported range 40000-1500000, default 500000 */
  faceCount?: number;
  /** Generation task type: Normal(with texture), LowPoly(smart decimation), Geometry(white model), Sketch(sketch generation), default Normal */
  generateType?: 'Normal' | 'LowPoly' | 'Geometry' | 'Sketch';
  /** Polygon type (LowPoly mode only): triangle, quadrilateral(quadrilateral+triangle), default triangle */
  polygonType?: 'triangle' | 'quadrilateral';
}

/**
 * Submit task response
 */
export interface SubmitTaskResult {
  /** Job ID, valid for 24 hours */
  jobId: string;
  /** Request ID */
  requestId: string;
}

/**
 * 3D file information
 */
export interface File3D {
  /** Preview image URL */
  previewImageUrl: string;
  /** File type */
  type: string;
  /** 3D file download URL (ZIP format) */
  url: string;
}

/**
 * Query task response
 */
export interface QueryTaskResult {
  /** Task status */
  status: TaskStatus;
  /** Error code (if any) */
  errorCode?: string;
  /** Error message (if any) */
  errorMessage?: string;
  /** Generated 3D file list */
  files: File3D[];
  /** Request ID */
  requestId: string;
}

/**
 * Wait for task completion options
 */
export interface WaitForCompletionOptions {
  /** Poll interval (milliseconds), default 3000 */
  pollInterval?: number;
  /** Maximum wait time (milliseconds), default 180000 (3 minutes) */
  timeout?: number;
}

/**
 * Tencent Cloud AI3D Client
 * 
 * Provides a clean API wrapper supporting text-to-3D and image-to-3D functionality
 */
export class TencentAI3DClient {
  private client: any;
  private config: TencentAI3DConfig;

  constructor(config: TencentAI3DConfig) {
    this.validateConfig(config);
    this.config = {
      region: 'ap-guangzhou',
      timeout: 60000,
      ...config
    };
    this.client = this.initializeClient();
  }

  /**
   * Initialize Tencent Cloud SDK client
   */
  private initializeClient(): any {
    // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND credentials are mock
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = this.config.secretId === 'mock_secret_id' || this.config.secretKey === 'mock_secret_key';
    const useSandbox = isSandbox && isMockCredentials;
    
    const clientConfig = {
      credential: {
        secretId: this.config.secretId,
        secretKey: this.config.secretKey
      },
      region: this.config.region,
      profile: {
        httpProfile: {
          endpoint: this.config.endpoint || (useSandbox ? 'ai3d.tencent_cloud.auth-proxy.local' : ''),
          reqTimeout: this.config.timeout! / 1000, // Convert to seconds
          protocol: useSandbox ? 'http:' : 'https:',
        }
      }
    };

    return new Ai3dClient(clientConfig);
  }

  /**
   * Validate configuration parameters
   */
  private validateConfig(config: TencentAI3DConfig): void {
    if (!config.secretId || config.secretId.trim() === '') {
      throw new Error('SecretId is required');
    }
    if (!config.secretKey || config.secretKey.trim() === '') {
      throw new Error('SecretKey is required');
    }
    if (config.region && !/^[a-z]+-[a-z]+(-\d+)?$/.test(config.region)) {
      throw new Error('Invalid region format');
    }
  }

  /**
   * Submit text-to-3D task
   * 
   * @param prompt - Chinese description of 3D content, maximum 200 characters
   * @param options - Optional parameters
   * @returns Task submission result containing jobId
   * @throws Error if submission fails
   * 
   * @example
   * ```typescript
   * const result = await client.submitTextTo3D('A little cat', {
   *   resultFormat: 'GLB',
   *   enablePBR: true
   * });
   * console.log('Job ID:', result.jobId);
   * ```
   */
  async submitTextTo3D(
    prompt: string,
    options?: SubmitTaskOptions
  ): Promise<SubmitTaskResult> {
    if (!prompt || prompt.trim() === '') {
      throw new Error('Prompt cannot be empty');
    }
    if (prompt.length > 200) {
      throw new Error('Prompt cannot exceed 200 characters');
    }

    try {
      const params: any = {
        Prompt: prompt,
        EnablePBR: options?.enablePBR || false
      };
      
      if (options?.faceCount) {
        params.FaceCount = options.faceCount;
      }
      if (options?.generateType) {
        params.GenerateType = options.generateType;
      }
      if (options?.polygonType) {
        params.PolygonType = options.polygonType;
      }

      const response = await this.client.SubmitHunyuanTo3DProJob(params);

      return {
        jobId: response.JobId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to submit text-to-3D task: ${error.message}`);
    }
  }

  /**
   * Submit image-to-3D task (using image URL)
   * 
   * @param imageUrl - Image URL address
   * @param options - Optional parameters
   * @returns Task submission result containing jobId
   * @throws Error if submission fails
   * 
   * @example
   * ```typescript
   * const result = await client.submitImageTo3D(
   *   'https://example.com/image.jpg',
   *   { resultFormat: 'GLB' }
   * );
   * console.log('Job ID:', result.jobId);
   * ```
   */
  async submitImageTo3D(
    imageUrl: string,
    options?: SubmitTaskOptions
  ): Promise<SubmitTaskResult> {
    if (!imageUrl || imageUrl.trim() === '') {
      throw new Error('ImageUrl cannot be empty');
    }
    if (!imageUrl.startsWith('http://') && !imageUrl.startsWith('https://')) {
      throw new Error('ImageUrl must be a valid HTTP(S) URL');
    }

    try {
      const params: any = {
        ImageUrl: imageUrl,
        EnablePBR: options?.enablePBR || false
      };
      
      if (options?.faceCount) {
        params.FaceCount = options.faceCount;
      }
      if (options?.generateType) {
        params.GenerateType = options.generateType;
      }
      if (options?.polygonType) {
        params.PolygonType = options.polygonType;
      }

      const response = await this.client.SubmitHunyuanTo3DProJob(params);

      return {
        jobId: response.JobId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to submit image-to-3D task: ${error.message}`);
    }
  }

  /**
   * Submit image-to-3D task (using Base64 encoding)
   * 
   * @param imageBase64 - Base64 encoded image string
   * @param options - Optional parameters
   * @returns Task submission result containing jobId
   * @throws Error if submission fails
   * 
   * @example
   * ```typescript
   * const fs = require('fs');
   * const imageBuffer = fs.readFileSync('image.jpg');
   * const base64 = imageBuffer.toString('base64');
   * 
   * const result = await client.submitImageBase64To3D(base64);
   * console.log('Job ID:', result.jobId);
   * ```
   */
  async submitImageBase64To3D(
    imageBase64: string,
    options?: SubmitTaskOptions
  ): Promise<SubmitTaskResult> {
    if (!imageBase64 || imageBase64.trim() === '') {
      throw new Error('ImageBase64 cannot be empty');
    }

    try {
      const params: any = {
        ImageBase64: imageBase64,
        EnablePBR: options?.enablePBR || false
      };
      
      if (options?.faceCount) {
        params.FaceCount = options.faceCount;
      }
      if (options?.generateType) {
        params.GenerateType = options.generateType;
      }
      if (options?.polygonType) {
        params.PolygonType = options.polygonType;
      }

      const response = await this.client.SubmitHunyuanTo3DProJob(params);

      return {
        jobId: response.JobId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to submit image-to-3D task: ${error.message}`);
    }
  }

  /**
   * Query task status and result
   * 
   * @param jobId - Job ID
   * @returns Task query result
   * @throws Error if query fails
   * 
   * @example
   * ```typescript
   * const result = await client.queryTask('1335141824121323520');
   * console.log('Task status:', result.status);
   * if (result.status === 'DONE') {
   *   console.log('Model URL:', result.files[0].url);
   * }
   * ```
   */
  async queryTask(jobId: string): Promise<QueryTaskResult> {
    if (!jobId || jobId.trim() === '') {
      throw new Error('JobId cannot be empty');
    }

    try {
      const params = {
        JobId: jobId
      };

      const response = await this.client.QueryHunyuanTo3DProJob(params);

      return {
        status: response.Status as TaskStatus,
        errorCode: response.ErrorCode,
        errorMessage: response.ErrorMessage,
        files: (response.ResultFile3Ds || []).map((file: any) => ({
          previewImageUrl: file.PreviewImageUrl,
          type: file.Type,
          url: file.Url
        })),
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to query task: ${error.message}`);
    }
  }

  /**
   * Wait for task completion
   * 
   * Automatically poll task status until completion or timeout
   * 
   * @param jobId - Job ID
   * @param options - Wait options
   * @returns Completed task result
   * @throws Error if task fails or times out
   * 
   * @example
   * ```typescript
   * const result = await client.waitForTaskCompletion('1335141824121323520', {
   *   pollInterval: 5000,  // Poll every 5 seconds
   *   timeout: 300000      // Maximum wait 5 minutes
   * });
   * console.log('Model URL:', result.files[0].url);
   * ```
   */
  async waitForTaskCompletion(
    jobId: string,
    options?: WaitForCompletionOptions
  ): Promise<QueryTaskResult> {
    const pollInterval = options?.pollInterval || 3000;
    const timeout = options?.timeout || 180000;
    const startTime = Date.now();

    while (true) {
      const result = await this.queryTask(jobId);

      if (result.status === 'DONE') {
        return result;
      }

      if (result.status === 'FAIL') {
        throw new Error(
          `Task failed: ${result.errorMessage || result.errorCode || 'Unknown error'}`
        );
      }

      if (Date.now() - startTime > timeout) {
        throw new Error(`Task timeout: wait time exceeded ${timeout}ms`);
      }

      // Wait before continuing to poll
      await new Promise(resolve => setTimeout(resolve, pollInterval));
    }
  }
}

/**
 * Create client instance using environment variables
 * 
 * @param config - Optional partial configuration to override environment variables
 * @returns TencentAI3DClient instance
 * 
 * @example
 * ```typescript
 * import { createClient } from './tencent-ai3d';
 * 
 * const client = createClient();
 * const result = await client.submitTextTo3D('A little cat');
 * ```
 */
export function createClient(
  config?: Partial<TencentAI3DConfig>
): TencentAI3DClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: env vars > sandbox mock > empty string
  const secretId = process.env.TENCENTCLOUD_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = process.env.TENCENTCLOUD_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = process.env.TENCENTCLOUD_REGION || 'ap-guangzhou';

  return new TencentAI3DClient({
    secretId,
    secretKey,
    region,
    endpoint: config?.endpoint,
    timeout: config?.timeout
  });
}
