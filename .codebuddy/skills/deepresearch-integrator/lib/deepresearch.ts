/**
 * Tencent Hunyuan DeepResearch SDK Wrapper (Native Tencent Cloud SDK)
 * 
 * Production-ready TypeScript wrapper for Tencent Hunyuan using native Tencent Cloud SDK.
 * Uses SecretId + SecretKey authentication (CAM credentials).
 * 
 * IMPORTANT: All comments and documentation in this file MUST be in English.
 * 
 * @example
 * ```typescript
 * import { HunyuanNativeClient, createNativeClient } from './deepresearch';
 * 
 * const client = createNativeClient();
 * const response = await client.chat('Explain quantum computing');
 * console.log(response.content);
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1729/105701
 * @version 1.0.0
 */

const tencentcloud = require('tencentcloud-sdk-nodejs-hunyuan');

const HunyuanSDK = tencentcloud.hunyuan.v20230901.Client;
type ChatCompletionsRequest = typeof tencentcloud.hunyuan.v20230901.Models.ChatCompletionsRequest;
type ChatCompletionsResponse = typeof tencentcloud.hunyuan.v20230901.Models.ChatCompletionsResponse;

/**
 * Configuration options for HunyuanNativeClient
 */
export interface HunyuanNativeClientConfig {
  /** Tencent Cloud Secret ID */
  secretId: string;
  /** Tencent Cloud Secret Key */
  secretKey: string;
  /** Region (default: ap-guangzhou) */
  region?: string;
  /** Model name to use (default: hunyuan-t1-latest) */
  model?: string;
  /** Request timeout in milliseconds (default: 120000ms = 2 minutes) */
  timeout?: number;
  /** Maximum tokens to generate (default: 4096) */
  maxTokens?: number;
  /** Enable search enhancement by default (default: false) */
  enableEnhancement?: boolean;
}

/**
 * Message structure for chat conversations
 */
export interface ChatMessage {
  /** Role of the message sender */
  Role: 'system' | 'user' | 'assistant' | 'tool';
  /** Content of the message */
  Content: string;
  /** Optional tool call ID */
  ToolCallId?: string;
  /** Optional name */
  Name?: string;
}

/**
 * Chat completion response
 */
export interface ChatResponse {
  /** Generated content from the model */
  content: string;
  /** Reason why the generation stopped */
  finishReason: string;
  /** Token usage statistics */
  usage: {
    promptTokens: number;
    completionTokens: number;
    totalTokens: number;
  };
  /** Unique response ID */
  id: string;
  /** Model used for generation */
  model: string;
  /** Search information (if enhancement enabled) */
  searchInfo?: any;
  /** Note/disclaimer from the API */
  note?: string;
}

/**
 * Optional parameters for chat requests
 */
export interface ChatOptions {
  /** Temperature for randomness (0.0 - 2.0) */
  temperature?: number;
  /** Top-p sampling (0.0 - 1.0) */
  topP?: number;
  /** Enable search enhancement for this request */
  enableEnhancement?: boolean;
  /** Maximum tokens to generate for this request */
  maxTokens?: number;
  /** System prompt to set context */
  systemPrompt?: string;
  /** Enable streaming response */
  stream?: boolean;
}

/**
 * HunyuanNativeClient provides a clean wrapper around Tencent Hunyuan API using native SDK
 * 
 * Uses SecretId + SecretKey authentication (CAM credentials).
 */
export class HunyuanNativeClient {
  private client: InstanceType<typeof HunyuanSDK>;
  private config: Required<Omit<HunyuanNativeClientConfig, 'enableEnhancement'>> & { enableEnhancement: boolean };

  /**
   * Create a new HunyuanNativeClient instance
   * 
   * @param config - Configuration options
   * @throws Error if credentials are missing or invalid
   * 
   * @example
   * ```typescript
   * const client = new HunyuanNativeClient({
   *   secretId: process.env.TENCENT_SECRET_ID!,
   *   secretKey: process.env.TENCENT_SECRET_KEY!,
   *   region: 'ap-guangzhou'
   * });
   * ```
   */
  constructor(config: HunyuanNativeClientConfig) {
    this.validateConfig(config);
    
    this.config = {
      secretId: config.secretId,
      secretKey: config.secretKey,
      region: config.region || 'ap-guangzhou',
      model: config.model || 'hunyuan-t1-latest',
      timeout: config.timeout || 120000,
      maxTokens: config.maxTokens || 4096,
      enableEnhancement: config.enableEnhancement || false
    };

    // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND credentials are mock
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = this.config.secretId === 'mock_secret_id' || this.config.secretKey === 'mock_secret_key';
    const useSandbox = isSandbox && isMockCredentials;

    this.client = new HunyuanSDK({
      credential: {
        secretId: this.config.secretId,
        secretKey: this.config.secretKey,
      },
      region: this.config.region,
      profile: {
        httpProfile: {
          endpoint: useSandbox ? 'hunyuan.tencent_cloud.auth-proxy.local' : 'hunyuan.tencentcloudapi.com',
          reqTimeout: Math.floor(this.config.timeout / 1000), // Convert to seconds
          protocol: useSandbox ? 'http:' : 'https:',
        },
      },
    });
  }

