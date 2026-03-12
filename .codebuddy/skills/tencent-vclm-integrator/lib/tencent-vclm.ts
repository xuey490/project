/**
 * Tencent Cloud Hunyuan Video Generation (VCLM) SDK Wrapper
 * 
 * Production-ready TypeScript wrapper for Tencent Cloud Hunyuan Video Generation SDK.
 * Provides a clean, type-safe API for common video generation operations.
 * 
 * @example
 * ```typescript
 * import { VCLMClient } from './tencent-vclm';
 * 
 * const client = new VCLMClient({
 *   secretId: process.env.TENCENTCLOUD_SECRET_ID,
 *   secretKey: process.env.TENCENTCLOUD_SECRET_KEY,
 *   region: 'ap-guangzhou'
 * });
 * 
 * // Submit video generation job
 * const job = await client.submitVideoJob('A cat running on grassland, realistic style');
 * 
 * // Wait for job completion
 * const result = await client.waitForJobCompletion(job.jobId);
 * 
 * // Download video
 * await client.downloadVideo(result.resultVideoUrl, './output.mp4');
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1616
 * @version 1.0.0
 */

const tencentcloud = require('tencentcloud-sdk-nodejs');
import * as fs from 'fs';
import * as https from 'https';
import * as path from 'path';

// Get VCLM client
const VclmClient = tencentcloud.vclm.v20240523.Client;

/**
 * VCLM client configuration options
 */
export interface VCLMClientConfig {
  /** Tencent Cloud SecretId */
  secretId: string;
  /** Tencent Cloud SecretKey */
  secretKey: string;
  /** Region, defaults to ap-guangzhou */
  region?: string;
  /** API endpoint, defaults to vclm.tencentcloudapi.com */
  endpoint?: string;
}

/**
 * Input image configuration
 */
export interface ImageInput {
  /** Image URL or base64 encoded */
  url?: string;
  /** Base64 encoded image data */
  base64?: string;
}

/**
 * Submit video job options
 */
export interface SubmitJobOptions {
  /** Input image */
  image?: ImageInput;
  /** Video resolution, currently only supports 720p */
  resolution?: '720p';
  /** Whether to add AI generation watermark, default 1 (add) */
  logoAdd?: 0 | 1;
}

/**
 * Submit job result
 */
export interface SubmitJobResult {
  /** Job ID */
  jobId: string;
  /** Request ID */
  requestId: string;
}

/**
 * Job status
 */
export type JobStatus = 'WAIT' | 'RUN' | 'FAIL' | 'DONE';

/**
 * Job details
 */
export interface JobDetail {
  /** Job status */
  status: JobStatus;
  /** Error code (only when status is FAIL) */
  errorCode?: string;
  /** Error message (only when status is FAIL) */
  errorMessage?: string;
  /** Result video URL (only when status is DONE, valid for 24 hours) */
  resultVideoUrl?: string;
  /** Request ID */
  requestId: string;
}

/**
 * Wait for job completion options
 */
export interface WaitOptions {
  /** Poll interval (milliseconds), defaults to 5000 */
  pollInterval?: number;
  /** Maximum wait time (milliseconds), defaults to 600000 (10 minutes) */
  timeout?: number;
  /** Progress callback function */
  onProgress?: (status: JobStatus) => void;
}

/**
 * VCLMClient provides a clean wrapper around Tencent Cloud Hunyuan Video Generation SDK
 */
export class VCLMClient {
  private client: any;
  private config: VCLMClientConfig;

  constructor(config: VCLMClientConfig) {
    this.validateConfig(config);
    this.config = {
      region: 'ap-guangzhou',
      ...config
    };
    this.client = this.initializeClient();
  }

  /**
   * Initialize the underlying SDK client
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
          endpoint: this.config.endpoint || (useSandbox ? 'vclm.tencent_cloud.auth-proxy.local' : ''),
          protocol: useSandbox ? 'http:' : 'https:',
        }
      }
    };

    return new VclmClient(clientConfig);
  }

  /**
   * Validate configuration
   */
  private validateConfig(config: VCLMClientConfig): void {
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = config.secretId === 'mock_secret_id' || config.secretKey === 'mock_secret_key';
    
    // If not in sandbox, require real credentials
    if (!isSandbox && isMockCredentials) {
      throw new Error('Real credentials (TENCENTCLOUD_SECRET_ID and TENCENTCLOUD_SECRET_KEY) are required when not in sandbox environment');
    }
    
    if (!config.secretId) {
      throw new Error('SecretId is required');
    }
    if (!config.secretKey) {
      throw new Error('SecretKey is required');
    }
    if (config.secretId.trim().length === 0) {
      throw new Error('SecretId cannot be empty');
    }
    if (config.secretKey.trim().length === 0) {
      throw new Error('SecretKey cannot be empty');
    }
  }

