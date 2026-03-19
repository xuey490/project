/**
 * 腾讯云 ASR 语音识别 SDK 封装
 * 
 * 生产级 TypeScript 封装，提供类型安全的语音识别 API。
 * 支持录音文件识别、一句话识别和语音流异步识别。
 * 
 * @example
 * ```typescript
 * import { TencentASRClient } from './tencent-asr';
 * 
 * const client = new TencentASRClient({
 *   secretId: process.env.TENCENTCLOUD_SECRET_ID!,
 *   secretKey: process.env.TENCENTCLOUD_SECRET_KEY!,
 *   region: 'ap-shanghai'
 * });
 * 
 * // 创建识别任务
 * const task = await client.createRecognitionTask(audioBuffer);
 * 
 * // 获取识别结果
 * const result = await client.getRecognitionResult(task.taskId);
 * console.log('识别结果:', result.result);
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1093
 * @version 1.0.0
 */

const tencentcloud = require('tencentcloud-sdk-nodejs-asr');

const AsrClient = tencentcloud.asr.v20190614.Client;
const Models = tencentcloud.asr.v20190614.Models;

/**
 * 腾讯云 ASR 客户端配置
 */
export interface TencentASRConfig {
  /** 腾讯云 SecretId */
  secretId: string;
  /** 腾讯云 SecretKey */
  secretKey: string;
  /** 服务地域，如 ap-shanghai, ap-guangzhou */
  region?: string;
  /** 请求超时时间（秒），默认 60 秒 */
  timeout?: number;
}

/**
 * 录音文件识别选项
 */
export interface RecognitionOptions {
  /** 引擎模型类型，如 16k_zh（16k中文）、8k_zh（8k中文） */
  engineType?: string;
  /** 声道数，仅支持单声道识别，默认值为 1 */
  channelNum?: number;
  /** 识别结果文本编码方式，0: UTF-8（默认），1: GB2312，2: GBK，3: BIG5 */
  resTextFormat?: number;
  /** 数据来源，0: 语音 URL，1: 语音数据（post body） */
  sourceType?: number;
  /** 回调 URL，识别完成后将结果通过 POST 请求通知到该 URL */
  callbackUrl?: string;
}

/**
 * 一句话识别选项
 */
export interface SentenceRecognitionOptions {
  /** 引擎模型类型 */
  engineType?: string;
  /** 语音编码方式：mp3, wav, pcm, m4a, speex, silk, aac, ogg-opus, amr */
  voiceFormat?: string;
}

/**
 * 创建识别任务返回结果
 */
export interface CreateTaskResult {
  /** 任务 ID，用于轮询识别结果 */
  taskId: number;
  /** 请求 ID */
  requestId: string;
}

/**
 * 识别任务状态
 */
export interface TaskStatus {
  /** 任务 ID */
  taskId: number;
  /** 任务状态：0-等待，1-执行中，2-成功，3-失败 */
  status: number;
  /** 识别结果，当 status=2 时返回 */
  result?: string;
  /** 错误信息，当 status=3 时返回 */
  errorMsg?: string;
  /** 结果详情 URL */
  resultDetail?: string;
  /** 音频时长（秒） */
  audioDuration?: number;
}

/**
 * 一句话识别结果
 */
export interface SentenceRecognitionResult {
  /** 识别结果 */
  result: string;
  /** 音频时长（毫秒） */
  audioDuration: number;
  /** 请求 ID */
  requestId: string;
}

/**
 * 腾讯云 ASR 客户端
 * 
 * 提供语音识别功能的封装，包括录音文件识别和一句话识别
 */
export class TencentASRClient {
  private client: any;
  private config: TencentASRConfig;

  constructor(config: TencentASRConfig) {
    this.validateConfig(config);
    this.config = {
      region: 'ap-shanghai',
      timeout: 60,
      ...config
    };
    this.client = this.initializeClient();
  }

  /**
   * 验证配置参数
   */
  private validateConfig(config: TencentASRConfig): void {
    if (!config.secretId) {
      throw new Error('SecretId is required');
    }
    if (!config.secretKey) {
      throw new Error('SecretKey is required');
    }
    if (config.timeout && config.timeout <= 0) {
      throw new Error('Timeout must be greater than 0');
    }
  }

  /**
   * 初始化腾讯云 ASR 客户端
   */
  private initializeClient(): any {
    // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND credentials are mock
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = this.config.secretId === 'mock_secret_id' || this.config.secretKey === 'mock_secret_key';
    const useSandbox = isSandbox && isMockCredentials;
    
    const clientConfig = {
      credential: {
        secretId: this.config.secretId,
        secretKey: this.config.secretKey,
      },
      region: this.config.region,
      profile: {
        httpProfile: {
          endpoint: useSandbox ? 'asr.tencent_cloud.auth-proxy.local' : '',
          reqTimeout: this.config.timeout,
          protocol: useSandbox ? 'http:' : 'https:',
        },
      },
    };

    return new AsrClient(clientConfig);
  }

