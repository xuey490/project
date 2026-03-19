import { useLocation } from "react-router-dom";
import { useEffect } from "react";

const NotFound = () => {
  const location = useLocation();

  useEffect(() => {
    console.error("404 Error: User attempted to access non-existent route:", location.pathname);
  }, [location.pathname]);

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted">
      <div className="text-center">
        <h1
          className="font-bold"
          style={{ fontSize: 'var(--font-size-display)', marginBottom: 'var(--spacing-md)' }}
        >
          404
        </h1>
        <p
          className="text-muted-foreground"
          style={{ fontSize: 'var(--font-size-headline)', marginBottom: 'var(--spacing-md)' }}
        >
          Oops! Page not found
        </p>
        <a href="/" className="text-primary underline hover:text-primary/90">
          Return to Home
        </a>
      </div>
    </div>
  );
};

export default NotFound;