  /**
   * Submit Hunyuan video generation job
   * 
   * Generate video based on text description or input image. Default provides 1 concurrent job,
   * meaning at most 1 submitted job can be processed simultaneously.
   * 
   * @param prompt - Video content description, Chinese positive prompt, supports up to 200 utf-8 characters
   * @param options - Optional job configuration
   * @returns Submit result containing job ID
   * @throws Error if submission fails
   * 
   * @example
   * ```typescript
   * // Text-only generation
   * const job = await client.submitVideoJob('A cat running on grassland, realistic style');
   * console.log('Job ID:', job.jobId);
   * 
   * // Text + image generation
   * const job = await client.submitVideoJob('Make this cat run', {
   *   image: { url: 'https://example.com/cat.jpg' },
   *   logoAdd: 0
   * });
   * ```
   */
  async submitVideoJob(
    prompt: string,
    options?: SubmitJobOptions
  ): Promise<SubmitJobResult> {
    try {
      // Validate prompt
      if (!prompt || prompt.trim().length === 0) {
        throw new Error('Prompt cannot be empty');
      }
      if (prompt.trim().length > 200) {
        throw new Error('Prompt cannot exceed 200 characters');
      }

      // Build request parameters
      const params: any = {
        Prompt: prompt.trim()
      };

      // Add optional parameters
      if (options?.image) {
        params.Image = {};
        if (options.image.url) {
          params.Image.Url = options.image.url;
        }
        if (options.image.base64) {
          params.Image.Base64 = options.image.base64;
        }
      }

      if (options?.resolution) {
        params.Resolution = options.resolution;
      }

      if (options?.logoAdd !== undefined) {
        params.LogoAdd = options.logoAdd;
      }

      // Call SDK
      const response = await this.client.SubmitHunyuanToVideoJob(params);

      return {
        jobId: response.JobId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to submit video job: ${error.message}`);
    }
  }

  /**
   * Query Hunyuan video generation job status
   * 
   * Query the execution status and result of a submitted job.
   * 
   * @param jobId - Job ID
   * @returns Job details
   * @throws Error if query fails
   * 
   * @example
   * ```typescript
   * const detail = await client.getJobStatus('1387262242604040192');
   * console.log('Job status:', detail.status);
   * 
   * if (detail.status === 'DONE') {
   *   console.log('Video URL:', detail.resultVideoUrl);
   * } else if (detail.status === 'FAIL') {
   *   console.error('Failure reason:', detail.errorMessage);
   * }
   * ```
   */
  async getJobStatus(jobId: string): Promise<JobDetail> {
    try {
      if (!jobId || jobId.trim().length === 0) {
        throw new Error('JobId cannot be empty');
      }

      const params = {
        JobId: jobId
      };

      const response = await this.client.DescribeHunyuanToVideoJob(params);

      return {
        status: response.Status as JobStatus,
        errorCode: response.ErrorCode || undefined,
        errorMessage: response.ErrorMessage || undefined,
        resultVideoUrl: response.ResultVideoUrl || undefined,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to get job status: ${error.message}`);
    }
  }

  /**
   * Wait for job completion
   * 
   * Poll job status until completion or failure. Default checks every 5 seconds, maximum wait 10 minutes.
   * 
   * @param jobId - Job ID
   * @param options - Wait options
   * @returns Completed job details
   * @throws Error if job fails or times out
   * 
   * @example
   * ```typescript
   * // Basic usage
   * const result = await client.waitForJobCompletion('1387262242604040192');
   * console.log('Video generated:', result.resultVideoUrl);
   * 
   * // Custom polling and timeout
   * const result = await client.waitForJobCompletion('1387262242604040192', {
   *   pollInterval: 3000,  // Check every 3 seconds
   *   timeout: 300000,     // Maximum wait 5 minutes
   *   onProgress: (status) => console.log('Current status:', status)
   * });
   * ```
   */
  async waitForJobCompletion(
    jobId: string,
    options?: WaitOptions
  ): Promise<JobDetail> {
    const pollInterval = options?.pollInterval || 5000;
    const timeout = options?.timeout || 600000; // 10 minutes
    const startTime = Date.now();

    while (true) {
      // Check timeout
      if (Date.now() - startTime > timeout) {
        throw new Error(`Job timeout after ${timeout}ms`);
      }

      // Get job status
      const detail = await this.getJobStatus(jobId);

      // Call progress callback
      if (options?.onProgress) {
        options.onProgress(detail.status);
      }

      // Check job status
      if (detail.status === 'DONE') {
        return detail;
      }

      if (detail.status === 'FAIL') {
        throw new Error(
          `Job failed: ${detail.errorMessage || detail.errorCode || 'Unknown error'}`
        );
      }

      // Wait before continuing to poll
      await new Promise(resolve => setTimeout(resolve, pollInterval));
    }
  }

