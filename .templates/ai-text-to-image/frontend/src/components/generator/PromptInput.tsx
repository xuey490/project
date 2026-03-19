import { useState, useRef } from 'react';
import { Button } from '@/components/ui/button';
import { Lightbulb, X } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Stagger, FadeIn, fadeUp } from '@/components/MotionPrimitives';

interface PromptInputProps {
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
}

const examplePrompts = [
  {
    title: '极简插画',
    prompt: '一只简约风格的白色猫咪，坐在阳光下，扁平化插画，柔和的色彩，干净的背景',
  },
  {
    title: '科技感',
    prompt: '未来城市夜景，霓虹灯光，赛博朋克风格，高楼大厦，飞行汽车',
  },
  {
    title: '水彩风',
    prompt: '春天的樱花树，水彩画风格，粉色花瓣飘落，蓝天白云，柔和光线',
  },
  {
    title: '写实风景',
    prompt: '阿尔卑斯山日出，高清摄影，金色阳光，雪山倒映在湖面，宁静氛围',
  },
  {
    title: '二次元',
    prompt: '动漫风格少女，蓝色长发，穿着白色连衣裙，站在花田中，微风吹拂',
  },
];

export function PromptInput({ value, onChange, disabled }: PromptInputProps) {
  const [isExamplesOpen, setIsExamplesOpen] = useState(false);
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  const handleClear = () => {
    onChange('');
    textareaRef.current?.focus();
  };

  const handleSelectExample = (prompt: string) => {
    onChange(prompt);
    setIsExamplesOpen(false);
    textareaRef.current?.focus();
  };

  return (
    <div className="space-y-3">
      <div className="relative">
        <textarea
          ref={textareaRef}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
          placeholder="输入描述词生成图片（如：一只简约风格的白色猫咪，坐在阳光下，扁平化插画）"
          className="w-full min-h-[120px] max-h-[300px] resize-y rounded-lg border border-transparent bg-muted/50 px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:bg-background focus:outline-none focus:ring-0 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          style={{ lineHeight: '1.6' }}
        />
      </div>

      <div className="flex items-center gap-3">
        <Dialog open={isExamplesOpen} onOpenChange={setIsExamplesOpen}>
          <DialogTrigger asChild>
            <Button
              variant="ghost"
              size="sm"
              className="text-xs text-muted-foreground hover:text-foreground gap-1.5 px-2"
              disabled={disabled}
            >
              <Lightbulb className="w-3.5 h-3.5" strokeWidth={1.5} />
              示例描述词
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle className="text-base font-medium">示例描述词</DialogTitle>
            </DialogHeader>
            <Stagger stagger={0.06} className="space-y-2 mt-2">
              {examplePrompts.map((example, index) => (
                <FadeIn key={index} variants={fadeUp}>
                  <button
                    onClick={() => handleSelectExample(example.prompt)}
                    className="w-full text-left p-3 rounded-lg border border-border hover:border-primary/50 hover:bg-accent/50 transition-all group"
                  >
                    <div className="text-sm font-medium text-foreground mb-1 group-hover:text-primary transition-colors">
                      {example.title}
                    </div>
                    <div className="text-xs text-muted-foreground line-clamp-2">
                      {example.prompt}
                    </div>
                  </button>
                </FadeIn>
              ))}
            </Stagger>
          </DialogContent>
        </Dialog>

        {value && (
          <Button
            variant="ghost"
            size="sm"
            className="text-xs text-muted-foreground hover:text-foreground gap-1 px-2"
            onClick={handleClear}
            disabled={disabled}
          >
            <X className="w-3.5 h-3.5" strokeWidth={1.5} />
            清空
          </Button>
        )}

        <span className="ml-auto text-xs text-muted-foreground/70">
          {value.length} 字符
        </span>
      </div>
    </div>
  );
}
