/**
 * Tencent Cloud Hunyuan Large Language Model SDK Wrapper
 * 
 * Production-ready TypeScript wrapper for Tencent Cloud Hunyuan LLM.
 * Provides a clean, type-safe API for text generation and conversations.
 * 
 * @example
 * ```typescript
 * import { HunyuanClient } from './hunyuan-chat';
 * 
 * const client = new HunyuanClient({
 *   secretId: process.env.TENCENTCLOUD_SECRET_ID!,
 *   secretKey: process.env.TENCENTCLOUD_SECRET_KEY!,
 *   region: 'ap-guangzhou'
 * });
 * 
 * const result = await client.chatCompletions([
 *   { Role: 'user', Content: 'Hello' }
 * ]);
 * ```
 * 
 * @see https://cloud.tencent.com/document/product/1729/105701
 * @version 1.0.0
 */

import * as tencentcloud from 'tencentcloud-sdk-nodejs-hunyuan';

const HunyuanSDKClient = tencentcloud.hunyuan.v20230901.Client;
type HunyuanSDKClientType = InstanceType<typeof HunyuanSDKClient>;

/**
 * Client configuration options
 */
export interface HunyuanClientConfig {
  /** Tencent Cloud SecretId */
  secretId: string;
  /** Tencent Cloud SecretKey */
  secretKey: string;
  /** Region, such as ap-guangzhou, ap-beijing */
  region?: string;
  /** API endpoint (optional) */
  endpoint?: string;
  /** Request timeout (seconds), default 60 */
  timeout?: number;
}

/**
 * Message object
 */
export interface Message {
  /** Role: system, user, assistant */
  Role: string;
  /** Message content */
  Content: string;
}

/**
 * Chat completion options
 */
export interface ChatCompletionOptions {
  /** Model name, default hunyuan-turbo */
  Model?: string;
  /** Whether to stream output, default false */
  Stream?: boolean;
  /** Nucleus sampling parameter [0.0, 1.0] */
  TopP?: number;
  /** Temperature parameter [0.0, 2.0], controls randomness */
  Temperature?: number;
  /** Whether to enable search enhancement */
  EnableEnhancement?: boolean;
  /** Whether to return search information */
  SearchInfo?: boolean;
  /** Random seed [1, 10000], for reproducible output */
  Seed?: number;
}

/**
 * Chat completion response
 */
export interface ChatCompletionResponse {
  /** Request ID */
  RequestId: string;
  /** Generated reply choices */
  Choices: Array<{
    /** Message content */
    Message: {
      Role: string;
      Content: string;
    };
    /** Finish reason: stop, tool_calls, sensitive */
    FinishReason: string;
  }>;
  /** Token usage */
  Usage: {
    /** Input token count */
    PromptTokens: number;
    /** Output token count */
    CompletionTokens: number;
    /** Total token count */
    TotalTokens: number;
  };
  /** Creation timestamp */
  Created: number;
  /** Response ID */
  Id: string;
}

/**
 * Stream response chunk
 */
export interface StreamChunk {
  /** Incremental content */
  Delta: {
    Role?: string;
    Content: string;
  };
  /** Finish reason */
  FinishReason: string;
}

/**
 * HunyuanClient provides a clean wrapper for Tencent Cloud Hunyuan LLM
 */
export class HunyuanClient {
  private client: HunyuanSDKClientType;
  private config: HunyuanClientConfig;

  constructor(config: HunyuanClientConfig) {
    this.validateConfig(config);
    this.config = {
      region: 'ap-guangzhou',
      timeout: 60,
      ...config
    };
    this.client = this.initializeClient();
  }

  /**
   * Validate configuration parameters
   */
  private validateConfig(config: HunyuanClientConfig): void {
    if (!config.secretId || config.secretId.trim() === '') {
      throw new Error('SecretId cannot be empty');
    }
    if (!config.secretKey || config.secretKey.trim() === '') {
      throw new Error('SecretKey cannot be empty');
    }
    if (config.timeout && (config.timeout < 1 || config.timeout > 300)) {
      throw new Error('Timeout must be between 1-300 seconds');
    }
  }

  /**
   * Initialize the underlying SDK client
   */
  private initializeClient(): HunyuanSDKClientType {
    // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND credentials are mock
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = this.config.secretId === 'mock_secret_id' || this.config.secretKey === 'mock_secret_key';
    const useSandbox = isSandbox && isMockCredentials;
    
    return new HunyuanSDKClient({
      credential: {
        secretId: this.config.secretId,
        secretKey: this.config.secretKey
      },
      region: this.config.region,
      profile: {
        httpProfile: {
          endpoint: this.config.endpoint || (useSandbox ? 'hunyuan.tencent_cloud.auth-proxy.local' : ''),
          reqTimeout: this.config.timeout,
          protocol: useSandbox ? 'http:' : 'https:',
        }
      }
    });
  }

  /**
   * Chat completion interface (non-streaming)
   */
  async chatCompletions(
    messages: Message[],
    options?: ChatCompletionOptions
  ): Promise<ChatCompletionResponse> {
    try {
      this.validateMessages(messages);

      const params = {
        Model: options?.Model || 'hunyuan-turbo',
        Messages: messages,
        Stream: false,
        TopP: options?.TopP,
        Temperature: options?.Temperature,
        EnableEnhancement: options?.EnableEnhancement,
        SearchInfo: options?.SearchInfo,
        Seed: options?.Seed
      };

      const response = await this.client.ChatCompletions(params);

      return {
        RequestId: response.RequestId || '',
        Choices: response.Choices?.map((choice: any) => ({
          Message: {
            Role: choice.Message?.Role || 'assistant',
            Content: choice.Message?.Content || ''
          },
          FinishReason: choice.FinishReason || 'stop'
        })) || [],
        Usage: {
          PromptTokens: response.Usage?.PromptTokens || 0,
          CompletionTokens: response.Usage?.CompletionTokens || 0,
          TotalTokens: response.Usage?.TotalTokens || 0
        },
        Created: response.Created || 0,
        Id: response.Id || ''
      };
    } catch (error: any) {
      throw new Error(`Hunyuan API call failed: ${error.message}`);
    }
  }