  /**
   * Validate configuration parameters
   * 
   * @param config - Configuration to validate
   * @throws Error if configuration is invalid
   */
  private validateConfig(config: HunyuanNativeClientConfig): void {
    if (!config.secretId || config.secretId.trim().length === 0) {
      throw new Error('Secret ID is required');
    }

    if (!config.secretKey || config.secretKey.trim().length === 0) {
      throw new Error('Secret Key is required');
    }

    if (config.timeout && config.timeout < 1000) {
      throw new Error('Timeout must be at least 1000ms');
    }

    if (config.maxTokens && (config.maxTokens < 1 || config.maxTokens > 64000)) {
      throw new Error('Max tokens must be between 1 and 64000');
    }
  }

  /**
   * Build messages array with optional system prompt
   * 
   * @param messages - Base messages
   * @param systemPrompt - Optional system prompt to prepend
   * @returns Formatted messages array
   */
  private buildMessages(messages: ChatMessage[], systemPrompt?: string): ChatMessage[] {
    const result: ChatMessage[] = [];

    if (systemPrompt) {
      result.push({ Role: 'system', Content: systemPrompt });
    }

    result.push(...messages);

    return result;
  }

  /**
   * Send a single message and get response
   * 
   * @param message - User message to send
   * @param options - Optional parameters
   * @returns Chat response with content and metadata
   * @throws Error if request fails
   * 
   * @example
   * ```typescript
   * const response = await client.chat('Explain quantum computing in detail');
   * console.log(response.content);
   * console.log('Tokens used:', response.usage.totalTokens);
   * ```
   */
  async chat(message: string, options?: ChatOptions): Promise<ChatResponse> {
    const messages: ChatMessage[] = [{ Role: 'user', Content: message }];
    return this.chatWithHistory(messages, options);
  }

  /**
   * Send multiple messages (conversation history) and get response
   * 
   * @param messages - Array of conversation messages
   * @param options - Optional parameters
   * @returns Chat response with content and metadata
   * @throws Error if request fails
   * 
   * @example
   * ```typescript
   * const messages = [
   *   { Role: 'user', Content: 'What is machine learning?' },
   *   { Role: 'assistant', Content: 'Machine learning is...' },
   *   { Role: 'user', Content: 'Can you give an example?' }
   * ];
   * const response = await client.chatWithHistory(messages);
   * console.log(response.content);
   * ```
   */
  async chatWithHistory(messages: ChatMessage[], options?: ChatOptions): Promise<ChatResponse> {
    try {
      const formattedMessages = this.buildMessages(messages, options?.systemPrompt);
      
      const params: ChatCompletionsRequest = {
        Model: this.config.model,
        Messages: formattedMessages,
        Stream: options?.stream || false,
      };

      // Add optional parameters
      if (options?.temperature !== undefined) {
        params.Temperature = options.temperature;
      }
      if (options?.topP !== undefined) {
        params.TopP = options.topP;
      }
      if (options?.maxTokens !== undefined || this.config.maxTokens) {
        // Note: Tencent SDK doesn't have MaxTokens, it generates until natural stop
      }
      if (options?.enableEnhancement !== undefined) {
        params.EnableEnhancement = options.enableEnhancement;
      } else if (this.config.enableEnhancement) {
        params.EnableEnhancement = this.config.enableEnhancement;
      }

      const response: ChatCompletionsResponse = await this.client.ChatCompletions(params);

      if (!response.Choices || response.Choices.length === 0) {
        throw new Error('Invalid response format from API');
      }

      const choice = response.Choices[0];
      const content = choice.Message?.Content || '';
      
      return {
        content: content,
        finishReason: choice.FinishReason || 'stop',
        usage: {
          promptTokens: response.Usage?.PromptTokens || 0,
          completionTokens: response.Usage?.CompletionTokens || 0,
          totalTokens: response.Usage?.TotalTokens || 0
        },
        id: response.Id || '',
        model: this.config.model,
        searchInfo: response.SearchInfo,
        note: response.Note
      };
    } catch (error: any) {
      if (error.code === 'AuthFailure.SignatureFailure' || error.code === 'AuthFailure.SecretIdNotFound') {
        throw new Error('Authentication failed. Please check your SecretId and SecretKey.');
      } else if (error.code === 'RequestLimitExceeded') {
        throw new Error('Rate limit exceeded. Please retry later.');
      } else if (error.code === 'InvalidParameter.Model') {
        throw new Error(`Model not found: ${this.config.model}. Check available models in console.`);
      } else {
        throw new Error(`Hunyuan API error: ${error.message || 'Unknown error'}`);
      }
    }
  }

