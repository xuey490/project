import { Router, Request, Response } from 'express'
import { createClient, Message } from '../lib/hunyuan-chat'
import { logger } from '../config/logger'

export const chatRouter = Router()

// Create Hunyuan client instance
const hunyuanClient = createClient({ timeout: 120 })

// System prompt for customer service
const SYSTEM_PROMPT = `你是一个专业、友好的AI智能客服助手。你的职责是：
1. 热情友好地回答用户的问题
2. 提供准确、有帮助的信息
3. 如果不确定答案，诚实地告知用户
4. 使用简洁清晰的语言回复
5. 在适当的时候提供额外的帮助或建议

请始终保持专业、耐心和礼貌的态度。`

interface ChatMessage {
  role: 'user' | 'assistant'
  content: string
}

interface ChatRequest {
  message: string
  history?: ChatMessage[]
}

interface ChatResponse {
  success: boolean
  data?: {
    reply: string
    usage?: {
      promptTokens: number
      completionTokens: number
      totalTokens: number
    }
  }
  error?: string
}

/**
 * POST /api/chat
 * Send a message and get AI response
 */
chatRouter.post('/', async (req: Request, res: Response) => {
  const startTime = Date.now()
  
  try {
    const { message, history = [] } = req.body as ChatRequest

    if (!message || typeof message !== 'string' || message.trim() === '') {
      res.status(400).json({
        success: false,
        error: '消息内容不能为空'
      } as ChatResponse)
      return
    }

    // Build messages array with system prompt and conversation history
    const messages: Message[] = [
      { Role: 'system', Content: SYSTEM_PROMPT }
    ]

    // Add conversation history (limit to last 10 messages to prevent token overflow)
    const recentHistory = history.slice(-10)
    for (const msg of recentHistory) {
      messages.push({
        Role: msg.role,
        Content: msg.content
      })
    }

    // Add current user message
    messages.push({ Role: 'user', Content: message.trim() })

    logger.info({ messageCount: messages.length }, 'Processing chat request')

    // Call Hunyuan API
    const result = await hunyuanClient.chatCompletions(messages, {
      Temperature: 0.7,
      TopP: 0.9
    })

    const reply = result.Choices[0]?.Message?.Content || '抱歉，我暂时无法回答您的问题。'

    const responseTime = Date.now() - startTime
    logger.info({ responseTime, tokens: result.Usage.TotalTokens }, 'Chat response generated')

    res.json({
      success: true,
      data: {
        reply,
        usage: {
          promptTokens: result.Usage.PromptTokens,
          completionTokens: result.Usage.CompletionTokens,
          totalTokens: result.Usage.TotalTokens
        }
      }
    } as ChatResponse)
  } catch (error: any) {
    logger.error({ error: error.message }, 'Chat API error')
    
    res.status(500).json({
      success: false,
      error: '服务暂时不可用，请稍后再试'
    } as ChatResponse)
  }
})

/**
 * POST /api/chat/stream
 * Send a message and get streaming AI response
 */
chatRouter.post('/stream', async (req: Request, res: Response) => {
  try {
    const { message, history = [] } = req.body as ChatRequest

    if (!message || typeof message !== 'string' || message.trim() === '') {
      res.status(400).json({
        success: false,
        error: '消息内容不能为空'
      })
      return
    }

    // Set headers for SSE
    res.setHeader('Content-Type', 'text/event-stream')
    res.setHeader('Cache-Control', 'no-cache')
    res.setHeader('Connection', 'keep-alive')

    // Build messages array
    const messages: Message[] = [
      { Role: 'system', Content: SYSTEM_PROMPT }
    ]

    const recentHistory = history.slice(-10)
    for (const msg of recentHistory) {
      messages.push({
        Role: msg.role,
        Content: msg.content
      })
    }

    messages.push({ Role: 'user', Content: message.trim() })

    logger.info({ messageCount: messages.length }, 'Processing streaming chat request')

    // Call Hunyuan streaming API
    await hunyuanClient.chatCompletionsStream(
      messages,
      { Temperature: 0.7, TopP: 0.9 },
      (chunk) => {
        if (chunk.Delta.Content) {
          res.write(`data: ${JSON.stringify({ content: chunk.Delta.Content })}\n\n`)
        }
      }
    )

    // Send done signal
    res.write(`data: ${JSON.stringify({ done: true })}\n\n`)
    res.end()
  } catch (error: any) {
    logger.error({ error: error.message }, 'Streaming chat API error')
    
    res.write(`data: ${JSON.stringify({ error: '服务暂时不可用' })}\n\n`)
    res.end()
  }
})
