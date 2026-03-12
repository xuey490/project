# Supabase Authentication - Complete Implementation Guide

## Frontend Implementation

### 1. Supabase Client Setup

**File: `frontend/src/lib/supabase.ts`**

```typescript
import { createClient } from '@supabase/supabase-js';

const SUPABASE_URL = 'https://jvyddfsojtsipmiwjcdr.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imp2eWRkZnNvanRzaXBtaXdqY2RyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIxNDE3NTAsImV4cCI6MjA3NzcxNzc1MH0.BvEXIgONC2GxAUzRIx8I7P3PsTH3p10vh68CEyRPRMg';

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
  auth: {
    autoRefreshToken: true,
    persistSession: true,
    detectSessionInUrl: true
  }
});
```

### 2. Global Authentication Context

**File: `frontend/src/contexts/AuthContext.tsx`**

```typescript
import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { User } from '@supabase/supabase-js';
import { supabase } from '@/lib/supabase';

interface AuthContextType {
  user: User | null;
  loading: boolean;
}

const AuthContext = createContext<AuthContextType>({
  user: null,
  loading: true,
});

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Get initial session
    supabase.auth.getSession().then(({ data: { session } }) => {
      setUser(session?.user ?? null);
      setLoading(false);
    });

    // Listen for auth state changes (handles OAuth callbacks automatically)
    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      (_event, session) => {
        setUser(session?.user ?? null);
        setLoading(false);
      }
    );

    return () => subscription.unsubscribe();
  }, []);

  return (
    <AuthContext.Provider value={{ user, loading }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuthContext = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuthContext must be used within AuthProvider');
  }
  return context;
};
```

### 3. Authentication Hooks

**File: `frontend/src/hooks/use-auth.ts`**

```typescript
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { supabase } from '@/lib/supabase';

interface SignUpParams {
  email: string;
  password: string;
  name?: string;
}

interface SignInParams {
  email: string;
  password: string;
}

/**
 * Sign up with email and password
 */
export const useSignUp = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ email, password, name }: SignUpParams) => {
      const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
          data: { name }
        }
      });

      if (error) throw new Error(error.message);
      return data.user;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
};

/**
 * Sign in with email and password
 */
export const useSignIn = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ email, password }: SignInParams) => {
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password
      });

      if (error) throw new Error(error.message);
      return data.session;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
};

/**
 * Sign in with Google OAuth
 */
export const useGoogleSignIn = () => {
  return useMutation({
    mutationFn: async () => {
      const { data, error } = await supabase.auth.signInWithOAuth({
        provider: 'google',
        options: {
          redirectTo: `${window.location.origin}/login`,
        },
      });

      if (error) throw new Error(error.message);
      return data;
    },
  });
};

/**
 * Sign out
 */
export const useSignOut = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async () => {
      const { error } = await supabase.auth.signOut();
      if (error) throw new Error(error.message);
    },
    onSuccess: () => {
      queryClient.setQueryData(['auth', 'user'], null);
      queryClient.clear();
    },
  });
};

/**
 * Send OTP verification code to email
 */
export const useSendOtp = () => {
  return useMutation({
    mutationFn: async ({ email }: { email: string }) => {
      const { error } = await supabase.auth.signInWithOtp({
        email,
        options: {
          shouldCreateUser: true,
        },
      });

      if (error) throw new Error(error.message);
      return { email };
    },
  });
};

/**
 * Verify OTP code and sign in
 */
export const useVerifyOtp = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ email, token }: { email: string; token: string }) => {
      const { data, error } = await supabase.auth.verifyOtp({
        email,
        token,
        type: 'email',
      });

      if (error) throw new Error(error.message);
      return data.session;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['auth', 'user'] });
    },
  });
};
```

### 4. API Client with Auto JWT

**File: `frontend/src/lib/api-client.ts`**

