import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiClient } from '@/lib/api-client';
import { useAuthContext } from '@/contexts/AuthContext';

export interface SavedImage {
  id: string;
  url: string;
  prompt: string;
  aspectRatio: string;
  quality: string;
  style: string;
  createdAt: string;
}

interface ImagesResponse {
  images: SavedImage[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

interface SaveImageInput {
  url: string;
  prompt: string;
  aspectRatio?: string;
  quality?: string;
  style?: string;
}

/**
 * 获取用户图片列表
 */
export function useImages(page = 1, limit = 20) {
  const { user } = useAuthContext();

  return useQuery<ImagesResponse>({
    queryKey: ['images', page, limit],
    queryFn: async () => {
      const response = await apiClient.get('/images', {
        params: { page, limit },
      });
      return response.data;
    },
    enabled: !!user, // 只有登录后才请求
  });
}

/**
 * 保存图片
 */
export function useSaveImage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (input: SaveImageInput) => {
      const response = await apiClient.post('/images', input);
      return response.data as SavedImage;
    },
    onSuccess: () => {
      // 刷新图片列表
      queryClient.invalidateQueries({ queryKey: ['images'] });
    },
  });
}

/**
 * 删除图片
 */
export function useDeleteImage() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (imageId: string) => {
      await apiClient.delete(`/images/${imageId}`);
    },
    onSuccess: () => {
      // 刷新图片列表
      queryClient.invalidateQueries({ queryKey: ['images'] });
    },
  });
}
