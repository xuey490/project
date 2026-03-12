import { useEffect, useRef } from 'react'
import { useChat } from '@/hooks/use-chat'
import { ChatMessage } from './ChatMessage'
import { ChatInput } from './ChatInput'
import { Button } from '@/components/ui/button'
import { Bot, MessageSquare, Sparkles, Trash2 } from 'lucide-react'
import { FadeIn, Stagger, HoverLift, fadeUp, scaleUp } from '@/components/MotionPrimitives'

export function ChatContainer() {
  const { messages, isLoading, sendMessage, clearMessages, stopGeneration } = useChat()
  const scrollRef = useRef<HTMLDivElement>(null)

  // Auto scroll to bottom when new messages arrive
  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTo({
        top: scrollRef.current.scrollHeight,
        behavior: 'smooth'
      })
    }
  }, [messages])

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="flex items-center justify-between px-6 py-4 border-b border-border/50 bg-card/30 backdrop-blur-sm">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <Bot className="w-5 h-5 text-white" />
          </div>
          <div>
            <h2 className="font-semibold">AI智能客服</h2>
            <p className="text-xs text-muted-foreground">随时为您提供帮助</p>
          </div>
        </div>
        
        {messages.length > 0 && (
          <Button
            variant="ghost"
            size="sm"
            onClick={clearMessages}
            className="text-muted-foreground hover:text-destructive"
          >
            <Trash2 className="w-4 h-4 mr-1.5" />
            清空对话
          </Button>
        )}
      </div>

      {/* Messages area */}
      <div ref={scrollRef} className="flex-1 overflow-y-auto p-4">
        {messages.length === 0 ? (
          <div className="flex flex-col items-center justify-center h-full min-h-[400px] text-center px-4">
            <FadeIn variants={scaleUp}>
              <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mb-6 shadow-xl shadow-blue-500/25">
                <Sparkles className="w-10 h-10 text-white" />
              </div>
            </FadeIn>
            <FadeIn>
              <h3 className="text-xl font-semibold mb-2">您好！有什么可以帮您的？</h3>
              <p className="text-muted-foreground mb-8 max-w-md">
                我是AI智能客服，可以回答您的问题、提供建议和帮助。请随时向我提问！
              </p>
            </FadeIn>
            
            {/* Quick actions */}
            <Stagger stagger={0.12} className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-md">
              {[
                { icon: MessageSquare, text: '产品咨询', prompt: '请介绍一下你们的产品' },
                { icon: Sparkles, text: '使用帮助', prompt: '如何使用这个服务？' }
              ].map((item, index) => (
                <HoverLift key={index} variants={fadeUp}>
                  <button
                    onClick={() => sendMessage(item.prompt)}
                    className="flex items-center gap-3 p-4 rounded-xl border border-border/50 bg-card/50 hover:bg-card hover:border-primary/30 transition-all text-left group cursor-pointer w-full"
                  >
                    <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                      <item.icon className="w-5 h-5 text-primary" />
                    </div>
                    <span className="font-medium">{item.text}</span>
                  </button>
                </HoverLift>
              ))}
            </Stagger>
          </div>
        ) : (
          <div className="space-y-4 max-w-3xl mx-auto">
            {messages.map((message) => (
              <ChatMessage key={message.id} message={message} />
            ))}
          </div>
        )}
      </div>

      {/* Input area */}
      <div className="p-4 border-t border-border/50 bg-background/80 backdrop-blur-sm">
        <div className="max-w-3xl mx-auto">
          <ChatInput
            onSend={sendMessage}
            isLoading={isLoading}
            onStop={stopGeneration}
          />
        </div>
      </div>
    </div>
  )
}