```typescript
import axios from 'axios';
import { supabase } from './supabase';

export const apiClient = axios.create({
  baseURL: '/api/v1',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor: Auto-add JWT from Supabase
apiClient.interceptors.request.use(
  async (config) => {
    const { data: { session } } = await supabase.auth.getSession();
    
    if (session?.access_token) {
      config.headers.Authorization = `Bearer ${session.access_token}`;
    }
    
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: Auto-refresh on 401
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      try {
        const { data: { session }, error: refreshError } = 
          await supabase.auth.refreshSession();
        
        if (refreshError || !session) {
          await supabase.auth.signOut();
          window.location.href = '/login';
          return Promise.reject(error);
        }
        
        // Retry original request with new token
        const originalRequest = error.config;
        originalRequest.headers.Authorization = `Bearer ${session.access_token}`;
        return apiClient(originalRequest);
      } catch (refreshError) {
        await supabase.auth.signOut();
        window.location.href = '/login';
        return Promise.reject(error);
      }
    }

    return Promise.reject(error);
  }
);
```

### 5. App.tsx Setup

```typescript
import { AuthProvider } from '@/contexts/AuthContext';
import { BrowserRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

const queryClient = new QueryClient();

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          {/* Your routes */}
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  );
}

export default App;
```

### 6. Login Page Example

**File: `frontend/src/pages/Login.tsx`**

```typescript
import { useState, useEffect, useRef } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useSignIn, useGoogleSignIn, useSendOtp, useVerifyOtp } from '@/hooks/use-auth';
import { useAuthContext } from '@/contexts/AuthContext';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();
  const signIn = useSignIn();
  const googleSignIn = useGoogleSignIn();
  const sendOtp = useSendOtp();
  const verifyOtp = useVerifyOtp();
  const { user } = useAuthContext();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [otpCode, setOtpCode] = useState('');
  const [loginMode, setLoginMode] = useState<'password' | 'otp'>('password');
  const [otpStep, setOtpStep] = useState<'send' | 'verify'>('send');
  const hasRedirected = useRef(false);

  // Auto-redirect on OAuth callback or already authenticated
  useEffect(() => {
    const isOAuthCallback = location.hash.includes('access_token');

    if (user && (isOAuthCallback || !hasRedirected.current)) {
      hasRedirected.current = true;

      if (isOAuthCallback) {
        toast.success('Signed in with Google successfully');
      }

      navigate('/', { replace: true });
    }
  }, [user, navigate, location.hash]);

  const handlePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await signIn.mutateAsync({ email, password });
      toast.success('Signed in successfully');
      navigate('/');
    } catch (error) {
      toast.error((error as Error).message);
    }
  };

  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await sendOtp.mutateAsync({ email });
      toast.success('Verification code sent to your email');
      setOtpStep('verify');
    } catch (error) {
      toast.error((error as Error).message);
    }
  };

  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await verifyOtp.mutateAsync({ email, token: otpCode });
      toast.success('Signed in successfully');
      navigate('/');
    } catch (error) {
      toast.error((error as Error).message);
    }
  };

  const handleGoogleSignIn = async () => {
    try {
      await googleSignIn.mutateAsync();
    } catch (error) {
      toast.error((error as Error).message);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Sign In</CardTitle>
        </CardHeader>
        <CardContent>
          {/* Login mode tabs */}
          <div className="flex border-b mb-4">
            <button
              type="button"
              className={`flex-1 pb-2 text-sm font-medium ${
                loginMode === 'password'
                  ? 'border-b-2 border-primary text-primary'
                  : 'text-muted-foreground'
              }`}
              onClick={() => { setLoginMode('password'); setOtpStep('send'); setOtpCode(''); }}
            >
              Password Login
            </button>
            <button
              type="button"
              className={`flex-1 pb-2 text-sm font-medium ${
                loginMode === 'otp'
                  ? 'border-b-2 border-primary text-primary'
                  : 'text-muted-foreground'
              }`}
              onClick={() => { setLoginMode('otp'); }}
            >
              Verification Code
            </button>
          </div>

          {/* Password login form */}
          {loginMode === 'password' && (
            <form onSubmit={handlePasswordSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="password">Password</Label>
                <Input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>
              <Button type="submit" className="w-full" disabled={signIn.isPending}>
                {signIn.isPending ? 'Signing in...' : 'Sign In'}
              </Button>
            </form>
          )}

          {/* OTP login form - Step 1: Send code */}
          {loginMode === 'otp' && otpStep === 'send' && (
            <form onSubmit={handleSendOtp} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="otp-email">Email</Label>
                <Input
                  id="otp-email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>
              <Button type="submit" className="w-full" disabled={sendOtp.isPending}>
                {sendOtp.isPending ? 'Sending...' : 'Send Verification Code'}
              </Button>
            </form>
          )}

          {/* OTP login form - Step 2: Verify code */}
          {loginMode === 'otp' && otpStep === 'verify' && (
            <form onSubmit={handleVerifyOtp} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="otp-email-display">Email</Label>
                <Input
                  id="otp-email-display"
                  type="email"
                  value={email}
                  disabled
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="otp-code">Verification Code</Label>
                <Input
                  id="otp-code"
                  type="text"
                  inputMode="numeric"
                  maxLength={6}
                  placeholder="Enter 6-digit code"
                  value={otpCode}
                  onChange={(e) => setOtpCode(e.target.value)}
                  required
                />
              </div>
              <Button type="submit" className="w-full" disabled={verifyOtp.isPending}>
                {verifyOtp.isPending ? 'Verifying...' : 'Verify & Sign In'}
              </Button>
              <Button
                type="button"
                variant="link"
                className="w-full"
                onClick={() => { setOtpStep('send'); setOtpCode(''); }}
              >
                Resend Code
              </Button>
            </form>
          )}

          <div className="relative my-4">
            <div className="absolute inset-0 flex items-center">
              <span className="w-full border-t" />
            </div>
            <div className="relative flex justify-center text-xs uppercase">
              <span className="bg-background px-2 text-muted-foreground">Or</span>
            </div>
          </div>

          <Button
            type="button"
            variant="outline"
            className="w-full"
            onClick={handleGoogleSignIn}
          >
            Continue with Google
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}
```

