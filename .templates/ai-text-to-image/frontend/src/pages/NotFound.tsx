import { useLocation, Link } from "react-router-dom";
import { useEffect } from "react";
import { Layout } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Home } from "lucide-react";

const NotFound = () => {
  const location = useLocation();

  useEffect(() => {
    console.error("404 Error: User attempted to access non-existent route:", location.pathname);
  }, [location.pathname]);

  return (
    <Layout hideFooter>
      <div className="min-h-[calc(100vh-60px)] flex items-center justify-center">
        <div className="text-center px-4">
          <h1 className="text-6xl font-semibold text-muted-foreground/30 mb-4">404</h1>
          <p className="text-lg text-foreground mb-2">页面未找到</p>
          <p className="text-sm text-muted-foreground mb-6">
            您访问的页面不存在或已被移除
          </p>
          <Link to="/">
            <Button className="gap-2">
              <Home className="w-4 h-4" strokeWidth={1.5} />
              返回首页
            </Button>
          </Link>
        </div>
      </div>
    </Layout>
  );
};

export default NotFound;
