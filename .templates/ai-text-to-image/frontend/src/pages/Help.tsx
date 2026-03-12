import { Layout } from '@/components/layout';
import { Lightbulb, AlertCircle, Zap, HelpCircle } from 'lucide-react';

const helpItems = [
  {
    icon: Lightbulb,
    title: '描述词怎么写？',
    content: `好的描述词应该包含以下要素：
    
1. 主体内容：明确描述你想要的主要对象（如：一只白色猫咪）
2. 风格定义：指定艺术风格（如：扁平化插画、水彩风、写实摄影）
3. 场景环境：描述背景或环境（如：坐在阳光下、站在花田中）
4. 细节补充：添加色彩、光线、氛围等修饰（如：柔和的色彩、温暖的氛围）

示例：一只简约风格的白色猫咪，坐在阳光下，扁平化插画，柔和的色彩，干净的背景`,
  },
  {
    icon: AlertCircle,
    title: '生成失败原因',
    content: `图片生成可能失败的常见原因：

1. 描述词过于模糊或抽象
2. 包含敏感或违规内容
3. 网络连接不稳定
4. 服务器繁忙，请稍后重试

建议：尝试简化描述词，或更换更具体的表达方式`,
  },
  {
    icon: Zap,
    title: '如何提高生成质量？',
    content: `提升生成效果的技巧：

1. 使用清晰具体的描述，避免模棱两可
2. 在高级设置中选择合适的风格倾向
3. 适当提高清晰度设置
4. 参考示例描述词的写法
5. 多次尝试，微调描述词`,
  },
  {
    icon: HelpCircle,
    title: '图片保存在哪里？',
    content: `生成的图片有两种保存方式：

1. 下载：点击"下载"按钮，图片将保存到您的本地设备
2. 收藏：点击"保存至作品"，图片将收藏在"我的作品"中（存储在浏览器本地，最多保存50张）

注意：清除浏览器数据会导致"我的作品"中的图片丢失，重要图片请及时下载`,
  },
];

const Help = () => {
  return (
    <Layout>
      <div className="min-h-[calc(100vh-110px)] py-8 sm:py-12">
        <div className="mx-auto max-w-[720px] px-4 sm:px-6">
          {/* Header */}
          <div className="mb-8">
            <h1 className="text-xl sm:text-2xl font-semibold text-foreground tracking-tight">
              帮助中心
            </h1>
            <p className="text-sm text-muted-foreground mt-1">
              常见问题解答
            </p>
          </div>

          {/* Help Items */}
          <div className="space-y-4">
            {helpItems.map((item, index) => (
              <div
                key={index}
                className="p-5 rounded-xl bg-card border border-border shadow-[0_2px_8px_rgba(0,0,0,0.04)]"
              >
                <div className="flex items-start gap-3">
                  <div className="w-8 h-8 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <item.icon className="w-4 h-4 text-primary" strokeWidth={1.5} />
                  </div>
                  <div className="flex-1 min-w-0">
                    <h2 className="text-sm font-medium text-foreground mb-3">
                      {item.title}
                    </h2>
                    <div className="text-xs text-muted-foreground leading-relaxed whitespace-pre-line">
                      {item.content}
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Help;