### 7. Protected Route Component

```typescript
import { useAuthContext } from '@/contexts/AuthContext';
import { Navigate } from 'react-router-dom';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
  const { user, loading } = useAuthContext();

  if (loading) {
    return <div className="min-h-screen flex items-center justify-center">Loading...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
}
```

---

## Backend Implementation

### 1. JWT Verification Middleware

**File: `backend/src/middleware/auth.middleware.ts`**

```typescript
import { Request, Response, NextFunction } from 'express';
import { createClient } from '@supabase/supabase-js';
import { prisma } from '../config/database.js';
import { createLogger } from '../config/logger.js';

const logger = createLogger('AuthMiddleware');

const supabase = createClient(
  'https://jvyddfsojtsipmiwjcdr.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imp2eWRkZnNvanRzaXBtaXdqY2RyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIxNDE3NTAsImV4cCI6MjA3NzcxNzc1MH0.BvEXIgONC2GxAUzRIx8I7P3PsTH3p10vh68CEyRPRMg'
);

declare global {
  namespace Express {
    interface Request {
      user?: {
        id: string;
        email: string;
        name?: string;
      };
    }
  }
}

/**
 * Middleware to require authentication via Supabase JWT
 * Automatically syncs user to local database on first access
 */
export async function requireAuth(req: Request, res: Response, next: NextFunction) {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader?.startsWith('Bearer ')) {
      return res.status(401).json({
        status: 'error',
        message: 'No authorization token provided'
      });
    }

    const token = authHeader.replace('Bearer ', '');

    // Verify JWT with Supabase
    const { data: { user }, error } = await supabase.auth.getUser(token);

    if (error || !user) {
      logger.warn({ error: error?.message }, 'Invalid JWT token');
      return res.status(401).json({
        status: 'error',
        message: 'Invalid or expired token'
      });
    }

    // Check if user exists in local database
    let localUser = await prisma.user.findUnique({
      where: { id: user.id },
      select: {
        id: true,
        email: true,
        name: true
      }
    });

    // Auto-sync: Create user in local database if doesn't exist
    if (!localUser) {
      const name = user.user_metadata?.name || 
                   user.user_metadata?.full_name || 
                   user.email?.split('@')[0] || 
                   'User';

      localUser = await prisma.user.create({
        data: {
          id: user.id,
          email: user.email!,
          name
        },
        select: {
          id: true,
          email: true,
          name: true
        }
      });

      logger.info({ userId: localUser.id, email: localUser.email }, 'New user auto-synced from Supabase');
    }

    // Attach user to request for downstream handlers
    req.user = localUser;
    
    next();
  } catch (error) {
    logger.error({ error }, 'Authentication error');
    return res.status(401).json({
      status: 'error',
      message: 'Authentication failed'
    });
  }
}
```

