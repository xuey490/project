// 存档管理 UI 组件
import { motion, AnimatePresence } from 'framer-motion'
import { useState, useEffect, forwardRef } from 'react'
import {
  Save,
  FolderOpen,
  Trash2,
  Download,
  Upload,
  Plus,
  Clock,
  Calendar,
  Gamepad2,
  X,
  Check,
  AlertTriangle,
} from 'lucide-react'
import { useSaveSystem, type SaveMeta } from '@/storage'
import {
  Button,
  Card,
  CardTitle,
  CardContent,
  CardActions,
  Dialog,
  DialogContent,
  DialogTitle,
  DialogDescription,
  DialogClose,
  Input,
} from '@/components'
import { cn } from '@/utils'

// ============================================
// 工具函数
// ============================================

function formatPlaytime(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)

  if (hours > 0) {
    return `${hours}小时 ${minutes}分`
  }
  return `${minutes}分钟`
}

function formatDate(timestamp: number): string {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return '刚刚'
  if (diffMins < 60) return `${diffMins}分钟前`
  if (diffHours < 24) return `${diffHours}小时前`
  if (diffDays < 7) return `${diffDays}天前`

  return date.toLocaleDateString('zh-CN', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// ============================================
// 存档卡片
// ============================================

interface SaveCardProps {
  save: SaveMeta
  isSelected?: boolean
  onSelect: () => void
  onLoad: () => void
  onDelete: () => void
}

const SaveCard = forwardRef<HTMLDivElement, SaveCardProps>(function SaveCard(
  { save, isSelected, onSelect, onLoad, onDelete },
  ref
) {
  const [showDelete, setShowDelete] = useState(false)

  return (
    <Card
      ref={ref}
      selected={isSelected}
      interactive
      onClick={onSelect}
    >
      <CardTitle>{save.name}</CardTitle>

      <CardContent>
        <div className="flex items-center gap-2">
          <Gamepad2 className="w-4 h-4 text-purple-400" />
          <span>关卡 {save.level}</span>
          <span className="text-slate-600">|</span>
          <span className="text-cyan-400">{save.scene}</span>
        </div>

        <div className="flex items-center gap-2">
          <Clock className="w-4 h-4 text-yellow-400" />
          <span>{formatPlaytime(save.playtime)}</span>
        </div>

        <div className="flex items-center gap-2">
          <Calendar className="w-4 h-4 text-green-400" />
          <span>{formatDate(save.updatedAt)}</span>
        </div>
      </CardContent>

      <CardActions>
        <Button
          onClick={(e) => {
            e.stopPropagation()
            onLoad()
          }}
          className="flex-1 flex items-center justify-center gap-1 px-3 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/40 border border-cyan-500/50 text-cyan-300 text-sm transition-colors"
        >
          <FolderOpen className="w-4 h-4" />
          加载
        </Button>

        {showDelete ? (
          <div className="flex gap-1">
            <Button
              onClick={(e) => {
                e.stopPropagation()
                onDelete()
                setShowDelete(false)
              }}
              className="px-2 py-1.5 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 text-red-400 text-sm transition-colors"
            >
              <Check className="w-4 h-4" />
            </Button>
            <Button
              onClick={(e) => {
                e.stopPropagation()
                setShowDelete(false)
              }}
              className="px-2 py-1.5 bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600 text-slate-400 text-sm transition-colors"
            >
              <X className="w-4 h-4" />
            </Button>
          </div>
        ) : (
          <Button
            onClick={(e) => {
              e.stopPropagation()
              setShowDelete(true)
            }}
            className="px-2 py-1.5 bg-slate-700/50 hover:bg-red-500/20 border border-slate-600 hover:border-red-500/50 text-slate-400 hover:text-red-400 text-sm transition-colors"
          >
            <Trash2 className="w-4 h-4" />
          </Button>
        )}
      </CardActions>
    </Card>
  )
})

// ============================================
// 创建存档对话框
// ============================================

interface CreateSaveDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onConfirm: (name: string) => void
}

function CreateSaveDialog({ open, onOpenChange, onConfirm }: CreateSaveDialogProps) {
  const [name, setName] = useState('')

  const handleConfirm = () => {
    if (name.trim()) {
      onConfirm(name.trim())
      setName('')
      onOpenChange(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <AnimatePresence>
        {open && (
          <DialogContent className="w-full max-w-md">
            <DialogTitle>
              <Plus className="w-5 h-5 text-cyan-400" /> 创建新存档
            </DialogTitle>
            <DialogDescription>输入存档名称以创建新的游戏存档</DialogDescription>

            <Input
              value={name}
              onChange={(e) => setName(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleConfirm()}
              placeholder="输入存档名称..."
              autoFocus
              className="mb-4"
            />

            <div className="flex gap-3">
              <Button
                onClick={handleConfirm}
                disabled={!name.trim()}
                className="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-cyan-500/20 hover:bg-cyan-500/40 border border-cyan-500/50 text-cyan-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <Plus className="w-4 h-4" />
                创建
              </Button>
              <DialogClose asChild>
                <Button className="px-4 py-2 bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600 text-slate-300 transition-colors">
                  取消
                </Button>
              </DialogClose>
            </div>
          </DialogContent>
        )}
      </AnimatePresence>
    </Dialog>
  )
}

// ============================================
// 通知组件
// ============================================

interface NotificationProps {
  type: 'success' | 'error'
  message: string
}

function Notification({ type, message }: NotificationProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 20 }}
      className={cn(
        'notification',
        type === 'success' ? 'notification-success' : 'notification-error'
      )}
    >
      <div className="flex items-center gap-2">
        {type === 'success' ? (
          <Check className="w-5 h-5" />
        ) : (
          <AlertTriangle className="w-5 h-5" />
        )}
        {message}
      </div>
    </motion.div>
  )
}

// ============================================
// 存档管理面板主组件
// ============================================

interface SaveManagerProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  onSaveLoaded?: () => void
}

