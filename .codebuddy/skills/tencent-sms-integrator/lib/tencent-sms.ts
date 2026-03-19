/**
 * Tencent Cloud SMS Service SDK Wrapper
 *
 * Production-grade TypeScript wrapper providing type-safe SMS sending APIs.
 * Supports single send, batch send, and delivery status queries.
 *
 * @example
 * ```typescript
 * import { TencentSMSClient, createClient } from './tencent-sms';
 *
 * const client = createClient();
 *
 * // Send verification code
 * const result = await client.sendSms({
 *   phoneNumber: '+8613800138000',
 *   templateId: '449739',
 *   templateParams: ['123456', '5']
 * });
 *
 * console.log('Send result:', result);
 * ```
 *
 * @see https://cloud.tencent.com/document/product/382/43197
 * @version 1.0.0
 */

import tencentcloud from 'tencentcloud-sdk-nodejs';

const SmsClient = tencentcloud.sms.v20210111.Client;

/**
 * Tencent Cloud SMS client configuration
 */
export interface TencentSMSConfig {
  /** Tencent Cloud SecretId */
  secretId: string;
  /** Tencent Cloud SecretKey */
  secretKey: string;
  /** SMS application ID, e.g., 1400787878 */
  smsSdkAppId: string;
  /** SMS signature content, e.g., "TencentCloud" */
  signName: string;
  /** Service region, e.g., ap-guangzhou, ap-beijing */
  region?: string;
  /** Request timeout in seconds, default 10 */
  timeout?: number;
}

/**
 * Send SMS request parameters
 */
export interface SendSmsRequest {
  /** Phone number in E.164 format: +[country code][number], e.g., +8613800138000 */
  phoneNumber: string;
  /** SMS template ID */
  templateId: string;
  /** Template parameters, corresponding to template variables */
  templateParams?: string[];
  /** Session context, returned as-is in response */
  sessionContext?: string;
  /** Extension code (optional) */
  extendCode?: string;
  /** Sender ID (optional for international SMS) */
  senderId?: string;
}

/**
 * Batch send SMS request parameters
 */
export interface SendBatchSmsRequest {
  /** Phone numbers array in E.164 format, max 200 */
  phoneNumbers: string[];
  /** SMS template ID */
  templateId: string;
  /** Template parameters, corresponding to template variables */
  templateParams?: string[];
  /** Session context, returned as-is in response */
  sessionContext?: string;
  /** Extension code (optional) */
  extendCode?: string;
  /** Sender ID (optional for international SMS) */
  senderId?: string;
}

/**
 * Single send status
 */
export interface SendStatus {
  /** Serial number */
  serialNo: string;
  /** Phone number */
  phoneNumber: string;
  /** Number of billable messages */
  fee: number;
  /** Session context */
  sessionContext: string;
  /** Send status code, "Ok" means success */
  code: string;
  /** Send status description */
  message: string;
  /** ISO country code, e.g., CN */
  isoCode: string;
}

/**
 * Send SMS response
 */
export interface SendSmsResponse {
  /** Send status array */
  sendStatusSet: SendStatus[];
  /** Request ID */
  requestId: string;
}

/**
 * Pull SMS status request
 */
export interface PullSmsStatusRequest {
  /** Maximum number of records to pull, max 100 */
  limit: number;
}

/**
 * SMS delivery status
 */
export interface SmsStatus {
  /** Time when user actually received the SMS */
  userReceiveTime: string;
  /** Actual SMS delivery status */
  reportStatus: string;
  /** User SMS receipt status code */
  errmsg: string;
  /** User SMS receipt status description */
  description: string;
  /** Phone number */
  phoneNumber: string;
  /** Serial number */
  serialNo: string;
  /** Session context */
  sessionContext: string;
}

/**
 * Tencent Cloud SMS Client
 *
 * Provides SMS sending functionality wrapper, including single send, batch send, and status queries
 */
export class TencentSMSClient {
  private client: any;
  private config: Required<TencentSMSConfig>;

  constructor(config: TencentSMSConfig) {
    this.validateConfig(config);
    this.config = {
      region: 'ap-guangzhou',
      timeout: 10,
      ...config
    } as Required<TencentSMSConfig>;
    this.client = this.initializeClient();
  }

