import { Button } from '@/components/ui/button';
import { Download, RefreshCw, Bookmark, Loader2 } from 'lucide-react';
import { FadeIn, scaleUp } from '@/components/MotionPrimitives';

interface GeneratedImage {
  id: string;
  url: string;
  prompt: string;
  createdAt: Date;
}

interface ImageResultProps {
  image: GeneratedImage | null;
  isLoading: boolean;
  onRegenerate: () => void;
  onSave: () => void;
  isSaving?: boolean;
}

export function ImageResult({ image, isLoading, onRegenerate, onSave, isSaving }: ImageResultProps) {
  const handleDownload = async () => {
    if (!image) return;
    
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
    }
  };

  if (isLoading) {
    return (
      <div className="mt-6 p-8 rounded-xl bg-card border border-border shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div className="flex flex-col items-center justify-center py-12">
          <Loader2 className="w-8 h-8 text-primary animate-spin mb-4" strokeWidth={1.5} />
          <p className="text-sm text-muted-foreground">正在生成图片...</p>
        </div>
      </div>
    );
  }

  if (!image) {
    return null;
  }

  return (
    <FadeIn variants={scaleUp} className="mt-6 p-5 rounded-xl bg-card border border-border shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
      {/* Image */}
      <div className="flex justify-center">
        <div className="relative rounded-lg overflow-hidden bg-muted">
          <img
            src={image.url}
            alt={image.prompt}
            className="max-w-full max-h-[500px] object-contain"
          />
        </div>
      </div>

      {/* Actions */}
      <div className="flex items-center justify-center gap-3 mt-5">
        <Button
          variant="outline"
          size="sm"
          className="gap-1.5 text-xs"
          onClick={handleDownload}
        >
          <Download className="w-3.5 h-3.5" strokeWidth={1.5} />
          下载
        </Button>
        
        <Button
          variant="default"
          size="sm"
          className="gap-1.5 text-xs"
          onClick={onRegenerate}
        >
          <RefreshCw className="w-3.5 h-3.5" strokeWidth={1.5} />
          重新生成
        </Button>
        
        <Button
          variant="outline"
          size="sm"
          className="gap-1.5 text-xs"
          onClick={onSave}
          disabled={isSaving}
        >
          {isSaving ? (
            <Loader2 className="w-3.5 h-3.5 animate-spin" strokeWidth={1.5} />
          ) : (
            <Bookmark className="w-3.5 h-3.5" strokeWidth={1.5} />
          )}
          {isSaving ? '保存中...' : '保存至作品'}
        </Button>
      </div>
    </FadeIn>
  );
}