  /**
   * Download generated video
   * 
   * Download video file from result URL to local storage. Note: Video URL is valid for only 24 hours.
   * 
   * @param videoUrl - Video URL (obtained from job result)
   * @param savePath - Local save path
   * @throws Error if download fails
   * 
   * @example
   * ```typescript
   * const detail = await client.getJobStatus('1387262242604040192');
   * 
   * if (detail.status === 'DONE' && detail.resultVideoUrl) {
   *   await client.downloadVideo(detail.resultVideoUrl, './output.mp4');
   *   console.log('Video saved to ./output.mp4');
   * }
   * ```
   */
  async downloadVideo(videoUrl: string, savePath: string): Promise<void> {
    return new Promise((resolve, reject) => {
      try {
        if (!videoUrl || videoUrl.trim().length === 0) {
          throw new Error('Video URL cannot be empty');
        }

        if (!savePath || savePath.trim().length === 0) {
          throw new Error('Save path cannot be empty');
        }

        // Ensure directory exists
        const dir = path.dirname(savePath);
        if (!fs.existsSync(dir)) {
          fs.mkdirSync(dir, { recursive: true });
        }

        // Create write stream
        const fileStream = fs.createWriteStream(savePath);

        // Download file
        https.get(videoUrl, (response) => {
          if (response.statusCode !== 200) {
            reject(new Error(`Download failed with status: ${response.statusCode}`));
            return;
          }

          response.pipe(fileStream);

          fileStream.on('finish', () => {
            fileStream.close();
            resolve();
          });

          fileStream.on('error', (error) => {
            fs.unlink(savePath, () => {}); // Delete incomplete file
            reject(new Error(`Failed to write file: ${error.message}`));
          });
        }).on('error', (error) => {
          reject(new Error(`Failed to download video: ${error.message}`));
        });
      } catch (error: any) {
        reject(new Error(`Failed to download video: ${error.message}`));
      }
    });
  }

  /**
   * Submit job and wait for completion (convenience method)
   * 
   * Convenience method combining submitVideoJob and waitForJobCompletion.
   * 
   * @param prompt - Video content description
   * @param submitOptions - Submit job options
   * @param waitOptions - Wait options
   * @returns Completed job details
   * @throws Error if submission or execution fails
   * 
   * @example
   * ```typescript
   * const result = await client.generateVideo('A dog dancing', {
   *   logoAdd: 0
   * }, {
   *   onProgress: (status) => console.log('Status:', status)
   * });
   * 
   * console.log('Video generated:', result.resultVideoUrl);
   * ```
   */
  async generateVideo(
    prompt: string,
    submitOptions?: SubmitJobOptions,
    waitOptions?: WaitOptions
  ): Promise<JobDetail> {
    const job = await this.submitVideoJob(prompt, submitOptions);
    return await this.waitForJobCompletion(job.jobId, waitOptions);
  }
}

/**
 * Create VCLMClient instance using environment variables
 * 
 * @example
 * ```typescript
 * import { createClient } from './tencent-vclm';
 * 
 * const client = createClient();
 * ```
 */
export function createClient(config?: Partial<VCLMClientConfig>): VCLMClient {
  const secretId = process.env.TENCENTCLOUD_SECRET_ID || 'mock_secret_id';
  const secretKey = process.env.TENCENTCLOUD_SECRET_KEY || 'mock_secret_key';
  const region = process.env.TENCENTCLOUD_REGION || 'ap-guangzhou';
  
  return new VCLMClient({
    secretId,
    secretKey,
    region,
    endpoint: config?.endpoint
  });
}
