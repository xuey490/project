/**
 * Google GenAI Imagen SDK Wrapper
 * 
 * Production-ready TypeScript wrapper for Google Gemini image generation API.
 * Default model: gemini-2.5-flash-image
 * 
 * @example
 * ```typescript
 * import { GenAIImageClient } from './google-genai-imagen';
 * 
 * // Default usage
 * const client = new GenAIImageClient({
 *   apiKey: process.env.GEMINI_API_KEY
 * });
 * 
 * // With custom endpoint
 * const client = new GenAIImageClient({
 *   apiKey: process.env.GEMINI_API_KEY,
 *   baseURL: 'https://custom-api.example.com'
 * });
 * 
 * const result = await client.generateImage(
 *   'A cute red panda in a bamboo forest',
 *   { aspectRatio: '16:9' }
 * );
 * ```
 * 
 * @see https://ai.google.dev/gemini-api/docs/image-generation
 * @version 1.1.0
 */

import { GoogleGenAI } from '@google/genai';

/**
 * Configuration options for GenAIImageClient
 */
export interface GenAIImageClientConfig {
  /** Google AI API key */
  apiKey: string;
  /** Default model to use for generation */
  defaultModel?: string;
  /** Custom API endpoint/base URL (optional) */
  baseURL?: string;
}

/**
 * Supported aspect ratios for image generation
 */
export type AspectRatio = '1:1' | '2:3' | '3:2' | '3:4' | '4:3' | '4:5' | '5:4' | '9:16' | '16:9' | '21:9';

/**
 * Supported image sizes (only for gemini-3-pro-image-preview)
 */
export type ImageSize = '1K' | '2K' | '4K';

/**
 * Model names for image generation
 * Common models: 'gemini-2.5-flash-image' (default), 'gemini-2.0-flash-exp', 'nano-banana-fast'
 */
export type ImageModel = string;

/**
 * Options for image generation
 */
export interface GenerateImageOptions {
  /** Aspect ratio of the generated image */
  aspectRatio?: AspectRatio;
  /** Image size (only for gemini-3-pro-image-preview, must be uppercase) */
  imageSize?: ImageSize;
  /** Whether to include text description in response */
  includeText?: boolean;
  /** Model to use (overrides default) */
  model?: ImageModel;
  /** Enable Google Search grounding (only for gemini-3-pro-image-preview) */
  useGoogleSearch?: boolean;
}

/**
 * Result from image generation or editing
 */
export interface ImageGenerationResult {
  /** Generated image data as Buffer */
  imageData: Buffer;
  /** Optional text description */
  text?: string;
  /** MIME type of the image */
  mimeType: string;
  /** Grounding metadata if Google Search was used */
  groundingMetadata?: unknown;
}


/**
 * GenAIImageClient provides a clean wrapper around Google Gemini image generation API
 */
export class GenAIImageClient {
  private client: GoogleGenAI;
  private defaultModel: ImageModel;
  
  constructor(config: GenAIImageClientConfig) {
    this.validateConfig(config);
    // Use config, then env var, then Google's default
    this.defaultModel = config.defaultModel || process.env.GOOGLE_GENAI_MODEL || 'gemini-2.5-flash-image';
    
    // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND API key is mock
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    const isMockCredentials = config.apiKey === 'mock_api_key';
    const useSandbox = isSandbox && isMockCredentials;
    const baseURL = config.baseURL || process.env.GOOGLE_GENAI_BASE_URL || (useSandbox ? 'http://google.openai.auth-proxy.local' : undefined);
    
    // Initialize client with custom baseURL if provided or if using sandbox
    const clientConfig: any = { apiKey: config.apiKey };
    if (baseURL) {
      clientConfig.httpOptions = {
        baseUrl: baseURL
      };
    }
    
    this.client = new GoogleGenAI(clientConfig);
  }
  
  /**
   * Validate configuration
   */
  private validateConfig(config: GenAIImageClientConfig): void {
    if (!config.apiKey) {
      throw new Error('API key is required');
    }
    if (config.apiKey.length < 20 && config.apiKey !== 'mock_api_key') {
      throw new Error('Invalid API key format');
    }
  }
  
  /**
   * Build generation config from options
   */
  private buildConfig(options: GenerateImageOptions = {}): any {
    const responseModalities: Array<'TEXT' | 'IMAGE'> = ['IMAGE'];
    if (options.includeText !== false) {
      responseModalities.unshift('TEXT');
    }
    
    const config: any = {
      responseModalities,
    };
    
    // Add image config if aspect ratio or size specified
    if (options.aspectRatio || options.imageSize) {
      config.imageConfig = {};
      if (options.aspectRatio) {
        config.imageConfig.aspectRatio = options.aspectRatio;
      }
      if (options.imageSize) {
        // Validate size is uppercase
        if (options.imageSize !== options.imageSize.toUpperCase() as ImageSize) {
          throw new Error('imageSize must be uppercase (1K, 2K, 4K)');
        }
        config.imageConfig.imageSize = options.imageSize;
      }
    }
    
    // Add Google Search tool if enabled
    if (options.useGoogleSearch) {
      config.tools = [{ google_search: {} }];
    }
    
    return config;
  }
  
