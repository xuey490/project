/**
 * Tencent Cloud Face Compare Client Wrapper
 *
 * Provides face similarity detection for identity verification.
 * Only supports face comparison (CompareFace API), NOT face enrollment.
 *
 * @example
 * const client = createFaceCompareClient();
 * const result = await compareFaces(client, {
 *   imageA: base64ReferencePhoto,
 *   imageB: base64CapturedPhoto
 * });
 *
 * if (result.isSamePerson) {
 *   console.log('Verified:', result.score);
 * }
 */

import * as tencentcloud from 'tencentcloud-sdk-nodejs-iai';

const IaiClient = tencentcloud.iai.v20200303.Client;

export type FaceCompareClient = InstanceType<typeof IaiClient>;

export interface FaceCompareClientConfig {
  secretId: string;
  secretKey: string;
  region: string;
}

export interface CompareFaceParams {
  /** Base64 encoded image A (reference photo) */
  imageA?: string;
  /** Base64 encoded image B (captured photo) */
  imageB?: string;
  /** URL of image A */
  urlA?: string;
  /** URL of image B */
  urlB?: string;
  /**
   * Quality control level (0-4)
   * 0: No check (default)
   * 1: Reject very blurry/occluded
   * 2: Reject overexposed/underexposed
   * 3: Reject minor issues
   * 4: Highest quality required
   */
  qualityControl?: 0 | 1 | 2 | 3 | 4;
}

export interface CompareFaceResult {
  /** Similarity score (0-100) */
  score: number;
  /** True if score >= 70 (same person threshold) */
  isSamePerson: boolean;
  /** True if score >= 80 (high confidence threshold) */
  isHighConfidence: boolean;
  /** Request ID for debugging */
  requestId: string;
}

/**
 * Creates a Tencent Cloud Face Compare client
 *
 * @param config - Optional configuration, defaults to environment variables
 * @returns Configured IAI client instance
 */
export function createFaceCompareClient(config?: Partial<FaceCompareClientConfig>): FaceCompareClient {
  const isSandbox = process.env.X_IDE_AUTH_PROXY !== undefined;
  
  // Priority: config > env vars > sandbox mock > empty string
  const secretId = config?.secretId || process.env.TENCENT_FACE_COMPARE_SECRET_ID || (isSandbox ? 'mock_secret_id' : '');
  const secretKey = config?.secretKey || process.env.TENCENT_FACE_COMPARE_SECRET_KEY || (isSandbox ? 'mock_secret_key' : '');
  const region = config?.region || process.env.TENCENT_FACE_COMPARE_REGION || 'ap-guangzhou';

  if (!secretId || !secretKey) {
    throw new Error(
      'Missing Tencent Cloud credentials. Set TENCENT_FACE_COMPARE_SECRET_ID and TENCENT_FACE_COMPARE_SECRET_KEY environment variables.'
    );
  }

  // Only use sandbox proxy if X_IDE_AUTH_PROXY exists AND credentials are mock
  const isMockCredentials = secretId === 'mock_secret_id' || secretKey === 'mock_secret_key';
  const useSandbox = isSandbox && isMockCredentials;

  const clientConfig = {
    credential: {
      secretId,
      secretKey,
    },
    region,
    profile: {
      httpProfile: {
        endpoint: useSandbox ? 'iai.tencent_cloud.auth-proxy.local' : 'iai.tencentcloudapi.com',
        protocol: useSandbox ? 'http:' : 'https:',
      },
    },
  };

  return new IaiClient(clientConfig);
}

/**
 * Compare two face images using base64 encoded data
 *
 * @param client - Face compare client instance
 * @param params - Comparison parameters with base64 images
 * @returns Comparison result with similarity score
 *
 * @example
 * const result = await compareFaces(client, {
 *   imageA: storedReferenceBase64,
 *   imageB: liveCaptureBase64,
 *   qualityControl: 2
 * });
 */