  /**
   * Validate configuration parameters
   */
  private validateConfig(config: TencentSMSConfig): void {
    if (!config.secretId) {
      throw new Error('SecretId is required');
    }
    if (!config.secretKey) {
      throw new Error('SecretKey is required');
    }
    if (!config.smsSdkAppId) {
      throw new Error('SmsSdkAppId is required');
    }
    if (!config.signName) {
      throw new Error('SignName is required');
    }
    if (config.timeout && config.timeout <= 0) {
      throw new Error('Timeout must be greater than 0');
    }
  }

  /**
   * Initialize Tencent Cloud SMS client
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
          endpoint: useSandbox ? 'sms.tencent_cloud.auth-proxy.local' : 'sms.tencentcloudapi.com',
          reqMethod: 'POST',
          reqTimeout: this.config.timeout,
          protocol: useSandbox ? 'http:' : 'https:',
        },
      },
    };

    return new SmsClient(clientConfig);
  }

  /**
   * Send single SMS
   *
   * Send SMS to a single phone number, suitable for verification codes, notifications, etc.
   *
   * @param request - Send request parameters
   * @returns Send result
   * @throws Error if sending fails
   *
   * @example
   * ```typescript
   * const result = await client.sendSms({
   *   phoneNumber: '+8613800138000',
   *   templateId: '449739',
   *   templateParams: ['123456', '5']  // code, expiry
   * });
   *
   * if (result.sendStatusSet[0].code === 'Ok') {
   *   console.log('Send successful');
   * }
   * ```
   */
  async sendSms(request: SendSmsRequest): Promise<SendSmsResponse> {
    return this.sendBatchSms({
      phoneNumbers: [request.phoneNumber],
      templateId: request.templateId,
      templateParams: request.templateParams,
      sessionContext: request.sessionContext,
      extendCode: request.extendCode,
      senderId: request.senderId
    });
  }

  /**
   * Batch send SMS
   *
   * Send the same SMS content to multiple phone numbers, max 200 numbers per request.
   *
   * @param request - Batch send request parameters
   * @returns Send result array
   * @throws Error if sending fails
   *
   * @example
   * ```typescript
   * const result = await client.sendBatchSms({
   *   phoneNumbers: ['+8613800138000', '+8613800138001'],
   *   templateId: '449739',
   *   templateParams: ['Order shipped']
   * });
   *
   * result.sendStatusSet.forEach(status => {
   *   console.log(`${status.phoneNumber}: ${status.code}`);
   * });
   * ```
   */
  async sendBatchSms(request: SendBatchSmsRequest): Promise<SendSmsResponse> {
    try {
      // Validate phone number count
      if (request.phoneNumbers.length > 200) {
        throw new Error('Maximum 200 phone numbers per request');
      }

      if (request.phoneNumbers.length === 0) {
        throw new Error('At least one phone number is required');
      }

      const params: any = {
        SmsSdkAppId: this.config.smsSdkAppId,
        SignName: this.config.signName,
        TemplateId: request.templateId,
        PhoneNumberSet: request.phoneNumbers,
      };

      // Optional parameters
      if (request.templateParams && request.templateParams.length > 0) {
        params.TemplateParamSet = request.templateParams;
      }
      if (request.sessionContext) {
        params.SessionContext = request.sessionContext;
      }
      if (request.extendCode) {
        params.ExtendCode = request.extendCode;
      }
      if (request.senderId) {
        params.SenderId = request.senderId;
      }

      const response = await this.client.SendSms(params);

      if (!response || !response.SendStatusSet) {
        throw new Error('Failed to send SMS: Invalid response');
      }

      return {
        sendStatusSet: response.SendStatusSet.map((status: any) => ({
          serialNo: status.SerialNo,
          phoneNumber: status.PhoneNumber,
          fee: status.Fee,
          sessionContext: status.SessionContext || '',
          code: status.Code,
          message: status.Message,
          isoCode: status.IsoCode || ''
        })),
        requestId: response.RequestId
      };
    } catch (error: any) {
      throw new Error(`Failed to send SMS: ${error.message}`);
    }
  }

