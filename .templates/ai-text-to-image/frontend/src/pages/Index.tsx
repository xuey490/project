import { useState, useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Layout } from '@/components/layout';
import { PromptInput, AdvancedSettings, GeneratorSettings } from '@/components/generator';
import { Button } from '@/components/ui/button';
import { Sparkles, Download, RefreshCw, Save, ImageIcon, Loader2 } from 'lucide-react';
import { toast } from 'sonner';
import { useAuthContext } from '@/contexts/AuthContext';
import { useSaveImage } from '@/hooks/use-images';

interface GeneratedImage {
  id: string;
  url: string;
  prompt: string;
  createdAt: Date;
}

interface LocationState {
  prompt?: string;
}

const Index = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { user } = useAuthContext();
  const saveImageMutation = useSaveImage();
  
  const [prompt, setPrompt] = useState('');
  const [settings, setSettings] = useState<GeneratorSettings>({
    aspectRatio: '1:1',
    quality: 'medium',
    style: 'auto',
  });
  const [isGenerating, setIsGenerating] = useState(false);
  const [generatedImage, setGeneratedImage] = useState<GeneratedImage | null>(null);
  const [errorMessage, setErrorMessage] = useState('');

  // 从 Gallery 页面接收 prompt
  useEffect(() => {
    const state = location.state as LocationState;
    if (state?.prompt) {
      setPrompt(state.prompt);
      // 清除 state 以防止刷新时重复设置
      window.history.replaceState({}, document.title);
    }
  }, [location.state]);

  const handleGenerate = async () => {
    if (!prompt.trim()) {
      setErrorMessage('请输入描述词');
      return;
    }
    
    setErrorMessage('');
    setIsGenerating(true);
    setGeneratedImage(null);

    try {
      // 1. 调用后端 API 创建生成任务
      const response = await fetch('/api/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          prompt: prompt.trim(),
          aspectRatio: settings.aspectRatio,
          quality: settings.quality,
          style: settings.style,
        }),
      });

      if (!response.ok) {
        throw new Error('生成失败，请稍后重试');
      }

      const result = await response.json();
      
      if (!result.success || !result.data?.jobId) {
        throw new Error('生成任务创建失败');
      }

      const { jobId } = result.data;

      // 2. 轮询查询任务状态
      const pollResult = async (): Promise<string> => {
        const queryResponse = await fetch('/api/aiart/query', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ jobId }),
        });

        if (!queryResponse.ok) {
          throw new Error('查询任务状态失败');
        }

        const queryResult = await queryResponse.json();
        
        if (!queryResult.success) {
          throw new Error(queryResult.error || '查询失败');
        }

        const { status, imageUrl, errorMessage } = queryResult.data;

        if ((status === 'completed' || status === 'SUCCESS') && imageUrl) {
          return imageUrl;
        }

        if (status === 'failed' || status === 'FAILED') {
          throw new Error(errorMessage || '图片生成失败');
        }

        // 等待 1 秒后继续轮询
        await new Promise(resolve => setTimeout(resolve, 1000));
        return pollResult();
      };

      const imageUrl = await pollResult();
      
      setGeneratedImage({
        id: jobId,
        url: imageUrl,
        prompt: prompt.trim(),
        createdAt: new Date(),
      });
    } catch (error) {
      console.error('Generate error:', error);
      setErrorMessage(error instanceof Error ? error.message : '生成失败，请稍后重试');
    } finally {
      setIsGenerating(false);
    }
  };

  const handleRegenerate = () => {
    handleGenerate();
  };

  const handleSave = async () => {
    if (!generatedImage) return;
    
    // 检查是否登录
    if (!user) {
      toast.error('请先登录后再保存');
      navigate('/login');
      return;
    }

    try {
      await saveImageMutation.mutateAsync({
        url: generatedImage.url,
        prompt: generatedImage.prompt,
        aspectRatio: settings.aspectRatio,
        quality: settings.quality,
        style: settings.style,
      });
      toast.success('已保存至我的作品');
    } catch (error) {
      console.error('Save error:', error);
      toast.error('保存失败，请重试');
    }
  };

  const handleDownload = () => {
    if (!generatedImage) return;
    
    const link = document.createElement('a');
    link.href = generatedImage.url;
    link.download = `generated-${generatedImage.id}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toast.success('图片已开始下载');
  };

  // 根据比例计算预览区域的样式
  const getPreviewAspectClass = () => {
    switch (settings.aspectRatio) {
      case '16:9':
        return 'aspect-video';
      case '4:3':
        return 'aspect-[4/3]';
      default:
        return 'aspect-square';
    }
  };

  return (
    <Layout>
      <div className="min-h-[calc(100vh-110px)] py-6 sm:py-8">
        <div className="mx-auto w-full max-w-6xl px-4 sm:px-6">
          {/* Hero Section */}
          <div className="text-center mb-6 sm:mb-8">
            <h1 className="text-2xl sm:text-3xl font-semibold text-foreground tracking-tight mb-2">
              文生图创作工具
            </h1>
            <p className="text-sm text-muted-foreground">
              输入描述词，AI 为你生成精美图片
            </p>
          </div>

          {/* Main Content - Left/Right Layout */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Left Side - Input Area */}
            <div className="bg-card rounded-xl border border-border p-5 sm:p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] h-fit">
              {/* Prompt Input */}
              <PromptInput
                value={prompt}
                onChange={setPrompt}
                disabled={isGenerating}
              />

              {/* Advanced Settings */}
              <div className="mt-4 pt-4 border-t border-border">
                <AdvancedSettings
                  settings={settings}
                  onChange={setSettings}
                  disabled={isGenerating}
                />
              </div>

              {/* Generate Button */}
              <div className="mt-6">
                <Button
                  onClick={handleGenerate}
                  disabled={isGenerating}
                  className="w-full h-10 gap-2 text-sm font-medium"
                >
                  {isGenerating ? (
                    <>
                      <div className="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
                      生成中...
                    </>
                  ) : (
                    <>
                      <Sparkles className="w-4 h-4" strokeWidth={1.5} />
                      生成图片
                    </>
                  )}
                </Button>
                
                {/* Error Message */}
                {errorMessage && (
                  <p className="mt-2 text-xs text-destructive text-center">
                    {errorMessage}
                  </p>
                )}
              </div>
            </div>

            {/* Right Side - Result Area */}
            <div className="bg-card rounded-xl border border-border p-5 sm:p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] flex flex-col">
              <h2 className="text-sm font-medium text-foreground mb-4">生成结果</h2>
              
              {/* Preview Area */}
              <div className={`relative w-full ${getPreviewAspectClass()} bg-muted/50 rounded-lg overflow-hidden border border-border/50 flex items-center justify-center`}>
                {isGenerating ? (
                  // Loading State
                  <div className="flex flex-col items-center gap-3">
                    <div className="w-10 h-10 border-3 border-primary/30 border-t-primary rounded-full animate-spin" />
                    <p className="text-sm text-muted-foreground">正在生成图片...</p>
                  </div>
                ) : generatedImage ? (
                  // Generated Image
                  <img
                    src={generatedImage.url}
                    alt={generatedImage.prompt}
                    className="w-full h-full object-contain"
                  />
                ) : (
                  // Empty State
                  <div className="flex flex-col items-center gap-3 text-muted-foreground/60">
                    <ImageIcon className="w-12 h-12" strokeWidth={1} />
                    <p className="text-sm">输入描述词并点击生成</p>
                  </div>
                )}
              </div>

              {/* Action Buttons */}
              {generatedImage && !isGenerating && (
                <div className="mt-4 flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={handleRegenerate}
                    className="flex-1 gap-1.5"
                  >
                    <RefreshCw className="w-3.5 h-3.5" />
                    重新生成
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={handleDownload}
                    className="flex-1 gap-1.5"
                  >
                    <Download className="w-3.5 h-3.5" />
                    下载
                  </Button>
                  <Button
                    variant="default"
                    size="sm"
                    onClick={handleSave}
                    disabled={saveImageMutation.isPending}
                    className="flex-1 gap-1.5"
                  >
                    {saveImageMutation.isPending ? (
                      <Loader2 className="w-3.5 h-3.5 animate-spin" />
                    ) : (
                      <Save className="w-3.5 h-3.5" />
                    )}
                    保存
                  </Button>
                </div>
              )}

              {/* Prompt Display */}
              {generatedImage && !isGenerating && (
                <div className="mt-4 pt-4 border-t border-border">
                  <p className="text-xs text-muted-foreground mb-1">描述词</p>
                  <p className="text-sm text-foreground">{generatedImage.prompt}</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Index;