export async function compareFaces(
  client: FaceCompareClient,
  params: CompareFaceParams
): Promise<CompareFaceResult> {
  const requestParams: {
    ImageA?: string;
    ImageB?: string;
    UrlA?: string;
    UrlB?: string;
    QualityControl?: number;
  } = {};

  if (params.imageA) {
    requestParams.ImageA = params.imageA;
  }
  if (params.imageB) {
    requestParams.ImageB = params.imageB;
  }
  if (params.urlA) {
    requestParams.UrlA = params.urlA;
  }
  if (params.urlB) {
    requestParams.UrlB = params.urlB;
  }
  if (params.qualityControl !== undefined) {
    requestParams.QualityControl = params.qualityControl;
  }

  // Validate that at least one image source is provided for each
  const hasImageA = params.imageA || params.urlA;
  const hasImageB = params.imageB || params.urlB;

  if (!hasImageA || !hasImageB) {
    throw new Error('Both imageA/urlA and imageB/urlB must be provided');
  }

  const response = await client.CompareFace(requestParams);

  const score = response.Score ?? 0;

  return {
    score,
    isSamePerson: score >= 70,
    isHighConfidence: score >= 80,
    requestId: response.RequestId ?? '',
  };
}

/**
 * Compare two face images using URLs
 * Convenience wrapper for URL-based comparison
 *
 * @param client - Face compare client instance
 * @param params - URLs of the images to compare
 * @returns Comparison result with similarity score
 *
 * @example
 * const result = await compareFacesWithUrl(client, {
 *   urlA: 'https://example.com/reference.jpg',
 *   urlB: 'https://example.com/captured.jpg'
 * });
 */
export async function compareFacesWithUrl(
  client: FaceCompareClient,
  params: { urlA: string; urlB: string; qualityControl?: 0 | 1 | 2 | 3 | 4 }
): Promise<CompareFaceResult> {
  return compareFaces(client, {
    urlA: params.urlA,
    urlB: params.urlB,
    qualityControl: params.qualityControl,
  });
}

/**
 * Face comparison error types for better error handling
 */
export const FaceCompareErrorCodes = {
  /** Authentication failed */
  AUTH_FAILURE: 'AuthFailure.InvalidAuthorization',
  /** Face quality not qualified */
  QUALITY_NOT_QUALIFIED: 'FailedOperation.FaceQualityNotQualified',
  /** Face size too small */
  FACE_TOO_SMALL: 'FailedOperation.FaceSizeTooSmall',
  /** Image decode failed */
  IMAGE_DECODE_FAILED: 'FailedOperation.ImageDecodeFailed',
  /** Image download error */
  IMAGE_DOWNLOAD_ERROR: 'FailedOperation.ImageDownloadError',
  /** Face detection failed */
  FACE_DETECT_FAILED: 'FailedOperation.ImageFacedetectFailed',
  /** Image resolution exceeds limit */
  RESOLUTION_EXCEED: 'FailedOperation.ImageResolutionExceed',
  /** Image resolution too small */
  RESOLUTION_TOO_SMALL: 'FailedOperation.ImageResolutionTooSmall',
  /** Image size exceeds 5MB */
  SIZE_EXCEED: 'FailedOperation.ImageSizeExceed',
  /** No face in photo */
  NO_FACE: 'InvalidParameterValue.NoFaceInPhoto',
  /** URL format illegal */
  URL_ILLEGAL: 'InvalidParameterValue.UrlIllegal',
  /** Account in arrears */
  IN_ARREARS: 'ResourceUnavailable.InArrears',
  /** Account frozen */
  FROZEN: 'ResourceUnavailable.Freeze',
} as const;

/**
 * Check if an error is a specific face compare error
 *
 * @param error - The caught error
 * @param errorCode - The error code to check
 * @returns True if the error matches the specified code
 */
export function isFaceCompareError(error: unknown, errorCode: string): boolean {
  if (error && typeof error === 'object' && 'code' in error) {
    return (error as { code: string }).code === errorCode;
  }
  return false;
}
