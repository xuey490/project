/**
 * 腾讯云 TTS 语音合成 SDK 封装
 * 
 * 生产级 TypeScript 封装，提供类型安全的语音合成 API。
 * 支持通用语音合成（短文本）和长文本语音合成（异步）。
 * 
 * @example
 * ```typescript
 * import { TencentTTSClient, createClient } from './tencent-tts';
 * 
 * const client = createClient();
 * 
 * // 短文本合成（同步，返回 Base64 音频）
 * const result = await client.textToVoice('你好世界');
 * 
 * // 长文本合成（异步，返回下载 URL）
 * const task = await client.createLongTextTask('很长的文章...');
 * const status = await client.getLongTextTaskStatus(task.taskId);
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1073
 * @version 1.0.0
 */

import * as tencentcloud from 'tencentcloud-sdk-nodejs-tts';

const TtsClient = (tencentcloud as any).tts.v20190823.Client;

// ============ 音色列表常量 ============

/**
 * 超自然大模型音色（最新一代，支持中英文）
 */
export const SUPER_NATURAL_VOICES = {
  /** 智小虎 - 聊天童声 */
  ZHI_XIAO_HU: 502007,
  /** 智小悟 - 聊天男声 */
  ZHI_XIAO_WU: 502006,
  /** 智小解 - 解说男声 */
  ZHI_XIAO_JIE: 502005,
  /** 智小满 - 营销女声 */
  ZHI_XIAO_MAN: 502004,
  /** 智小敏 - 聊天女声 */
  ZHI_XIAO_MIN: 502003,
  /** 智小柔 - 聊天女声 */
  ZHI_XIAO_ROU: 502001,
  /** 暖心阿灿 - 聊天男声 */
  NUAN_XIN_A_CAN: 602004,
  /** 专业梓欣 - 聊天女声 */
  ZHUAN_YE_ZI_XIN: 602005,
  /** 懂事少年 - 特色男声 */
  DONG_SHI_SHAO_NIAN: 603000,
  /** 潇湘妹妹 - 特色女声 */
  XIAO_XIANG_MEI_MEI: 603001,
  /** 软萌心心 - 特色男童声 */
  RUAN_MENG_XIN_XIN: 603002,
  /** 随和老李 - 聊天男声 */
  SUI_HE_LAO_LI: 603003,
  /** 温柔小柠 - 聊天女声 */
  WEN_ROU_XIAO_NING: 603004,
  /** 知心大林 - 聊天男声 */
  ZHI_XIN_DA_LIN: 603005,
  /** 爱小悠 - 聊天女声 */
  AI_XIAO_YOU: 602003,
} as const;

/**
 * 大模型音色（高质量，部分支持多情感）
 */
export const LARGE_MODEL_VOICES = {
  /** 智斌 - 阅读男声（中英文） */
  ZHI_BIN: 501000,
  /** 智兰 - 资讯女声（中英文） */
  ZHI_LAN: 501001,
  /** 智菊 - 阅读女声（中英文） */
  ZHI_JU: 501002,
  /** 智宇 - 阅读男声（中英文） */
  ZHI_YU: 501003,
  /** 月华 - 聊天女声（中英文） */
  YUE_HUA: 501004,
  /** 飞镜 - 聊天男声（中英文） */
  FEI_JING: 501005,
  /** 千嶂 - 聊天男声（中英文） */
  QIAN_ZHANG: 501006,
  /** 浅草 - 聊天男声（中英文） */
  QIAN_CAO: 501007,
  /** WeJames - 外语男声（英文） */
  WE_JAMES: 501008,
  /** WeWinny - 外语女声（英文） */
  WE_WINNY: 501009,
  /** 爱小溪 - 聊天女声（支持多情感） */
  AI_XIAO_XI: 601000,
  /** 爱小洛 - 阅读女声（支持多情感） */
  AI_XIAO_LUO: 601001,
  /** 爱小辰 - 聊天男声（支持多情感） */
  AI_XIAO_CHEN: 601002,
  /** 爱小荷 - 阅读女声（支持新闻/故事/广播/诗歌/客服） */
  AI_XIAO_HE: 601003,
  /** 爱小树 - 资讯男声（支持多情感） */
  AI_XIAO_SHU: 601004,
  /** 爱小静 - 聊天女声（支持多情感） */
  AI_XIAO_JING: 601005,
  /** 爱小耀 - 阅读男声（支持多情感） */
  AI_XIAO_YAO: 601006,
  /** 爱小叶 - 聊天女声（支持多情感） */
  AI_XIAO_YE: 601007,
  /** 爱小豪 - 聊天男声（支持多情感） */
  AI_XIAO_HAO: 601008,
  /** 爱小童 - 男童声（支持多情感） */
  AI_XIAO_TONG: 601015,
} as const;

