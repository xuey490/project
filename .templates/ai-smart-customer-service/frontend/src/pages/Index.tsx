import { ChatContainer } from '@/components/chat/ChatContainer'
import { Bot, Shield, Zap, Clock } from 'lucide-react'

const Index = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-cyan-50/20">
      {/* Decorative background elements */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl" />
      </div>

      <div className="relative flex flex-col lg:flex-row min-h-screen">
        {/* Left panel - Branding */}
        <div className="hidden lg:flex lg:w-[400px] xl:w-[480px] flex-col justify-between p-8 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
          {/* Logo & Title */}
          <div>
            <div className="flex items-center gap-3 mb-8">
              <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <Bot className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-xl font-bold text-white">AI智能客服</h1>
                <p className="text-sm text-slate-400">Smart Customer Service</p>
              </div>
            </div>

            <h2 className="text-3xl font-bold text-white mb-4 leading-tight">
              智能对话，<br />
              <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">
                即刻响应
              </span>
            </h2>
            <p className="text-slate-400 leading-relaxed">
              基于先进的AI技术，为您提供7×24小时全天候智能服务。无论是产品咨询、使用指导还是问题解答，我们都能快速为您提供专业帮助。
            </p>
          </div>

          {/* Features */}
          <div className="space-y-4">
            {[
              { icon: Zap, title: '即时响应', desc: '毫秒级响应，无需等待' },
              { icon: Shield, title: '安全可靠', desc: '对话内容安全加密' },
              { icon: Clock, title: '全天候服务', desc: '7×24小时随时在线' }
            ].map((feature, index) => (
              <div key={index} className="flex items-start gap-4 p-4 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10">
                <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center flex-shrink-0">
                  <feature.icon className="w-5 h-5 text-cyan-400" />
                </div>
                <div>
                  <h3 className="font-semibold text-white mb-0.5">{feature.title}</h3>
                  <p className="text-sm text-slate-400">{feature.desc}</p>
                </div>
              </div>
            ))}
          </div>

          {/* Footer */}
          <p className="text-xs text-slate-500">
            © 2025 AI智能客服 · 由AI技术驱动
          </p>
        </div>

        {/* Right panel - Chat */}
        <div className="flex-1 flex flex-col">
          {/* Mobile header */}
          <div className="lg:hidden flex items-center gap-3 p-4 bg-white/80 backdrop-blur-sm border-b border-border/50">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
              <Bot className="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 className="font-bold">AI智能客服</h1>
              <p className="text-xs text-muted-foreground">随时为您服务</p>
            </div>
          </div>

          {/* Chat container */}
          <div className="flex-1 bg-white/60 backdrop-blur-sm">
            <ChatContainer />
          </div>
        </div>
      </div>
    </div>
  )
}

export default Index
