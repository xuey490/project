import { Routes, useLocation } from "react-router-dom";
import { AnimatePresence } from "framer-motion";

interface AnimatedRoutesProps {
  children: React.ReactNode;
}

/**
 * AnimatedRoutes - 页面切换动画容器
 *
 * 使用 mode="popLayout" 而非 "wait"：
 * - "wait" 会等待退出动画完成才开始进入动画（总延迟 ~0.6s）
 * - "popLayout" 允许新页面立即进入，旧页面同时退出（更流畅）
 *
 * ⚠️ 重要：Navbar/Header/Sidebar 必须放在 AnimatedRoutes 外部，
 * 否则每次页面切换都会重新创建并参与动画。
 */
export function AnimatedRoutes({ children }: AnimatedRoutesProps) {
  const location = useLocation();

  return (
    <AnimatePresence mode="popLayout">
      <Routes location={location} key={location.pathname}>
        {children}
      </Routes>
    </AnimatePresence>
  );
}