export function SaveManager({ open, onOpenChange, onSaveLoaded }: SaveManagerProps) {
  const {
    savesList,
    currentSaveId,
    isLoading,
    isSaving,
    initialize,
    createSave,
    loadSave,
    deleteSave,
    save,
    exportSave,
    importSave,
  } = useSaveSystem()

  const [selectedId, setSelectedId] = useState<string | null>(null)
  const [showCreateDialog, setShowCreateDialog] = useState(false)
  const [notification, setNotification] = useState<NotificationProps | null>(null)

  useEffect(() => {
    if (open) {
      initialize()
    }
  }, [open, initialize])

  const showNotification = (type: 'success' | 'error', message: string) => {
    setNotification({ type, message })
    setTimeout(() => setNotification(null), 3000)
  }

  const handleCreate = async (name: string) => {
    const id = await createSave(name)
    if (id) {
      showNotification('success', '存档创建成功')
    } else {
      showNotification('error', '创建存档失败')
    }
  }

  const handleLoad = async (saveId: string) => {
    const success = await loadSave(saveId)
    if (success) {
      showNotification('success', '存档加载成功')
      onSaveLoaded?.()
      onOpenChange(false)
    } else {
      showNotification('error', '加载存档失败')
    }
  }

  const handleDelete = async (saveId: string) => {
    const success = await deleteSave(saveId)
    if (success) {
      showNotification('success', '存档已删除')
      if (selectedId === saveId) setSelectedId(null)
    } else {
      showNotification('error', '删除存档失败')
    }
  }

  const handleSave = async () => {
    const success = await save()
    if (success) {
      showNotification('success', '游戏已保存')
    } else {
      showNotification('error', '保存失败')
    }
  }

  const handleExport = async () => {
    if (!selectedId) return
    const data = await exportSave(selectedId)
    if (data) {
      const blob = new Blob([data], { type: 'application/json' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `save_${selectedId}.json`
      a.click()
      URL.revokeObjectURL(url)
      showNotification('success', '存档已导出')
    }
  }

  const handleImport = () => {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = '.json'
    input.onchange = async (e) => {
      const file = (e.target as HTMLInputElement).files?.[0]
      if (!file) return

      const reader = new FileReader()
      reader.onload = async () => {
        const data = reader.result as string
        const id = await importSave(data)
        if (id) {
          showNotification('success', '存档导入成功')
        } else {
          showNotification('error', '导入存档失败')
        }
      }
      reader.readAsText(file)
    }
    input.click()
  }

  return (
    <>
      <Dialog open={open} onOpenChange={onOpenChange}>
        <AnimatePresence>
          {open && (
            <DialogContent className="w-[calc(100vw-2rem)] md:w-[calc(100vw-5rem)] max-w-6xl h-[calc(100vh-4rem)] md:h-[calc(100vh-5rem)]" showClose={false}>
              <div className="flex flex-col h-full -m-6">
                {/* 头部 */}
                <div className="flex items-center justify-between p-4 border-b border-cyan-500/30">
                  <DialogTitle className="mb-0">
                    <Save className="w-5 h-5 text-cyan-400" /> 存档管理
                  </DialogTitle>

                  <DialogClose asChild>
                    <Button className="p-2 hover:bg-slate-700/50 text-slate-400 hover:text-white transition-colors">
                      <X className="w-6 h-6" />
                    </Button>
                  </DialogClose>
                </div>

                {/* 工具栏 */}
                <div className="flex flex-wrap gap-2 p-4 border-b border-cyan-500/20">
                  <Button
                    onClick={() => setShowCreateDialog(true)}
                    className="flex items-center gap-2 px-4 py-2 bg-cyan-500/20 hover:bg-cyan-500/40 border border-cyan-500/50 text-cyan-300 transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                    新建存档
                  </Button>

                  {currentSaveId && (
                    <Button
                      onClick={handleSave}
                      disabled={isSaving}
                      className="flex items-center gap-2 px-4 py-2 bg-green-500/20 hover:bg-green-500/40 border border-green-500/50 text-green-300 disabled:opacity-50 transition-colors"
                    >
                      <Save className="w-4 h-4" />
                      {isSaving ? '保存中...' : '保存游戏'}
                    </Button>
                  )}

                  <div className="flex-1" />

                  <Button
                    onClick={handleExport}
                    disabled={!selectedId}
                    className="flex items-center gap-2 px-3 py-2 bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600 text-slate-300 disabled:opacity-50 transition-colors"
                  >
                    <Download className="w-4 h-4" />
                    导出
                  </Button>

                  <Button
                    onClick={handleImport}
                    className="flex items-center gap-2 px-3 py-2 bg-slate-700/50 hover:bg-slate-600/50 border border-slate-600 text-slate-300 transition-colors"
                  >
                    <Upload className="w-4 h-4" />
                    导入
                  </Button>
                </div>

                {/* 存档列表 */}
                <div className="flex-1 overflow-y-auto p-4">
                  {isLoading ? (
                    <div className="flex items-center justify-center h-full">
                      <div className="text-cyan-400 animate-pulse">加载中...</div>
                    </div>
                  ) : savesList.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-full text-slate-500">
                      <Save className="w-16 h-16 mb-4 opacity-50" />
                      <p className="text-lg">暂无存档</p>
                      <p className="text-sm mt-2">点击「新建存档」开始游戏</p>
                    </div>
                  ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      <AnimatePresence mode="popLayout">
                        {savesList.map((save) => (
                          <SaveCard
                            key={save.id}
                            save={save}
                            isSelected={selectedId === save.id}
                            onSelect={() => setSelectedId(save.id)}
                            onLoad={() => handleLoad(save.id)}
                            onDelete={() => handleDelete(save.id)}
                          />
                        ))}
                      </AnimatePresence>
                    </div>
                  )}
                </div>

                {/* 通知 */}
                <AnimatePresence>
                  {notification && <Notification {...notification} />}
                </AnimatePresence>
              </div>
            </DialogContent>
          )}
        </AnimatePresence>
      </Dialog>

      <CreateSaveDialog
        open={showCreateDialog}
        onOpenChange={setShowCreateDialog}
        onConfirm={handleCreate}
      />
    </>
  )
}

// ============================================
// 快速保存/加载按钮组件
// ============================================

export function QuickSaveButtons() {
  const { quickSave, quickLoad, currentSaveId, isSaving, isLoading } = useSaveSystem()
  const [message, setMessage] = useState<string | null>(null)

  const handleQuickSave = async () => {
    if (!currentSaveId) {
      setMessage('请先创建存档')
      setTimeout(() => setMessage(null), 2000)
      return
    }

    const success = await quickSave()
    setMessage(success ? '快速保存成功' : '保存失败')
    setTimeout(() => setMessage(null), 2000)
  }

  const handleQuickLoad = async () => {
    const success = await quickLoad()
    setMessage(success ? '快速加载成功' : '没有快速存档')
    setTimeout(() => setMessage(null), 2000)
  }

  return (
    <div className="flex gap-2">
      <Button
        onClick={handleQuickSave}
        disabled={isSaving}
        className="px-3 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/40 border border-cyan-500/50 text-cyan-300 text-sm disabled:opacity-50 transition-colors"
        title="快速保存 (F5)"
      >
        {isSaving ? '...' : 'F5'}
      </Button>

      <Button
        onClick={handleQuickLoad}
        disabled={isLoading}
        className="px-3 py-1.5 bg-purple-500/20 hover:bg-purple-500/40 border border-purple-500/50 text-purple-300 text-sm disabled:opacity-50 transition-colors"
        title="快速加载 (F9)"
      >
        {isLoading ? '...' : 'F9'}
      </Button>

      <AnimatePresence>
        {message && (
          <motion.span
            initial={{ opacity: 0, x: -10 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0 }}
            className="text-sm text-yellow-400 self-center"
          >
            {message}
          </motion.span>
        )}
      </AnimatePresence>
    </div>
  )
}
