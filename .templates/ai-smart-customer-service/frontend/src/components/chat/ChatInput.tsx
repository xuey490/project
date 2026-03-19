import { useState, useRef, useEffect } from 'react'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Send, Square } from 'lucide-react'
import { cn } from '@/lib/utils'

interface ChatInputProps {
  onSend: (message: string) => void
  isLoading: boolean
  onStop?: () => void
  disabled?: boolean
}

export function ChatInput({ onSend, isLoading, onStop, disabled }: ChatInputProps) {
  const [input, setInput] = useState('')
  const textareaRef = useRef<HTMLTextAreaElement>(null)

  const handleSubmit = () => {
    if (input.trim() && !isLoading && !disabled) {
      onSend(input.trim())
      setInput('')
    }
  }

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      handleSubmit()
    }
  }

  // Auto-resize textarea
  useEffect(() => {
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto'
      textareaRef.current.style.height = `${Math.min(textareaRef.current.scrollHeight, 150)}px`
    }
  }, [input])

  return (
    <div className="relative">
      <div className="flex items-end gap-2 p-4 bg-card/50 backdrop-blur-sm border border-border/50 rounded-2xl shadow-lg">
        <Textarea
          ref={textareaRef}
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={handleKeyDown}
          placeholder="输入您的问题..."
          disabled={disabled}
          className={cn(
            'flex-1 min-h-[44px] max-h-[150px] resize-none border-0 bg-transparent',
            'focus-visible:ring-0 focus-visible:ring-offset-0',
            'placeholder:text-muted-foreground/60 text-sm'
          )}
          rows={1}
        />
        
        {isLoading ? (
          <Button
            onClick={onStop}
            variant="destructive"
            size="icon"
            className="flex-shrink-0 h-10 w-10 rounded-xl"
          >
            <Square className="w-4 h-4 fill-current" />
          </Button>
        ) : (
          <Button
            onClick={handleSubmit}
            disabled={!input.trim() || disabled}
            size="icon"
            className={cn(
              'flex-shrink-0 h-10 w-10 rounded-xl transition-all',
              'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700',
              'disabled:opacity-50 disabled:cursor-not-allowed'
            )}
          >
            <Send className="w-4 h-4" />
          </Button>
        )}
      </div>
      
      <p className="text-xs text-muted-foreground text-center mt-2">
        按 Enter 发送，Shift + Enter 换行
      </p>
    </div>
  )
}