  /**
   * Send a message with streaming response
   * 
   * Note: Streaming support requires SSE handling in Tencent Cloud SDK
   * 
   * @param message - User message to send
   * @param onChunk - Callback function for each content chunk
   * @param options - Optional parameters
   * @throws Error if streaming is not supported or request fails
   * 
   * @example
   * ```typescript
   * await client.chatStream('Write a research paper outline', (chunk) => {
   *   process.stdout.write(chunk);
   * });
   * ```
   */
  async chatStream(
    message: string,
    onChunk: (content: string) => void,
    options?: ChatOptions
  ): Promise<void> {
    throw new Error('Streaming is not fully implemented in this wrapper. Use chatWithHistory with Stream: true if needed.');
  }

  /**
   * Send a message with search enhancement enabled
   * 
   * @param message - User message to send
   * @param options - Optional parameters
   * @returns Chat response with content, metadata, and search info
   * @throws Error if request fails
   * 
   * @example
   * ```typescript
   * const response = await client.chatWithEnhancement(
   *   'What are the latest developments in quantum computing in 2025?'
   * );
   * console.log(response.content);
   * if (response.searchInfo) {
   *   console.log('Search results:', response.searchInfo);
   * }
   * ```
   */
  async chatWithEnhancement(message: string, options?: ChatOptions): Promise<ChatResponse> {
    return this.chat(message, {
      ...options,
      enableEnhancement: true
    });
  }

  /**
   * Count approximate tokens in text
   * 
   * Note: This is a simple estimation. For accurate token counting,
   * use the token count from API responses.
   * 
   * @param text - Text to count tokens for
   * @returns Approximate token count
   * 
   * @example
   * ```typescript
   * const tokenCount = client.countTokens('Hello, world!');
   * console.log('Estimated tokens:', tokenCount);
   * ```
   */
  countTokens(text: string): number {
    // Simple estimation: ~4 characters per token for Chinese/English mixed content
    return Math.ceil(text.length / 4);
  }

  /**
   * Get current model name
   * 
   * @returns Model name being used
   */
  getModel(): string {
    return this.config.model;
  }

  /**
   * Get current region
   * 
   * @returns Region being used
   */
  getRegion(): string {
    return this.config.region;
  }
}

/**
 * Create a HunyuanNativeClient instance using environment variables
 * 
 * Required environment variables:
 * - TENCENT_SECRET_ID: Your Tencent Cloud Secret ID
 * - TENCENT_SECRET_KEY: Your Tencent Cloud Secret Key
 * 
 * Optional environment variables:
 * - TENCENT_REGION: Region (default: ap-guangzhou)
 * - HUNYUAN_MODEL: Model name (default: hunyuan-t1-latest)
 * 
 * @param config - Optional partial configuration to override environment variables
 * @returns Configured HunyuanNativeClient instance
 * @throws Error if required environment variables are missing
 * 
 * @example
 * ```typescript
 * import { createNativeClient } from './deepresearch';
 * 
 * // Using environment variables
 * const client = createNativeClient();
 * 
 * // Override specific settings
 * const client = createNativeClient({
 *   model: 'hunyuan-turbos-latest',
 *   enableEnhancement: true
 * });
 * ```
 */
export function createNativeClient(config?: Partial<HunyuanNativeClientConfig>): HunyuanNativeClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: env vars > sandbox mock > empty string
  const secretId = process.env.TENCENTCLOUD_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = process.env.TENCENTCLOUD_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = process.env.TENCENTCLOUD_REGION || 'ap-guangzhou';

  return new HunyuanNativeClient({
    secretId,
    secretKey,
    region,
    timeout: config?.timeout,
    maxTokens: config?.maxTokens,
    enableEnhancement: config?.enableEnhancement !== undefined ? config.enableEnhancement : true
  });
}