### 2. Apply Middleware to Routes

**Example: `backend/src/modules/todo.ts`**

```typescript
import { Router } from 'express';
import { requireAuth } from '../middleware/auth.middleware.js';
import { prisma } from '../config/database.js';

export const todoRouter = Router();

// Apply auth middleware to all routes
todoRouter.use(requireAuth);

// All routes below are protected
todoRouter.get('/', async (req, res) => {
  const todos = await prisma.todo.findMany({
    where: { userId: req.user!.id }
  });
  
  res.json({ status: 'success', data: todos });
});

todoRouter.post('/', async (req, res) => {
  const todo = await prisma.todo.create({
    data: {
      ...req.body,
      userId: req.user!.id
    }
  });
  
  res.json({ status: 'success', data: todo });
});
```

---

## Authentication Flows

### Email/Password Sign Up Flow

```
1. User fills form → clicks "Sign Up"
2. Frontend: supabase.auth.signUp({ email, password })
3. Supabase creates account and sends verification email
4. Frontend shows: "Please check your email for verification"
5. User clicks email link → email verified
6. User can now sign in
```

### Email/Password Sign In Flow

```
1. User fills form → clicks "Sign In"
2. Frontend: supabase.auth.signInWithPassword({ email, password })
3. Supabase returns session with JWT
4. Session auto-stored in localStorage
5. Frontend redirects to home
6. API requests auto-include JWT in Authorization header
7. Backend verifies JWT and auto-creates user if first time
```

### Google OAuth Flow

```
1. User clicks "Continue with Google"
2. Frontend: supabase.auth.signInWithOAuth({ provider: 'google' })
3. Browser redirects to Google login page
4. User authorizes → Google redirects back to /login#access_token=xxx
5. Supabase auto-extracts token and creates session
6. AuthContext's onAuthStateChange triggers
7. Login page's useEffect detects user and redirects to home
8. Backend auto-creates user on first API request
```

### Email OTP Sign In Flow

```
1. User selects "Verification Code" tab on login page
2. User enters email → clicks "Send Verification Code"
3. Frontend: supabase.auth.signInWithOtp({ email })
4. Supabase sends a 6-digit code to the user's email
5. Frontend shows code input form
6. User enters 6-digit code → clicks "Verify & Sign In"
7. Frontend: supabase.auth.verifyOtp({ email, token, type: 'email' })
8. Supabase returns session with JWT
9. Session auto-stored in localStorage
10. Frontend redirects to home
11. Backend auto-creates user on first API request
```

---

## Troubleshooting

### Common Mistake: Creating Backend Auth Routes

**Problem:** Creating `/api/auth/login` or `/api/auth/signup` on backend

**Solution:** Delete them. Use Supabase SDK on frontend instead:

```typescript
// ❌ WRONG - Don't create backend auth routes
router.post('/api/auth/login', async (req, res) => { ... });

// ✅ CORRECT - Use Supabase SDK on frontend
const { data, error } = await supabase.auth.signInWithPassword({ email, password });
```

### Authentication State Issues

**Problem:** Login succeeds but page redirects back to login, or user shows as not authenticated

**Common Causes:**

