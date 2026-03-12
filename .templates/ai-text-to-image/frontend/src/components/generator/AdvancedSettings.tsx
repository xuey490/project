import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Slider } from '@/components/ui/slider';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { ChevronDown, HelpCircle } from 'lucide-react';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';

export interface GeneratorSettings {
  aspectRatio: '1:1' | '4:3' | '16:9';
  quality: 'low' | 'medium' | 'high';
  style: string;
}

interface AdvancedSettingsProps {
  settings: GeneratorSettings;
  onChange: (settings: GeneratorSettings) => void;
  disabled?: boolean;
}

const aspectRatioOptions = [
  { value: '1:1', label: '1:1', desc: '正方形' },
  { value: '4:3', label: '4:3', desc: '标准' },
  { value: '16:9', label: '16:9', desc: '宽屏' },
];

const styleOptions = [
  { value: 'auto', label: '自动' },
  { value: 'realistic', label: '写实' },
  { value: 'illustration', label: '插画' },
  { value: 'flat', label: '扁平' },
  { value: 'anime', label: '二次元' },
  { value: 'watercolor', label: '水彩' },
];

const qualityLabels = {
  low: '低',
  medium: '中',
  high: '高',
};

export function AdvancedSettings({ settings, onChange, disabled }: AdvancedSettingsProps) {
  const [isOpen, setIsOpen] = useState(true);

  const handleChange = <K extends keyof GeneratorSettings>(
    key: K,
    value: GeneratorSettings[K]
  ) => {
    onChange({ ...settings, [key]: value });
  };

  const qualityValue = settings.quality === 'low' ? 0 : settings.quality === 'medium' ? 50 : 100;

  return (
    <Collapsible open={isOpen} onOpenChange={setIsOpen}>
      <CollapsibleTrigger asChild>
        <Button
          variant="ghost"
          size="sm"
          className="text-xs text-muted-foreground hover:text-foreground gap-1 px-2 -ml-2"
          disabled={disabled}
        >
          <ChevronDown
            className={`w-3.5 h-3.5 transition-transform duration-200 ${
              isOpen ? 'rotate-180' : ''
            }`}
            strokeWidth={1.5}
          />
          高级设置
        </Button>
      </CollapsibleTrigger>

      <CollapsibleContent className="pt-4 space-y-5">
        {/* 图片尺寸 */}
        <div className="space-y-2.5">
          <div className="flex items-center gap-1.5">
            <Label className="text-xs text-muted-foreground font-normal">图片尺寸</Label>
            <Tooltip>
              <TooltipTrigger asChild>
                <HelpCircle className="w-3 h-3 text-muted-foreground/50 cursor-help" />
              </TooltipTrigger>
              <TooltipContent side="top" className="text-xs max-w-[200px]">
                选择生成图片的宽高比例
              </TooltipContent>
            </Tooltip>
          </div>
          <RadioGroup
            value={settings.aspectRatio}
            onValueChange={(value) => handleChange('aspectRatio', value as GeneratorSettings['aspectRatio'])}
            className="flex gap-2"
            disabled={disabled}
          >
            {aspectRatioOptions.map((option) => (
              <Label
                key={option.value}
                className={`flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all ${
                  settings.aspectRatio === option.value
                    ? 'border-primary bg-accent text-foreground'
                    : 'border-border hover:border-primary/50 text-muted-foreground hover:text-foreground'
                } ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
              >
                <RadioGroupItem value={option.value} className="sr-only" />
                <span className="text-xs font-medium">{option.label}</span>
                <span className="text-[10px] text-muted-foreground">{option.desc}</span>
              </Label>
            ))}
          </RadioGroup>
        </div>

        {/* 清晰度 */}
        <div className="space-y-2.5">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-1.5">
              <Label className="text-xs text-muted-foreground font-normal">清晰度</Label>
              <Tooltip>
                <TooltipTrigger asChild>
                  <HelpCircle className="w-3 h-3 text-muted-foreground/50 cursor-help" />
                </TooltipTrigger>
                <TooltipContent side="top" className="text-xs max-w-[200px]">
                  更高清晰度生成时间更长
                </TooltipContent>
              </Tooltip>
            </div>
            <span className="text-xs text-foreground font-medium">
              {qualityLabels[settings.quality]}
            </span>
          </div>
          <Slider
            value={[qualityValue]}
            onValueChange={(value) => {
              const quality = value[0] <= 25 ? 'low' : value[0] <= 75 ? 'medium' : 'high';
              handleChange('quality', quality);
            }}
            max={100}
            step={50}
            disabled={disabled}
            className="w-full"
          />
        </div>

        {/* 风格倾向 */}
        <div className="space-y-2.5">
          <div className="flex items-center gap-1.5">
            <Label className="text-xs text-muted-foreground font-normal">风格倾向</Label>
            <Tooltip>
              <TooltipTrigger asChild>
                <HelpCircle className="w-3 h-3 text-muted-foreground/50 cursor-help" />
              </TooltipTrigger>
              <TooltipContent side="top" className="text-xs max-w-[200px]">
                选择生成图片的艺术风格
              </TooltipContent>
            </Tooltip>
          </div>
          <Select
            value={settings.style}
            onValueChange={(value) => handleChange('style', value)}
            disabled={disabled}
          >
            <SelectTrigger className="w-full h-9 text-xs">
              <SelectValue placeholder="选择风格" />
            </SelectTrigger>
            <SelectContent>
              {styleOptions.map((option) => (
                <SelectItem key={option.value} value={option.value} className="text-xs">
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </CollapsibleContent>
    </Collapsible>
  );
}
