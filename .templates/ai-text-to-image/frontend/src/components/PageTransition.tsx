import { motion } from "./MotionPrimitives";

const variants = {
  fade: {
    initial: { opacity: 0 },
    animate: { opacity: 1 },
    exit: { opacity: 0 },
  },
  "slide-up": {
    initial: { opacity: 0, y: 16 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -8 },
  },
  "slide-fade": {
    initial: { opacity: 0, x: 12 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: -12 },
  },
  scale: {
    initial: { opacity: 0, scale: 0.98 },
    animate: { opacity: 1, scale: 1 },
    exit: { opacity: 0, scale: 0.98 },
  },
};

type TransitionMode = keyof typeof variants;

interface PageTransitionProps {
  children: React.ReactNode;
  transition?: TransitionMode;
}

/**
 * PageTransition - 页面进入/退出动画包装器
 *
 * 优化说明：
 * - 动画时长缩短至 0.2s（从 0.3s），配合 popLayout 模式更流畅
 * - 位移距离减小（y: 16 而非 24），避免大幅度跳动
 * - 使用 layout 属性确保布局变化时也有平滑过渡
 *
 * ⚠️ 重要：此组件只包裹页面内容区域，
 * Navbar/Header/Sidebar 必须在 AnimatedRoutes 外部，不要包在 PageTransition 里。
 */
export function PageTransition({ children, transition = "fade" }: PageTransitionProps) {
  const v = variants[transition];

  return (
    <motion.div
      layout
      initial={v.initial}
      animate={v.animate}
      exit={v.exit}
      transition={{ duration: 0.2, ease: [0.25, 0.1, 0.25, 1] }}
    >
      {children}
    </motion.div>
  );
}
