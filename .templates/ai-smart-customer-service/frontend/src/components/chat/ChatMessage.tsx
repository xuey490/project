import { cn } from '@/lib/utils'
import { ChatMessage as ChatMessageType } from '@/types/chat'
import { Bot, User } from 'lucide-react'
import { FadeIn, fadeUp, fadeLeft } from '@/components/MotionPrimitives'

interface ChatMessageProps {
  message: ChatMessageType
}

export function ChatMessage({ message }: ChatMessageProps) {
  const isUser = message.role === 'user'

  return (
    <FadeIn variants={isUser ? fadeUp : fadeLeft} once={false}>
      <div
        className={cn(
          'flex gap-3 p-4 rounded-xl transition-all',
          isUser 
            ? 'bg-primary/5 ml-8' 
            : 'bg-card border border-border/50 mr-8'
        )}
      >
      {/* Avatar */}
      <div
        className={cn(
          'flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center',
          isUser 
            ? 'bg-primary text-primary-foreground' 
            : 'bg-gradient-to-br from-blue-500 to-cyan-500 text-white'
        )}
      >
        {isUser ? (
          <User className="w-5 h-5" />
        ) : (
          <Bot className="w-5 h-5" />
        )}
      </div>

      {/* Message content */}
      <div className="flex-1 min-w-0">
        <div className="flex items-center gap-2 mb-1">
          <span className="font-medium text-sm">
            {isUser ? '您' : 'AI客服'}
          </span>
          <span className="text-xs text-muted-foreground">
            {message.timestamp.toLocaleTimeString('zh-CN', {
              hour: '2-digit',
              minute: '2-digit'
            })}
          </span>
        </div>
        <div className="text-sm leading-relaxed whitespace-pre-wrap break-words">
          {message.content || (
            <span className="flex items-center gap-2 text-muted-foreground">
              <span className="animate-pulse">正在思考中</span>
              <span className="flex gap-1">
                <span className="w-1.5 h-1.5 bg-current rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                <span className="w-1.5 h-1.5 bg-current rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                <span className="w-1.5 h-1.5 bg-current rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
              </span>
            </span>
          )}
        </div>
        {message.isStreaming && message.content && (
          <span className="inline-block w-2 h-4 bg-primary/50 animate-pulse ml-0.5 rounded-sm" />
        )}
      </div>
      </div>
    </FadeIn>
  )
}
