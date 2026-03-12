import { useState } from 'react';
import { Layout } from '@/components/layout';
import { Button } from '@/components/ui/button';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Download, Trash2, Edit3, ImageIcon, Loader2, LogIn } from 'lucide-react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'sonner';
import { useAuthContext } from '@/contexts/AuthContext';
import { useImages, useDeleteImage, SavedImage } from '@/hooks/use-images';

const Gallery = () => {
  const navigate = useNavigate();
  const { user, loading: authLoading } = useAuthContext();
  const { data, isLoading, error } = useImages();
  const deleteImageMutation = useDeleteImage();
  
  const [selectedImage, setSelectedImage] = useState<SavedImage | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<SavedImage | null>(null);
  const [hoveredId, setHoveredId] = useState<string | null>(null);

  const handleDownload = async (image: SavedImage, e?: React.MouseEvent) => {
    e?.stopPropagation();
    try {
      // 如果是 base64 URL
      if (image.url.startsWith('data:')) {
        const a = document.createElement('a');
        a.href = image.url;
        a.download = `image-${image.id}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      } else {
        const response = await fetch(image.url);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `image-${image.id}.png`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      }
    } catch (error) {
      console.error('Download failed:', error);
      toast.error('下载失败');
    }
  };

  const handleDelete = (image: SavedImage, e?: React.MouseEvent) => {
    e?.stopPropagation();
    setDeleteTarget(image);
  };

  const confirmDelete = async () => {
    if (!deleteTarget) return;
    
    try {
      await deleteImageMutation.mutateAsync(deleteTarget.id);
      toast.success('已删除');
    } catch {
      toast.error('删除失败');
    }
    setDeleteTarget(null);
  };

  const handleEditPrompt = (image: SavedImage, e?: React.MouseEvent) => {
    e?.stopPropagation();
    navigate('/', { state: { prompt: image.prompt } });
  };

  // 加载中状态
  if (authLoading) {
    return (
      <Layout>
        <div className="min-h-[calc(100vh-110px)] flex items-center justify-center">
          <Loader2 className="w-6 h-6 animate-spin text-primary" />
        </div>
      </Layout>
    );
  }

  // 未登录状态
  if (!user) {
    return (
      <Layout>
        <div className="min-h-[calc(100vh-110px)] flex flex-col items-center justify-center py-12 px-4">
          <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <LogIn className="w-8 h-8 text-muted-foreground" strokeWidth={1.5} />
          </div>
          <h2 className="text-lg font-medium text-foreground mb-2">请先登录</h2>
          <p className="text-sm text-muted-foreground mb-6 text-center">
            登录后即可保存和管理你的作品
          </p>
          <Link to="/login">
            <Button className="gap-2">
              <LogIn className="w-4 h-4" strokeWidth={1.5} />
              去登录
            </Button>
          </Link>
        </div>
      </Layout>
    );
  }

  // 加载图片列表
  if (isLoading) {
    return (
      <Layout>
        <div className="min-h-[calc(100vh-110px)] flex items-center justify-center">
          <Loader2 className="w-6 h-6 animate-spin text-primary" />
        </div>
      </Layout>
    );
  }

  // 加载错误
  if (error) {
    return (
      <Layout>
        <div className="min-h-[calc(100vh-110px)] flex flex-col items-center justify-center py-12 px-4">
          <p className="text-sm text-destructive mb-4">加载失败，请重试</p>
          <Button onClick={() => window.location.reload()}>刷新页面</Button>
        </div>
      </Layout>
    );
  }

  const images = data?.images || [];

  // 空状态
  if (images.length === 0) {
    return (
      <Layout>
        <div className="min-h-[calc(100vh-110px)] flex flex-col items-center justify-center py-12 px-4">
          <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <ImageIcon className="w-8 h-8 text-muted-foreground" strokeWidth={1.5} />
          </div>
          <h2 className="text-lg font-medium text-foreground mb-2">暂无作品</h2>
          <p className="text-sm text-muted-foreground mb-6">生成图片后点击"保存至作品"即可收藏</p>
          <Button onClick={() => navigate('/')} className="gap-2">
            开始创作
          </Button>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="min-h-[calc(100vh-110px)] py-8 sm:py-12">
        <div className="mx-auto max-w-[1000px] px-4 sm:px-6">
          {/* Header */}
          <div className="mb-8">
            <h1 className="text-xl sm:text-2xl font-semibold text-foreground tracking-tight">
              我的作品
            </h1>
            <p className="text-sm text-muted-foreground mt-1">
              共 {data?.pagination.total || 0} 张图片
            </p>
          </div>

          {/* Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {images.map((image) => (
              <div
                key={image.id}
                className="group relative rounded-xl overflow-hidden bg-card border border-border shadow-[0_2px_8px_rgba(0,0,0,0.04)] cursor-pointer transition-all hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)]"
                onMouseEnter={() => setHoveredId(image.id)}
                onMouseLeave={() => setHoveredId(null)}
                onClick={() => setSelectedImage(image)}
              >
                <div className="aspect-square">
                  <img
                    src={image.url}
                    alt={image.prompt}
                    className="w-full h-full object-cover"
                  />
                </div>
                
                {/* Hover Overlay */}
                <div
                  className={`absolute inset-0 bg-black/50 flex items-end justify-center pb-4 transition-opacity duration-200 ${
                    hoveredId === image.id ? 'opacity-100' : 'opacity-0'
                  }`}
                >
                  <div className="flex gap-2">
                    <Button
                      variant="secondary"
                      size="icon-sm"
                      className="bg-white/90 hover:bg-white text-foreground"
                      onClick={(e) => handleDownload(image, e)}
                    >
                      <Download className="w-4 h-4" strokeWidth={1.5} />
                    </Button>
                    <Button
                      variant="secondary"
                      size="icon-sm"
                      className="bg-white/90 hover:bg-white text-foreground"
                      onClick={(e) => handleEditPrompt(image, e)}
                    >
                      <Edit3 className="w-4 h-4" strokeWidth={1.5} />
                    </Button>
                    <Button
                      variant="secondary"
                      size="icon-sm"
                      className="bg-white/90 hover:bg-white text-destructive hover:text-destructive"
                      onClick={(e) => handleDelete(image, e)}
                    >
                      <Trash2 className="w-4 h-4" strokeWidth={1.5} />
                    </Button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Image Preview Dialog */}
      {selectedImage && (
        <div
          className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 animate-in fade-in duration-200"
          onClick={() => setSelectedImage(null)}
        >
          <div
            className="max-w-4xl max-h-[90vh] rounded-xl overflow-hidden bg-card"
            onClick={(e) => e.stopPropagation()}
          >
            <img
              src={selectedImage.url}
              alt={selectedImage.prompt}
              className="max-w-full max-h-[70vh] object-contain"
            />
            <div className="p-4 border-t border-border">
              <p className="text-sm text-muted-foreground line-clamp-2">
                {selectedImage.prompt}
              </p>
              <div className="flex gap-2 mt-3">
                <Button
                  variant="outline"
                  size="sm"
                  className="gap-1.5 text-xs"
                  onClick={() => handleDownload(selectedImage)}
                >
                  <Download className="w-3.5 h-3.5" strokeWidth={1.5} />
                  下载
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  className="gap-1.5 text-xs"
                  onClick={() => {
                    handleEditPrompt(selectedImage);
                    setSelectedImage(null);
                  }}
                >
                  <Edit3 className="w-3.5 h-3.5" strokeWidth={1.5} />
                  编辑描述词
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Delete Confirmation */}
      <AlertDialog open={!!deleteTarget} onOpenChange={() => setDeleteTarget(null)}>
        <AlertDialogContent className="max-w-sm">
          <AlertDialogHeader>
            <AlertDialogTitle className="text-base">确认删除</AlertDialogTitle>
            <AlertDialogDescription className="text-sm">
              确定要删除这张图片吗？此操作无法撤销。
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel className="text-sm">取消</AlertDialogCancel>
            <AlertDialogAction
              onClick={confirmDelete}
              className="text-sm bg-destructive hover:bg-destructive/90"
              disabled={deleteImageMutation.isPending}
            >
              {deleteImageMutation.isPending ? (
                <Loader2 className="w-4 h-4 animate-spin" />
              ) : (
                '确认'
              )}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Layout>
  );
};

export default Gallery;