/**
 * 精品音色（经典音色）
 */
export const PREMIUM_VOICES = {
  /** WeJack - 英文男声 */
  WE_JACK: 101050,
  /** 智付 - 通用女声 */
  ZHI_FU: 101055,
  /** 智辉 - 新闻男声 */
  ZHI_HUI: 101013,
  /** 智彤 - 粤语女声 */
  ZHI_TONG: 101019,
  /** 智柯 - 通用男声 */
  ZHI_KE: 101030,
  /** 智友 - 通用男声 */
  ZHI_YOU: 101054,
  /** 智梅 - 通用女声 */
  ZHI_MEI: 101027,
  /** 智希 - 通用女声 */
  ZHI_XI: 101026,
  /** 智云 - 通用男声 */
  ZHI_YUN: 101004,
  /** 智萌 - 男童声 */
  ZHI_MENG: 101015,
  /** 智燕 - 新闻女声 */
  ZHI_YAN: 101011,
  /** 智瑜 - 情感女声 */
  ZHI_YU_FEMALE: 101001,
  /** 智瑞 - 新闻男声 */
  ZHI_RUI: 101021,
  /** 智甜 - 女童声 */
  ZHI_TIAN: 101016,
} as const;

/**
 * 情感类型（用于支持多情感的音色）
 */
export const EMOTION_CATEGORIES = {
  /** 中性 */
  NEUTRAL: 'neutral',
  /** 悲伤 */
  SAD: 'sad',
  /** 高兴 */
  HAPPY: 'happy',
  /** 生气 */
  ANGRY: 'angry',
  /** 恐惧 */
  FEAR: 'fear',
  /** 新闻风格 */
  NEWS: 'news',
  /** 故事风格 */
  STORY: 'story',
  /** 广播风格 */
  RADIO: 'radio',
  /** 诗歌风格 */
  POETRY: 'poetry',
  /** 客服风格 */
  CALL: 'call',
  /** 撒娇 */
  SAJIAO: 'sajiao',
  /** 厌恶 */
  DISGUSTED: 'disgusted',
  /** 震惊 */
  AMAZE: 'amaze',
  /** 平静 */
  PEACEFUL: 'peaceful',
  /** 激动 */
  EXCITING: 'exciting',
} as const;

// ============ 类型定义 ============

/**
 * 腾讯云 TTS 客户端配置
 */
