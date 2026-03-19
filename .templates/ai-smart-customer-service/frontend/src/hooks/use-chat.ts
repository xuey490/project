import { useState, useCallback, useRef } from 'react'
import { ChatMessage, ChatResponse } from '@/types/chat'
import { apiClient } from '@/lib/api-client'

export function useChat() {
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const [isLoading, setIsLoading] = useState(false)
  const abortControllerRef = useRef<AbortController | null>(null)

  const generateId = () => `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`

  const sendMessage = useCallback(async (content: string) => {
    if (!content.trim() || isLoading) return

    // Add user message
    const userMessage: ChatMessage = {
      id: generateId(),
      role: 'user',
      content: content.trim(),
      timestamp: new Date()
    }

    // Add assistant placeholder
    const assistantMessage: ChatMessage = {
      id: generateId(),
      role: 'assistant',
      content: '',
      timestamp: new Date(),
      isStreaming: true
    }

    setMessages(prev => [...prev, userMessage, assistantMessage])
    setIsLoading(true)

    try {
      // Prepare history for API
      const history = messages.map(msg => ({
        role: msg.role,
        content: msg.content
      }))

      // Create abort controller for potential cancellation
      abortControllerRef.current = new AbortController()

      // Use streaming endpoint
      const response = await fetch('/api/chat/stream', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          message: content.trim(),
          history
        }),
        signal: abortControllerRef.current.signal
      })

      if (!response.ok) {
        throw new Error('请求失败')
      }

      const reader = response.body?.getReader()
      const decoder = new TextDecoder()
      let fullContent = ''

      if (reader) {
        while (true) {
          const { done, value } = await reader.read()
          if (done) break

          const chunk = decoder.decode(value, { stream: true })
          const lines = chunk.split('\n')

          for (const line of lines) {
            if (line.startsWith('data: ')) {
              try {
                const data = JSON.parse(line.slice(6))
                if (data.content) {
                  fullContent += data.content
                  setMessages(prev => 
                    prev.map(msg => 
                      msg.id === assistantMessage.id 
                        ? { ...msg, content: fullContent }
                        : msg
                    )
                  )
                }
                if (data.done || data.error) {
                  break
                }
              } catch (e) {
                // Skip invalid JSON
              }
            }
          }
        }
      }

      // Mark streaming as complete
      setMessages(prev => 
        prev.map(msg => 
          msg.id === assistantMessage.id 
            ? { ...msg, isStreaming: false }
            : msg
        )
      )
    } catch (error: any) {
      if (error.name === 'AbortError') {
        // User cancelled the request
        setMessages(prev => 
          prev.map(msg => 
            msg.id === assistantMessage.id 
              ? { ...msg, content: '请求已取消', isStreaming: false }
              : msg
          )
        )
      } else {
        // Error occurred
        setMessages(prev => 
          prev.map(msg => 
            msg.id === assistantMessage.id 
              ? { ...msg, content: '抱歉，服务暂时不可用，请稍后再试。', isStreaming: false }
              : msg
          )
        )
      }
    } finally {
      setIsLoading(false)
      abortControllerRef.current = null
    }
  }, [messages, isLoading])

  const clearMessages = useCallback(() => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort()
    }
    setMessages([])
    setIsLoading(false)
  }, [])

  const stopGeneration = useCallback(() => {
    if (abortControllerRef.current) {
      abortControllerRef.current.abort()
    }
  }, [])

  return {
    messages,
    isLoading,
    sendMessage,
    clearMessages,
    stopGeneration
  }
}
