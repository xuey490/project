import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BrowserRouter, Route } from 'react-router-dom';
import { Toaster } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { AnimatedRoutes } from "@/components/AnimatedRoutes";
import { PageTransition } from "@/components/PageTransition";
import { AuthProvider } from "@/contexts/AuthContext";
import Index from "./pages/Index";
import Gallery from "./pages/Gallery";
import Help from "./pages/Help";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Privacy from "./pages/Privacy";
import Terms from "./pages/Terms";
import NotFound from "./pages/NotFound";

/**
 * Configure TanStack Query client with optimized defaults
 */
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // Data considered fresh for 1 minute
      staleTime: 60 * 1000,
      // Cache data for 5 minutes
      gcTime: 5 * 60 * 1000,
      // Retry failed requests once
      retry: 1,
      // Don't refetch on window focus by default
      refetchOnWindowFocus: false,
      // Don't refetch on reconnect by default
      refetchOnReconnect: false,
    },
    mutations: {
      // Retry failed mutations once
      retry: 1,
    },
  },
});

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <TooltipProvider>
          <Toaster />
          <BrowserRouter>
            <AnimatedRoutes>
              <Route path="/" data-genie-title="首页 - 文生图工具" data-genie-key="Home" element={<PageTransition transition="slide-up"><Index /></PageTransition>} />
              <Route path="/gallery" data-genie-title="我的作品" data-genie-key="Gallery" element={<PageTransition transition="slide-up"><Gallery /></PageTransition>} />
              <Route path="/help" data-genie-title="帮助中心" data-genie-key="Help" element={<PageTransition transition="fade"><Help /></PageTransition>} />
              <Route path="/login" data-genie-title="登录" data-genie-key="Login" element={<PageTransition transition="fade"><Login /></PageTransition>} />
              <Route path="/register" data-genie-title="注册" data-genie-key="Register" element={<PageTransition transition="fade"><Register /></PageTransition>} />
              <Route path="/privacy" data-genie-title="隐私政策" data-genie-key="Privacy" element={<PageTransition transition="fade"><Privacy /></PageTransition>} />
              <Route path="/terms" data-genie-title="用户协议" data-genie-key="Terms" element={<PageTransition transition="fade"><Terms /></PageTransition>} />
              {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
              <Route path="*" data-genie-key="NotFound" data-genie-title="页面未找到" element={<PageTransition transition="fade"><NotFound /></PageTransition>} />
            </AnimatedRoutes>
          </BrowserRouter>
        </TooltipProvider>
      </AuthProvider>
    </QueryClientProvider>
  );
}

export default App
