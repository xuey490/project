/**
 * Tavily Web Search Client Wrapper
 *
 * Wraps @tavily/core SDK with environment variable configuration and type-safe interfaces
 */

import { tavily, TavilyClient } from '@tavily/core';

export interface WebSearchConfig {
  apiKey?: string;
}

export interface SearchOptions {
  searchDepth?: 'basic' | 'advanced';
  topic?: 'general' | 'news' | 'finance';
  maxResults?: number;
  includeAnswer?: boolean | 'basic' | 'advanced';
  includeRawContent?: false | 'markdown' | 'text';
  includeDomains?: string[];
  excludeDomains?: string[];
  timeRange?: 'day' | 'week' | 'month' | 'year';
}

export interface ExtractOptions {
  format?: 'markdown' | 'text';
  extractDepth?: 'basic' | 'advanced';
  includeImages?: boolean;
}

export interface CrawlOptions {
  maxDepth?: number;
  limit?: number;
  instructions?: string;
  format?: 'markdown' | 'text';
}

export interface MapOptions {
  maxDepth?: number;
  limit?: number;
  instructions?: string;
}

export interface ResearchOptions {
  model?: 'mini' | 'pro' | 'auto';
  citationFormat?: 'numbered' | 'mla' | 'apa' | 'chicago';
}

/**
 * Web Search Client
 *
 * @example
 * const client = createClient();
 * const results = await client.search('AI news');
 */
export class WebSearchClient {
  private client: TavilyClient;

  constructor(config?: WebSearchConfig) {
    const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
    
    // Priority: config > env vars > sandbox mock
    const apiKey = config?.apiKey || process.env.TAVILY_WEB_SEARCH_API_KEY || (isSandbox ? 'mock_tavily_api_key' : '');

    if (!apiKey) {
      throw new Error(
        'TAVILY_WEB_SEARCH_API_KEY is required. Set it via environment variable or pass apiKey in config.'
      );
    }

    // Check if using sandbox proxy
    const isMockCredentials = apiKey === 'mock_tavily_api_key';
    const useSandbox = isSandbox && isMockCredentials;

    if (useSandbox) {
      // Use sandbox proxy endpoint
      this.client = tavily({ 
        apiKey,
        apiBaseURL: 'http://tavily.auth-proxy.local'
      });
    } else {
      this.client = tavily({ apiKey });
    }
  }

  /**
   * Perform web search
   */
  async search(query: string, options?: SearchOptions) {
    return this.client.search(query, options);
  }

  /**
   * Extract content from URLs
   */
  async extract(urls: string[], options?: ExtractOptions) {
    return this.client.extract(urls, options);
  }

  /**
   * Crawl a website
   */
  async crawl(url: string, options?: CrawlOptions) {
    return this.client.crawl(url, options);
  }

  /**
   * Map website structure
   */
  async map(url: string, options?: MapOptions) {
    return this.client.map(url, options);
  }

  /**
   * Create deep research task
   */
  async research(query: string, options?: ResearchOptions) {
    return this.client.research(query, options);
  }

  /**
   * Get research task results
   */
  async getResearch(requestId: string) {
    return this.client.getResearch(requestId);
  }
}

/**
 * Create Web Search client instance
 *
 * @example
 * // Using environment variables
 * const client = createClient();
 *
 * // Explicit API Key
 * const client = createClient({ apiKey: 'tvly-xxx' });
 */
export function createClient(config?: WebSearchConfig): WebSearchClient {
  return new WebSearchClient(config);
}