  /**
   * 创建录音文件识别任务
   * 
   * 对录音文件进行识别，支持 wav、pcm、mp3、silk、speex、amr 等格式。
   * 音频文件大小不超过 512MB，时长不超过 5 小时。
   * 
   * @param audioBuffer - 音频文件 Buffer
   * @param options - 识别选项
   * @returns 任务 ID 和请求 ID
   * @throws Error 如果创建任务失败
   * 
   * @example
   * ```typescript
   * import * as fs from 'fs';
   * 
   * const audioBuffer = fs.readFileSync('audio.wav');
   * const task = await client.createRecognitionTask(audioBuffer, {
   *   engineType: '16k_zh',
   *   channelNum: 1,
   *   resTextFormat: 0
   * });
   * 
   * console.log('任务 ID:', task.taskId);
   * ```
   */
  async createRecognitionTask(
    audioBuffer: Buffer,
    options?: RecognitionOptions
  ): Promise<CreateTaskResult> {
    try {
      const params: any = {
        Data: audioBuffer.toString('base64'),
        EngineModelType: options?.engineType || '16k_zh',
        ChannelNum: options?.channelNum || 1,
        ResTextFormat: options?.resTextFormat || 0,
        SourceType: options?.sourceType || 1
      };
      
      // 设置回调 URL（如果提供）
      if (options?.callbackUrl) {
        params.CallbackUrl = options.callbackUrl;
      }

      const response = await this.client.CreateRecTask(params);
      
      if (!response || !response.Data) {
        throw new Error('Failed to create recognition task: Invalid response');
      }

      return {
        taskId: response.Data.TaskId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to create recognition task: ${error.message}`);
    }
  }

  /**
   * 查询录音文件识别结果
   * 
   * 通过任务 ID 轮询识别结果。建议轮询间隔为 2-5 秒。
   * 
   * @param taskId - 任务 ID（从 createRecognitionTask 获得）
   * @returns 任务状态和识别结果
   * @throws Error 如果查询失败
   * 
   * @example
   * ```typescript
   * const result = await client.getRecognitionResult(123456);
   * 
   * if (result.status === 2) {
   *   console.log('识别成功:', result.result);
   * } else if (result.status === 3) {
   *   console.error('识别失败:', result.errorMsg);
   * } else {
   *   console.log('识别中，状态:', result.status);
   * }
   * ```
   */
  async getRecognitionResult(taskId: number): Promise<TaskStatus> {
    try {
      const params: any = {
        TaskId: taskId
      };

      const response = await this.client.DescribeTaskStatus(params);
      
      if (!response || !response.Data) {
        throw new Error('Failed to get recognition result: Invalid response');
      }

      const data = response.Data;
      
      return {
        taskId,
        status: data.Status,
        result: data.Result,
        errorMsg: data.ErrorMsg,
        resultDetail: data.ResultDetail,
        audioDuration: data.AudioDuration
      };
    } catch (error: any) {
      throw new Error(`Failed to get recognition result: ${error.message}`);
    }
  }

  /**
   * 一句话识别
   * 
   * 对 60 秒以内的短音频进行识别，快速返回结果。
   * 适用于语音搜索、语音指令等场景。
   * 
   * @param audioBuffer - 音频文件 Buffer（大小不超过 5MB）
   * @param options - 识别选项
   * @returns 识别结果
   * @throws Error 如果识别失败
   * 
   * @example
   * ```typescript
   * import * as fs from 'fs';
   * 
   * const audioBuffer = fs.readFileSync('short-audio.wav');
   * const result = await client.sentenceRecognition(audioBuffer, {
   *   engineType: '16k_zh',
   *   voiceFormat: 'wav'
   * });
   * 
   * console.log('识别结果:', result.result);
   * console.log('音频时长:', result.audioDuration, 'ms');
   * ```
   */
  async sentenceRecognition(
    audioBuffer: Buffer,
    options?: SentenceRecognitionOptions
  ): Promise<SentenceRecognitionResult> {
    try {
      // 检查文件大小（5MB = 5 * 1024 * 1024 字节）
      const maxSize = 5 * 1024 * 1024;
      if (audioBuffer.length > maxSize) {
        throw new Error(`Audio file size exceeds 5MB limit (${audioBuffer.length} bytes)`);
      }

      const params: any = {
        Data: audioBuffer.toString('base64'),
        EngSerViceType: options?.engineType || '16k_zh',
        VoiceFormat: options?.voiceFormat || 'wav',
        SourceType: 1
      };

      const response = await this.client.SentenceRecognition(params);
      
      if (!response || response.Result === undefined) {
        throw new Error('Failed to recognize sentence: Invalid response');
      }

      return {
        result: response.Result,
        audioDuration: response.AudioDuration || 0,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to recognize sentence: ${error.message}`);
    }
  }