  /**
   * Pull SMS delivery status
   *
   * Pull SMS delivery receipts to check actual delivery status.
   *
   * @param limit - Maximum number of records to pull, max 100
   * @returns SMS status array
   * @throws Error if query fails
   *
   * @example
   * ```typescript
   * const statuses = await client.pullSmsStatus(10);
   * statuses.forEach(status => {
   *   console.log(`${status.phoneNumber}: ${status.reportStatus}`);
   * });
   * ```
   */
  async pullSmsStatus(limit: number = 10): Promise<SmsStatus[]> {
    try {
      if (limit > 100) {
        throw new Error('Maximum limit is 100');
      }

      const params = {
        SmsSdkAppId: this.config.smsSdkAppId,
        Limit: limit
      };

      const response = await this.client.PullSmsSendStatus(params);

      if (!response || !response.PullSmsSendStatusSet) {
        return [];
      }

      return response.PullSmsSendStatusSet.map((status: any) => ({
        userReceiveTime: status.UserReceiveTime,
        reportStatus: status.ReportStatus,
        errmsg: status.Errmsg,
        description: status.Description,
        phoneNumber: status.PhoneNumber,
        serialNo: status.SerialNo,
        sessionContext: status.SessionContext || ''
      }));
    } catch (error: any) {
      throw new Error(`Failed to pull SMS status: ${error.message}`);
    }
  }

  /**
   * Send verification code SMS (convenience method)
   *
   * Simplified verification code sending method with auto-generated code.
   *
   * @param phoneNumber - Phone number in E.164 format
   * @param templateId - Verification code template ID
   * @param codeLength - Verification code length, default 6 digits
   * @param expireMinutes - Expiry time in minutes, default 5 minutes
   * @returns Send result and verification code
   * @throws Error if sending fails
   *
   * @example
   * ```typescript
   * const { code, success } = await client.sendVerificationCode(
   *   '+8613800138000',
   *   '449739',
   *   6,
   *   5
   * );
   *
   * if (success) {
   *   // Store code for later verification
   *   console.log('Verification code:', code);
   * }
   * ```
   */
  async sendVerificationCode(
    phoneNumber: string,
    templateId: string,
    codeLength: number = 6,
    expireMinutes: number = 5
  ): Promise<{ code: string; success: boolean; result: SendSmsResponse }> {
    // Generate numeric verification code
    const code = this.generateCode(codeLength);

    const result = await this.sendSms({
      phoneNumber,
      templateId,
      templateParams: [code, expireMinutes.toString()]
    });

    const success = result.sendStatusSet.length > 0 &&
                    result.sendStatusSet[0].code === 'Ok';

    return { code, success, result };
  }

  /**
   * Generate random numeric verification code
   */
  private generateCode(length: number): string {
    let code = '';
    for (let i = 0; i < length; i++) {
      code += Math.floor(Math.random() * 10).toString();
    }
    return code;
  }
}

/**
 * Create SMS client instance using environment variables
 *
 * Reads configuration from environment variables:
 * - TENCENT_SMS_SECRET_ID
 * - TENCENT_SMS_SECRET_KEY
 * - TENCENT_SMS_SDK_APP_ID
 * - TENCENT_SMS_SIGN_NAME
 * - TENCENT_SMS_REGION (optional, default ap-guangzhou)
 *
 * @param config - Optional additional configuration, overrides environment variables
 * @returns SMS client instance
 *
 * @example
 * ```typescript
 * import { createClient } from './tencent-sms';
 *
 * // Use environment variables
 * const client = createClient();
 *
 * // Override some configuration
 * const client2 = createClient({ region: 'ap-beijing' });
 * ```
 */
export function createClient(config?: Partial<TencentSMSConfig>): TencentSMSClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: config > env vars > sandbox mock
  const secretId = config?.secretId || process.env.TENCENT_SMS_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = config?.secretKey || process.env.TENCENT_SMS_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  
  return new TencentSMSClient({
    secretId,
    secretKey,
    smsSdkAppId: config?.smsSdkAppId || process.env.TENCENT_SMS_SDK_APP_ID!,
    signName: config?.signName || process.env.TENCENT_SMS_SIGN_NAME!,
    region: config?.region || process.env.TENCENT_SMS_REGION || 'ap-guangzhou',
    timeout: config?.timeout || 10
  });
}