  /**
   * Chat completion interface (streaming)
   */
  async chatCompletionsStream(
    messages: Message[],
    options?: ChatCompletionOptions,
    onChunk?: (chunk: StreamChunk) => void
  ): Promise<ChatCompletionResponse> {
    try {
      this.validateMessages(messages);

      const params = {
        Model: options?.Model || 'hunyuan-turbo',
        Messages: messages,
        Stream: true,
        TopP: options?.TopP,
        Temperature: options?.Temperature,
        EnableEnhancement: options?.EnableEnhancement,
        SearchInfo: options?.SearchInfo,
        Seed: options?.Seed
      };

      const response = await this.client.ChatCompletions(params);

      let fullContent = '';
      let requestId = '';
      let created = 0;
      let id = '';
      let finishReason = '';
      let usage = { PromptTokens: 0, CompletionTokens: 0, TotalTokens: 0 };

      if (response && typeof (response as any)[Symbol.asyncIterator] === 'function') {
        for await (const event of response as AsyncIterable<any>) {
          const data = event.data;
          if (data) {
            try {
              const parsed = typeof data === 'string' ? JSON.parse(data) : data;
              
              if (parsed.Choices && parsed.Choices.length > 0) {
                const choice = parsed.Choices[0];
                const deltaContent = choice.Delta?.Content || '';
                
                if (deltaContent) {
                  fullContent += deltaContent;
                  
                  const chunk: StreamChunk = {
                    Delta: {
                      Role: choice.Delta?.Role,
                      Content: deltaContent
                    },
                    FinishReason: choice.FinishReason || ''
                  };
                  
                  if (onChunk) {
                    onChunk(chunk);
                  }
                }
                
                if (choice.FinishReason) {
                  finishReason = choice.FinishReason;
                }
              }
              
              if (parsed.RequestId) requestId = parsed.RequestId;
              if (parsed.Created) created = parsed.Created;
              if (parsed.Id) id = parsed.Id;
              if (parsed.Usage) {
                usage = {
                  PromptTokens: parsed.Usage.PromptTokens || 0,
                  CompletionTokens: parsed.Usage.CompletionTokens || 0,
                  TotalTokens: parsed.Usage.TotalTokens || 0
                };
              }
            } catch (parseError) {
              // Skip non-JSON data
            }
          }
        }
      } else {
        if (response.Choices && response.Choices.length > 0) {
          for (const choice of response.Choices as any[]) {
            const chunk: StreamChunk = {
              Delta: {
                Role: choice.Message?.Role,
                Content: choice.Message?.Content || ''
              },
              FinishReason: choice.FinishReason || ''
            };

            fullContent += chunk.Delta.Content;

            if (onChunk) {
              onChunk(chunk);
            }
          }
        }

        requestId = response.RequestId || '';
        created = response.Created || 0;
        id = response.Id || '';
        if (response.Usage) {
          usage = {
            PromptTokens: response.Usage.PromptTokens || 0,
            CompletionTokens: response.Usage.CompletionTokens || 0,
            TotalTokens: response.Usage.TotalTokens || 0
          };
        }
      }

      return {
        RequestId: requestId,
        Choices: [{
          Message: {
            Role: 'assistant',
            Content: fullContent
          },
          FinishReason: finishReason || 'stop'
        }],
        Usage: usage,
        Created: created,
        Id: id
      };
    } catch (error: any) {
      throw new Error(`Hunyuan streaming API call failed: ${error.message}`);
    }
  }

  /**
   * Validate message array
   */
  private validateMessages(messages: Message[]): void {
    if (!messages || messages.length === 0) {
      throw new Error('Message array cannot be empty');
    }
    if (messages.length > 40) {
      throw new Error('Message array supports maximum 40 messages');
    }

    for (let i = 0; i < messages.length; i++) {
      const msg = messages[i];
      if (!msg.Role || !msg.Content) {
        throw new Error(`Message ${i} missing Role or Content field`);
      }
      if (!['system', 'user', 'assistant'].includes(msg.Role)) {
        throw new Error(`Message ${i} Role must be system, user, or assistant`);
      }
    }

    if (messages[0].Role === 'system' && messages.length > 1) {
      for (let i = 1; i < messages.length; i++) {
        if (messages[i].Role === 'system') {
          throw new Error('system role can only appear at the beginning of message array');
        }
      }
    }
  }

  /**
   * Simple text generation
   */
  async generateText(
    prompt: string,
    options?: ChatCompletionOptions
  ): Promise<string> {
    if (!prompt || prompt.trim() === '') {
      throw new Error('Prompt text cannot be empty');
    }

    const result = await this.chatCompletions(
      [{ Role: 'user', Content: prompt }],
      options
    );

    return result.Choices[0].Message.Content;
  }
}

/**
 * Create HunyuanClient instance using environment variables
 */
export function createClient(
  config?: Partial<HunyuanClientConfig>
): HunyuanClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  const secretId = process.env.TENCENTCLOUD_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = process.env.TENCENTCLOUD_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = process.env.TENCENTCLOUD_REGION || 'ap-guangzhou';

  return new HunyuanClient({
    secretId,
    secretKey,
    region,
    endpoint: config?.endpoint,
    timeout: config?.timeout
  });
}