export interface TencentTTSConfig {
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
 * 通用语音合成选项（短文本）
 */
export interface TextToVoiceOptions {
  /** 音色 ID，默认 501001（智兰） */
  voiceType?: number;
  /** 音量，范围 [-10, 10]，默认 0 */
  volume?: number;
  /** 语速，范围 [-2, 6]，默认 0（正常语速） */
  speed?: number;
  /** 音频采样率：8000, 16000, 24000，默认 16000 */
  sampleRate?: number;
  /** 音频格式：wav, mp3, pcm，默认 mp3 */
  codec?: 'wav' | 'mp3' | 'pcm';
  /** 主语言：1=中文（默认），2=英文 */
  primaryLanguage?: 1 | 2;
  /** 是否开启时间戳功能 */
  enableSubtitle?: boolean;
  /** 情感类型（仅支持多情感音色） */
  emotionCategory?: string;
  /** 情感强度，范围 [50, 200]，默认 100 */
  emotionIntensity?: number;
}

/**
 * 长文本语音合成选项
 */
export interface LongTextOptions extends TextToVoiceOptions {
  /** 回调 URL，合成完成后推送结果 */
  callbackUrl?: string;
  /** 是否开启旁白/对话解析（仅支持特定音色） */
  voiceoverDialogueSplit?: boolean;
}

/**
 * 通用语音合成结果
 */
export interface TextToVoiceResult {
  /** Base64 编码的音频数据 */
  audio: string;
  /** 会话 ID */
  sessionId: string;
  /** 时间戳数据（如果启用） */
  subtitles?: Subtitle[];
  /** 请求 ID */
  requestId: string;
}

/**
 * 时间戳/字幕数据
 */
export interface Subtitle {
  /** 字符开始索引 */
  beginIndex: number;
  /** 开始时间（毫秒） */
  beginTime: number;
  /** 字符结束索引 */
  endIndex: number;
  /** 结束时间（毫秒） */
  endTime: number;
  /** 拼音 */
  phoneme: string;
  /** 文字 */
  text: string;
}

/**
 * 长文本任务创建结果
 */
export interface CreateLongTextTaskResult {
  /** 任务 ID，用于查询结果 */
  taskId: string;
  /** 请求 ID */
  requestId: string;
}

/**
 * 长文本任务状态
 */
export interface LongTextTaskStatus {
  /** 任务 ID */
  taskId: string;
  /** 状态：0=排队中，1=处理中，2=成功，3=失败 */
  status: number;
  /** 状态描述 */
  statusStr: string;
  /** 音频下载 URL（成功时返回） */
  resultUrl: string;
  /** 时间戳数据 */
  subtitles: Subtitle[];
  /** 错误信息（失败时返回） */
  errorMsg: string;
}

// ============ 文本长度限制常量 ============

/** 中文最大字符数（通用合成） */
const MAX_CHINESE_CHARS = 150;
/** 英文最大字母数（通用合成） */
const MAX_ENGLISH_LETTERS = 500;
/** 长文本最大字符数 */
const MAX_LONG_TEXT_CHARS = 100000;

/**
 * 腾讯云 TTS 客户端
 * 
 * 提供语音合成功能，支持短文本同步合成和长文本异步合成。
 * 自动根据文本长度选择合适的合成方式。
 */
export class TencentTTSClient {
  private client: any;
  private config: TencentTTSConfig;

  constructor(config: TencentTTSConfig) {
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
  private validateConfig(config: TencentTTSConfig): void {
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
   * 初始化腾讯云 TTS 客户端
   */
  private initializeClient(): any {
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
          endpoint: useSandbox ? 'tts.tencent_cloud.auth-proxy.local' : '',
          reqTimeout: this.config.timeout,
          protocol: useSandbox ? 'http:' : 'https:',
        },
      },
    };

    return new TtsClient(clientConfig);
  }

  /**
   * 判断文本是否需要使用长文本合成
   * 
   * 根据文本内容自动判断：
   * - 中文超过 150 字符使用长文本
   * - 英文超过 500 字母使用长文本
   * - 混合文本按中文标准判断
   */
  needsLongTextSynthesis(text: string, primaryLanguage: 1 | 2 = 1): boolean {
    if (primaryLanguage === 2) {
      // 英文模式：按字母数计算
      return text.length > MAX_ENGLISH_LETTERS;
    }
    // 中文模式：按字符数计算
    return text.length > MAX_CHINESE_CHARS;
  }

  /**
   * 智能语音合成
   * 
   * 自动根据文本长度选择合适的合成方式：
   * - 短文本：同步合成，直接返回 Base64 音频
   * - 长文本：异步合成，需要轮询获取结果
   * 
   * @param text - 要合成的文本
   * @param options - 合成选项
   * @returns 短文本返回音频 Buffer，长文本返回下载 URL
   * 
   * @example
   * ```typescript
   * // 短文本 - 返回 Buffer
   * const result = await client.synthesize('你好世界');
   * if (result.type === 'audio') {
   *   fs.writeFileSync('output.mp3', result.audio);
   * }
   * 
   * // 长文本 - 返回 URL
   * const result = await client.synthesize(longArticle);
   * if (result.type === 'url') {
   *   console.log('下载地址:', result.url);
   * }
   * ```
   */
  async synthesize(
    text: string,
    options?: LongTextOptions
  ): Promise<{ type: 'audio'; audio: Buffer; subtitles?: Subtitle[] } | { type: 'url'; url: string; subtitles?: Subtitle[] }> {
    const primaryLanguage = options?.primaryLanguage || 1;
    
    if (this.needsLongTextSynthesis(text, primaryLanguage)) {
      // 长文本：异步合成
      const task = await this.createLongTextTask(text, options);
      const result = await this.waitForLongTextComplete(task.taskId);
      return {
        type: 'url',
        url: result.resultUrl,
        subtitles: result.subtitles
      };
    } else {
      // 短文本：同步合成
      const result = await this.textToVoice(text, options);
      const audioBuffer = Buffer.from(result.audio, 'base64');
      return {
        type: 'audio',
        audio: audioBuffer,
        subtitles: result.subtitles
      };
    }
  }