  /**
   * 创建语音流异步识别任务
   * 
   * 异步识别语音流，适用于大批量音频处理场景。
   * 
   * @param audioUrl - 音频文件 URL（需可公网访问）
   * @param options - 识别选项
   * @returns 任务 ID
   * @throws Error 如果创建任务失败
   * 
   * @example
   * ```typescript
   * const task = await client.createAsyncRecognitionTask(
   *   'https://example.com/audio.wav',
   *   {
   *     engineType: '16k_zh',
   *     channelNum: 1,
   *     callbackUrl: 'https://example.com/callback'
   *   }
   * );
   * 
   * console.log('任务 ID:', task.taskId);
   * ```
   */
  async createAsyncRecognitionTask(
    audioUrl: string,
    options?: RecognitionOptions
  ): Promise<CreateTaskResult> {
    try {
      if (!audioUrl.startsWith('http://') && !audioUrl.startsWith('https://')) {
        throw new Error('Audio URL must start with http:// or https://');
      }

      const params: any = {
        Url: audioUrl,
        EngineModelType: options?.engineType || '16k_zh',
        ChannelNum: options?.channelNum || 1,
        ResTextFormat: options?.resTextFormat || 0,
        SourceType: 0
      };
      
      // 设置回调 URL（如果提供）
      if (options?.callbackUrl) {
        params.CallbackUrl = options.callbackUrl;
      }

      const response = await this.client.CreateRecTask(params);
      
      if (!response || !response.Data) {
        throw new Error('Failed to create async recognition task: Invalid response');
      }

      return {
        taskId: response.Data.TaskId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to create async recognition task: ${error.message}`);
    }
  }

  /**
   * 轮询等待识别任务完成
   * 
   * 自动轮询识别结果，直到任务完成或超时。
   * 
   * @param taskId - 任务 ID
   * @param maxAttempts - 最大轮询次数，默认 30 次
   * @param interval - 轮询间隔（毫秒），默认 2000ms
   * @returns 识别结果
   * @throws Error 如果识别失败或超时
   * 
   * @example
   * ```typescript
   * const task = await client.createRecognitionTask(audioBuffer);
   * const result = await client.waitForRecognitionComplete(task.taskId);
   * console.log('识别结果:', result);
   * ```
   */
  async waitForRecognitionComplete(
    taskId: number,
    maxAttempts: number = 30,
    interval: number = 2000
  ): Promise<string> {
    let attempts = 0;

    while (attempts < maxAttempts) {
      const status = await this.getRecognitionResult(taskId);

      if (status.status === 2) {
        // 识别成功
        return status.result || '';
      } else if (status.status === 3) {
        // 识别失败
        throw new Error(status.errorMsg || 'Recognition failed');
      }

      // 等待后重试
      await new Promise(resolve => setTimeout(resolve, interval));
      attempts++;
    }

    throw new Error(`Recognition timeout after ${maxAttempts} attempts`);
  }
}

/**
 * 使用环境变量创建 ASR 客户端实例
 * 
 * 从环境变量读取配置:
 * - TENCENTCLOUD_SECRET_ID
 * - TENCENTCLOUD_SECRET_KEY
 * - TENCENTCLOUD_REGION(可选,默认 ap-shanghai)
 * 
 * @param config - 可选的额外配置,会覆盖环境变量
 * @returns ASR 客户端实例
 * 
 * @example
 * ```typescript
 * import { createClient } from './tencent-asr';
 * 
 * // 使用环境变量
 * const client = createClient();
 * 
 * // 覆盖部分配置
 * const client2 = createClient({ region: 'ap-guangzhou' });
 * ```
 */
export function createClient(config: Partial<TencentASRConfig> = {}): TencentASRClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: env vars > sandbox mock > empty string
  const secretId = config?.secretId || process.env.TENCENTCLOUD_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = config?.secretKey || process.env.TENCENTCLOUD_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = config?.region || process.env.TENCENTCLOUD_REGION || 'ap-shanghai';
  const timeout = config?.timeout || 60;
  
  return new TencentASRClient({
    secretId,
    secretKey,
    region,
    timeout
  });
}