  /**
   * Extract image data from response
   */
  private extractImageData(response: any): ImageGenerationResult {
    if (!response.candidates || response.candidates.length === 0) {
      throw new Error('No candidates in response');
    }
    
    const parts = response.candidates[0].content.parts;
    if (!parts || parts.length === 0) {
      throw new Error('No parts in response');
    }
    
    let imageData: Buffer | null = null;
    let mimeType = 'image/png';
    let text: string | undefined;
    
    for (const part of parts) {
      if (part.text && !part.thought) {
        text = part.text;
      }
      if (part.inlineData) {
        mimeType = part.inlineData.mimeType || 'image/png';
        imageData = Buffer.from(part.inlineData.data, 'base64');
      }
    }
    
    if (!imageData) {
      throw new Error('No image data in response');
    }
    
    const result: ImageGenerationResult = {
      imageData,
      mimeType,
      text,
    };
    
    // Add grounding metadata if present
    if (response.groundingMetadata) {
      result.groundingMetadata = response.groundingMetadata;
    }
    
    return result;
  }
  
  /**
   * Generate an image from a text prompt
   * 
   * @param prompt - Text description of the image to generate
   * @param options - Optional generation settings
   * @returns Generated image data
   * @throws Error if generation fails
   * 
   * @example
   * ```typescript
   * const result = await client.generateImage(
   *   'A photorealistic portrait of a ceramicist',
   *   { aspectRatio: '1:1' }
   * );
   * 
   * fs.writeFileSync('output.png', result.imageData);
   * console.log('Description:', result.text);
   * ```
   */
  async generateImage(
    prompt: string,
    options: GenerateImageOptions = {}
  ): Promise<ImageGenerationResult> {
    if (!prompt || prompt.trim().length === 0) {
      throw new Error('Prompt is required');
    }
    
    const model = options.model || this.defaultModel;
    const config = this.buildConfig(options);
    
    try {
      const response = await this.client.models.generateContent({
        model,
        contents: prompt,
        config,
      });
      
      return this.extractImageData(response);
    } catch (error: any) {
      throw new Error(`Image generation failed: ${error.message}`);
    }
  }
  
  /**
   * Edit an existing image with a text instruction
   * 
   * @param instruction - Text description of how to edit the image
   * @param imageBuffer - Source image as Buffer
   * @param options - Optional generation settings
   * @returns Edited image data
   * @throws Error if editing fails
   * 
   * @example
   * ```typescript
   * const originalImage = fs.readFileSync('cat.jpg');
   * const result = await client.editImage(
   *   'Add a wizard hat to this cat',
   *   originalImage,
   *   { aspectRatio: '1:1' }
   * );
   * 
   * fs.writeFileSync('cat_with_hat.png', result.imageData);
   * ```
   */
  async editImage(
    instruction: string,
    imageBuffer: Buffer,
    options: GenerateImageOptions = {}
  ): Promise<ImageGenerationResult> {
    if (!instruction || instruction.trim().length === 0) {
      throw new Error('Instruction is required');
    }
    if (!imageBuffer || imageBuffer.length === 0) {
      throw new Error('Image buffer is required');
    }
    
    const model = options.model || this.defaultModel;
    const config = this.buildConfig(options);
    
    // Detect MIME type from buffer
    const mimeType = this.detectMimeType(imageBuffer);
    const base64Data = imageBuffer.toString('base64');
    
    try {
      const response = await this.client.models.generateContent({
        model,
        contents: [
          instruction,
          {
            inlineData: {
              mimeType,
              data: base64Data,
            },
          },
        ],
        config,
      });
      
      return this.extractImageData(response);
    } catch (error: any) {
      throw new Error(`Image editing failed: ${error.message}`);
    }
  }
  
  
  /**
   * Detect MIME type from buffer magic bytes
   */
  private detectMimeType(buffer: Buffer): string {
    if (buffer.length < 4) {
      return 'image/jpeg'; // default fallback
    }
    
    // PNG: 89 50 4E 47
    if (buffer[0] === 0x89 && buffer[1] === 0x50 && buffer[2] === 0x4E && buffer[3] === 0x47) {
      return 'image/png';
    }
    
    // JPEG: FF D8 FF
    if (buffer[0] === 0xFF && buffer[1] === 0xD8 && buffer[2] === 0xFF) {
      return 'image/jpeg';
    }
    
    // WebP: 52 49 46 46 ... 57 45 42 50
    if (buffer[0] === 0x52 && buffer[1] === 0x49 && buffer[2] === 0x46 && buffer[3] === 0x46) {
      if (buffer.length >= 12 && buffer[8] === 0x57 && buffer[9] === 0x45 && buffer[10] === 0x42 && buffer[11] === 0x50) {
        return 'image/webp';
      }
    }
    
    // Default fallback
    return 'image/jpeg';
  }
}

/**
 * Create a GenAIImageClient instance with environment variables
 * 
 * @param config - Optional partial configuration to override env vars
 * @returns Configured client instance
 * 
 * @example
 * ```typescript
 * import { createClient } from './google-genai-imagen';
 * 
 * const client = createClient();
 * const result = await client.generateImage('A cute red panda');
 * ```
 */
export function createClient(config: Partial<GenAIImageClientConfig> = {}): GenAIImageClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: env vars > sandbox mock > empty string
  const apiKey = config?.apiKey || process.env.GOOGLE_GENAI_API_KEY || (isSandbox ? 'mock_api_key' : '');
  const defaultModel = config?.defaultModel || process.env.GOOGLE_GENAI_MODEL || 'gemini-2.5-flash-image';
  const baseURL = config?.baseURL || process.env.GOOGLE_GENAI_BASE_URL;
  
  return new GenAIImageClient({
    apiKey,
    defaultModel,
    baseURL,
  });
}