  /**
   * 通用语音合成（短文本）
   * 
   * 同步合成语音，直接返回 Base64 编码的音频数据。
   * 中文最多 150 字符，英文最多 500 字母。
   * 
   * @param text - 要合成的文本
   * @param options - 合成选项
   * @returns Base64 音频数据和时间戳
   * @throws Error 如果文本超过限制或合成失败
   * 
   * @example
   * ```typescript
   * const result = await client.textToVoice('你好世界', {
   *   voiceType: LARGE_MODEL_VOICES.ZHI_LAN,
   *   speed: 0,
   *   volume: 0,
   *   codec: 'mp3'
   * });
   * 
   * // 保存音频文件
   * const audioBuffer = Buffer.from(result.audio, 'base64');
   * fs.writeFileSync('output.mp3', audioBuffer);
   * ```
   */
  async textToVoice(
    text: string,
    options?: TextToVoiceOptions
  ): Promise<TextToVoiceResult> {
    try {
      const sessionId = `tts-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      
      const params: any = {
        Text: text,
        SessionId: sessionId,
        VoiceType: options?.voiceType || LARGE_MODEL_VOICES.ZHI_LAN,
        Volume: options?.volume ?? 0,
        Speed: options?.speed ?? 0,
        SampleRate: options?.sampleRate || 16000,
        Codec: options?.codec || 'mp3',
        PrimaryLanguage: options?.primaryLanguage || 1,
        EnableSubtitle: options?.enableSubtitle || false,
      };

      // 情感控制
      if (options?.emotionCategory) {
        params.EmotionCategory = options.emotionCategory;
        params.EmotionIntensity = options.emotionIntensity ?? 100;
      }

      const response = await this.client.TextToVoice(params);
      
      if (!response || !response.Audio) {
        throw new Error('Failed to synthesize speech: Invalid response');
      }

      return {
        audio: response.Audio,
        sessionId: response.SessionId,
        subtitles: response.Subtitles?.map((s: any) => ({
          beginIndex: s.BeginIndex,
          beginTime: s.BeginTime,
          endIndex: s.EndIndex,
          endTime: s.EndTime,
          phoneme: s.Phoneme,
          text: s.Text
        })),
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to synthesize speech: ${error.message}`);
    }
  }

  /**
   * 创建长文本语音合成任务
   * 
   * 异步合成长文本（最多 10 万字符），需要轮询或回调获取结果。
   * 
   * @param text - 要合成的长文本
   * @param options - 合成选项
   * @returns 任务 ID
   * @throws Error 如果文本超过限制或创建失败
   * 
   * @example
   * ```typescript
   * const task = await client.createLongTextTask(articleContent, {
   *   voiceType: LARGE_MODEL_VOICES.AI_XIAO_HE,
   *   emotionCategory: EMOTION_CATEGORIES.NEWS,
   *   codec: 'mp3'
   * });
   * 
   * console.log('任务 ID:', task.taskId);
   * ```
   */
  async createLongTextTask(
    text: string,
    options?: LongTextOptions
  ): Promise<CreateLongTextTaskResult> {
    try {
      if (text.length > MAX_LONG_TEXT_CHARS) {
        throw new Error(`Text exceeds maximum length of ${MAX_LONG_TEXT_CHARS} characters`);
      }

      const params: any = {
        Text: text,
        VoiceType: options?.voiceType || LARGE_MODEL_VOICES.ZHI_LAN,
        Volume: options?.volume ?? 0,
        Speed: options?.speed ?? 0,
        SampleRate: options?.sampleRate || 16000,
        Codec: options?.codec || 'mp3',
        PrimaryLanguage: options?.primaryLanguage || 1,
        EnableSubtitle: options?.enableSubtitle || false,
      };

      // 回调 URL
      if (options?.callbackUrl) {
        params.CallbackUrl = options.callbackUrl;
      }

      // 旁白/对话解析
      if (options?.voiceoverDialogueSplit !== undefined) {
        params.VoiceoverDialogueSplit = options.voiceoverDialogueSplit;
      }

      // 情感控制
      if (options?.emotionCategory) {
        params.EmotionCategory = options.emotionCategory;
        params.EmotionIntensity = options.emotionIntensity ?? 100;
      }

      const response = await this.client.CreateTtsTask(params);
      
      if (!response || !response.Data) {
        throw new Error('Failed to create long text task: Invalid response');
      }

      return {
        taskId: response.Data.TaskId,
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to create long text task: ${error.message}`);
    }
  }

  /**
   * 查询长文本语音合成任务状态
   * 
   * @param taskId - 任务 ID（从 createLongTextTask 获得）
   * @returns 任务状态和结果
   * @throws Error 如果查询失败
   * 
   * @example
   * ```typescript
   * const status = await client.getLongTextTaskStatus(task.taskId);
   * 
   * if (status.status === 2) {
   *   console.log('合成完成，下载地址:', status.resultUrl);
   * } else if (status.status === 3) {
   *   console.error('合成失败:', status.errorMsg);
   * } else {
   *   console.log('处理中，状态:', status.statusStr);
   * }
   * ```
   */
  async getLongTextTaskStatus(taskId: string): Promise<LongTextTaskStatus> {
    try {
      const params: any = {
        TaskId: taskId
      };

      const response = await this.client.DescribeTtsTaskStatus(params);
      
      if (!response || !response.Data) {
        throw new Error('Failed to get task status: Invalid response');
      }

      const data = response.Data;
      
      return {
        taskId: data.TaskId,
        status: data.Status,
        statusStr: data.StatusStr,
        resultUrl: data.ResultUrl || '',
        subtitles: data.Subtitles?.map((s: any) => ({
          beginIndex: s.BeginIndex,
          beginTime: s.BeginTime,
          endIndex: s.EndIndex,
          endTime: s.EndTime,
          phoneme: s.Phoneme,
          text: s.Text
        })) || [],
        errorMsg: data.ErrorMsg || ''
      };
    } catch (error: any) {
      throw new Error(`Failed to get task status: ${error.message}`);
    }
  }

  /**
   * 轮询等待长文本任务完成
   * 
   * 自动轮询任务状态，直到完成或超时。
   * 
   * @param taskId - 任务 ID
   * @param maxAttempts - 最大轮询次数，默认 180 次（约 30 分钟）
   * @param interval - 轮询间隔（毫秒），默认 10000ms（10秒）
   * @returns 任务结果
   * @throws Error 如果合成失败或超时
   * 
   * @example
   * ```typescript
   * const task = await client.createLongTextTask(longText);
   * const result = await client.waitForLongTextComplete(task.taskId);
   * console.log('下载地址:', result.resultUrl);
   * ```
   */
  async waitForLongTextComplete(
    taskId: string,
    maxAttempts: number = 180,
    interval: number = 10000
  ): Promise<LongTextTaskStatus> {
    let attempts = 0;

    while (attempts < maxAttempts) {
      const status = await this.getLongTextTaskStatus(taskId);

      if (status.status === 2) {
        // 合成成功
        return status;
      } else if (status.status === 3) {
        // 合成失败
        throw new Error(status.errorMsg || 'Long text synthesis failed');
      }

      // 等待后重试
      await new Promise(resolve => setTimeout(resolve, interval));
      attempts++;
    }

    throw new Error(`Long text synthesis timeout after ${maxAttempts} attempts`);
  }
}

/**
 * 使用环境变量创建 TTS 客户端实例
 * 
 * 从环境变量读取配置:
 * - TENCENTCLOUD_SECRET_ID
 * - TENCENTCLOUD_SECRET_KEY
 * - TENCENTCLOUD_REGION（可选，默认 ap-shanghai）
 * 
 * @param config - 可选的额外配置，会覆盖环境变量
 * @returns TTS 客户端实例
 * 
 * @example
 * ```typescript
 * import { createClient } from './tencent-tts';
 * 
 * // 使用环境变量
 * const client = createClient();
 * 
 * // 覆盖部分配置
 * const client2 = createClient({ region: 'ap-guangzhou' });
 * ```
 */
export function createClient(config: Partial<TencentTTSConfig> = {}): TencentTTSClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  const secretId = config?.secretId || process.env.TENCENTCLOUD_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = config?.secretKey || process.env.TENCENTCLOUD_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = config?.region || process.env.TENCENTCLOUD_REGION || 'ap-shanghai';
  const timeout = config?.timeout || 60;
  
  return new TencentTTSClient({
    secretId,
    secretKey,
    region,
    timeout
  });
}

/**
 * 获取所有可用音色列表
 * 
 * @returns 音色列表，按类型分组
 */
export function getAvailableVoices() {
  return {
    superNatural: Object.entries(SUPER_NATURAL_VOICES).map(([key, id]) => ({
      id,
      name: key,
      type: 'super_natural',
      description: getVoiceDescription(id)
    })),
    largeModel: Object.entries(LARGE_MODEL_VOICES).map(([key, id]) => ({
      id,
      name: key,
      type: 'large_model',
      description: getVoiceDescription(id)
    })),
    premium: Object.entries(PREMIUM_VOICES).map(([key, id]) => ({
      id,
      name: key,
      type: 'premium',
      description: getVoiceDescription(id)
    }))
  };
}

/**
 * 获取音色描述
 */
function getVoiceDescription(voiceId: number): string {
  const descriptions: Record<number, string> = {
    // 超自然大模型
    502007: '智小虎 - 聊天童声（中英文）',
    502006: '智小悟 - 聊天男声（中英文）',
    502005: '智小解 - 解说男声（中英文）',
    502004: '智小满 - 营销女声（中英文）',
    502003: '智小敏 - 聊天女声（中英文）',
    502001: '智小柔 - 聊天女声（中英文）',
    602004: '暖心阿灿 - 聊天男声（中英文）',
    602005: '专业梓欣 - 聊天女声（中英文）',
    603000: '懂事少年 - 特色男声（中英文）',
    603001: '潇湘妹妹 - 特色女声（中英文）',
    603002: '软萌心心 - 特色男童声（中英文）',
    603003: '随和老李 - 聊天男声（中英文）',
    603004: '温柔小柠 - 聊天女声（中英文）',
    603005: '知心大林 - 聊天男声（中英文）',
    602003: '爱小悠 - 聊天女声（中英文）',
    // 大模型
    501000: '智斌 - 阅读男声（中英文）',
    501001: '智兰 - 资讯女声（中英文）',
    501002: '智菊 - 阅读女声（中英文）',
    501003: '智宇 - 阅读男声（中英文）',
    501004: '月华 - 聊天女声（中英文）',
    501005: '飞镜 - 聊天男声（中英文）',
    501006: '千嶂 - 聊天男声（中英文）',
    501007: '浅草 - 聊天男声（中英文）',
    501008: 'WeJames - 外语男声（英文）',
    501009: 'WeWinny - 外语女声（英文）',
    601000: '爱小溪 - 聊天女声（支持多情感）',
    601001: '爱小洛 - 阅读女声（支持多情感）',
    601002: '爱小辰 - 聊天男声（支持多情感）',
    601003: '爱小荷 - 阅读女声（支持新闻/故事/广播/诗歌/客服）',
    601004: '爱小树 - 资讯男声（支持多情感）',
    601005: '爱小静 - 聊天女声（支持多情感）',
    601006: '爱小耀 - 阅读男声（支持多情感）',
    601007: '爱小叶 - 聊天女声（支持多情感）',
    601008: '爱小豪 - 聊天男声（支持多情感）',
    601015: '爱小童 - 男童声（支持多情感）',
    // 精品
    101050: 'WeJack - 英文男声',
    101055: '智付 - 通用女声',
    101013: '智辉 - 新闻男声',
    101019: '智彤 - 粤语女声',
    101030: '智柯 - 通用男声',
    101054: '智友 - 通用男声',
    101027: '智梅 - 通用女声',
    101026: '智希 - 通用女声',
    101004: '智云 - 通用男声',
    101015: '智萌 - 男童声',
    101011: '智燕 - 新闻女声',
    101001: '智瑜 - 情感女声',
    101021: '智瑞 - 新闻男声',
    101016: '智甜 - 女童声',
  };
  return descriptions[voiceId] || `音色 ID: ${voiceId}`;
}
