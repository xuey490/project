import { Link } from 'react-router-dom';

export function Footer() {
  return (
    <footer className="h-[50px] border-t border-border bg-background">
      <div className="mx-auto h-full max-w-[1200px] px-4 sm:px-6 flex items-center justify-center gap-4 text-xs text-muted-foreground">
        <span>© 2026 ImageGen</span>
        <span className="text-border">|</span>
        <Link to="/privacy" className="hover:text-foreground transition-colors">隐私政策</Link>
        <span className="text-border">|</span>
        <Link to="/terms" className="hover:text-foreground transition-colors">用户协议</Link>
      </div>
    </footer>
  );
}