1. **Using localStorage instead of AuthContext**
   ```typescript
   // ❌ Wrong
   const userId = localStorage.getItem('userId');
   
   // ✅ Correct
   const { user, loading } = useAuthContext();
   ```

2. **Backend uses old x-user-id header**
   ```typescript
   // ❌ Wrong
   const userId = req.headers['x-user-id'];
   
   // ✅ Correct
   import { requireAuth } from '../middleware/auth.middleware.js';
   router.use(requireAuth);
   const userId = req.user.id;
   ```

3. **Prisma schema missing fields**
   - Error: `Unknown field subscriptionTier`
   - Solution: Add missing fields to User model, run `npx prisma db push`

**Quick fix:** Follow migration steps in SKILL.md Section 0.

### OAuth Redirect Issues

**Problem:** OAuth callback returns to `/login` but doesn't redirect

**Solution:** Ensure `useEffect` monitors `location.hash`:

```typescript
useEffect(() => {
  const isOAuthCallback = location.hash.includes('access_token');
  if (user && isOAuthCallback) {
    navigate('/', { replace: true });
  }
}, [user, location.hash]);
```

### Infinite Redirect Loop

**Problem:** Page keeps redirecting repeatedly

**Solution:** Use `useRef` to prevent duplicate redirects:

```typescript
const hasRedirected = useRef(false);

if (user && !hasRedirected.current) {
  hasRedirected.current = true;
  navigate('/', { replace: true });
}
```

### Token Not Sent to Backend

**Problem:** API returns 401 even when logged in

**Solution:** Verify API client interceptor is async:

```typescript
apiClient.interceptors.request.use(
  async (config) => {  // ← Must be async!
    const { data: { session } } = await supabase.auth.getSession();
    config.headers.Authorization = `Bearer ${session.access_token}`;
    return config;
  }
);
```

### Infinite Loop on Login Page

**Problem:** Page keeps reloading due to 401 errors

**Cause:** Making authenticated API calls before user is logged in

**Solution:** Disable queries when not authenticated:

```typescript
const { data } = useQuery({
  queryKey: ['todos'],
  queryFn: fetchTodos,
  enabled: !!user  // ← Only fetch when authenticated
});
```

### OTP Code Not Received or Invalid

**Problem:** User doesn't receive OTP email, or the code is rejected

**Common Causes:**

1. **Email not delivered** - Check spam/junk folder. Supabase free tier has rate limits on emails.

2. **Code expired** - OTP codes expire after a few minutes. User should request a new code.

3. **Wrong type parameter in verifyOtp**
   ```typescript
   // ❌ Wrong
   await supabase.auth.verifyOtp({ email, token, type: 'magiclink' });

   // ✅ Correct
   await supabase.auth.verifyOtp({ email, token, type: 'email' });
   ```

4. **Code already used** - Each OTP code can only be verified once. Request a new one.

### User Not Created in Database

**Problem:** User authenticated but not in local DB

**Solution:** Check Prisma schema matches expected structure:

```prisma
model User {
  id        String   @id  // Must match Supabase user.id
  email     String   @unique
  name      String?
  createdAt DateTime @default(now())
}
```

---

## File Structure Summary

```
project/
├── frontend/
│   ├── src/
│   │   ├── contexts/
│   │   │   └── AuthContext.tsx          # Global auth state
│   │   ├── hooks/
│   │   │   └── use-auth.ts              # Auth mutations
│   │   ├── lib/
│   │   │   ├── supabase.ts              # Supabase client
│   │   │   └── api-client.ts            # Axios with auto JWT
│   │   ├── pages/
│   │   │   ├── Login.tsx                # Login page
│   │   │   └── SignUp.tsx               # Signup page
│   │   └── App.tsx                      # Wrap with AuthProvider
│   └── package.json
└── backend/
    ├── src/
    │   ├── middleware/
    │   │   └── auth.middleware.ts       # JWT verification
    │   ├── modules/
    │   │   └── [resource].ts            # Protected routes
    │   └── app.ts                       # No auth routes needed
    └── package.json
```
