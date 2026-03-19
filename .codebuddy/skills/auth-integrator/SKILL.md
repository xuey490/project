---
name: auth-integrator
description: Implement user register/login/authentication for web applications. Use this skill when the application requires user authentication (email/password, email verification code (OTP), Google/Github or any other OAuth), session management, or protected routes. Triggers on requests for login, signup, sign-out, user accounts, or access control. Powered by Supabase Auth.
_meta_type: sdk
---

# Authentication Integration

Implement frontend-driven authentication using Supabase Auth with JWT verification on the backend.

## Core Principle: Frontend-Only Authentication

**⚠️ CRITICAL: ALL authentication logic MUST use Supabase SDK on frontend ONLY.**

**What this means:**

✅ **DO on Frontend:**
- Sign up: `supabase.auth.signUp()`
- Sign in: `supabase.auth.signInWithPassword()`
- OTP send: `supabase.auth.signInWithOtp()`
- OTP verify: `supabase.auth.verifyOtp()`
- OAuth: `supabase.auth.signInWithOAuth()`
- Sign out: `supabase.auth.signOut()`
- Password reset: `supabase.auth.resetPasswordForEmail()`
- Get session: `supabase.auth.getSession()`
- All auth UI components (login forms, OTP verification, OAuth buttons, password reset)

❌ **DO NOT on Backend:**
- No POST `/api/auth/signup` routes
- No POST `/api/auth/login` routes
- No POST `/api/auth/logout` routes
- No password hashing/verification code
- No session creation/management code
- No OAuth callback handlers

**Backend's ONLY job:** Verify JWT tokens using `requireAuth` middleware.

**Why this is simpler:**
- Single source of truth (Supabase manages everything)
- No duplicate auth logic between frontend/backend
- Automatic session refresh and OAuth handling
- No need to sync auth state manually

## Prerequisites

**Required: Backend service must exist in the project.**

This SDK requires both frontend and backend integration:
- Frontend: Supabase Auth client for user authentication
- Backend: JWT verification middleware for protected API routes

Do NOT proceed if:
- Project is frontend-only (no `backend/` directory)
- Project has no API routes that need protection

If backend is missing, inform user: "Supabase Auth integration requires a backend service for JWT verification and protected API routes. Please initialize project with backend template first."

## Architecture Overview

```
Frontend (React + Supabase)           Backend (Express + JWT)
├── ✅ Sign Up (Supabase SDK)         ├── ❌ NO /api/auth/signup
├── ✅ Sign In (Supabase SDK)         ├── ❌ NO /api/auth/login
├── ✅ Email OTP (Supabase SDK)       ├── ❌ NO /api/auth/logout
├── ✅ OAuth (Supabase SDK)           ├── ✅ requireAuth middleware
├── ✅ Sign Out (Supabase SDK)        ├── ✅ Verify JWT only
├── ✅ Password Reset (Supabase SDK)  └── ✅ Auto-sync users to DB
├── ✅ Session Management
└── ✅ Global AuthContext
```

**Key Principles:**
1. **Frontend handles ALL authentication** - Use Supabase SDK for all auth operations
2. **Backend only verifies JWT** - No auth routes, no password handling
3. **Single source of truth** - Supabase manages sessions and user state
4. **Auto user sync** - Backend creates local user records on first API access

## Implementation Steps

### 0. CRITICAL: Migrate Existing Authentication Code

**Only needed if integrating into an existing project with authentication.**

Follow this simple 3-step migration:

#### Step 1: Delete Backend Auth Routes

Remove all backend auth endpoints (they're replaced by Supabase SDK):
```bash
# Search for backend auth routes
grep -r "'/auth/" backend/src/
grep -r "'/api/auth" backend/src/
```

Delete any routes like:
- `/auth/signup`, `/auth/login`, `/auth/logout`
- `/api/auth/*`, `/api/users/login`

#### Step 2: Replace Frontend Auth State

**Search and replace across frontend:**

| Old Pattern | New Pattern | Search Command |
|-------------|-------------|----------------|
| `localStorage.getItem('userId')` | `useAuthContext()` | `grep -r "localStorage.getItem('user" frontend/` |
| `localStorage.removeItem('userId')` | `supabase.auth.signOut()` | `grep -r "localStorage.remove" frontend/` |
| Custom login API calls | `supabase.auth.signInWithPassword()` | `grep -r "/api/auth" frontend/` |

**Example replacement:**
```typescript
// ❌ Old: localStorage check
const userId = localStorage.getItem('userId');
if (!userId) navigate('/signin');

// ✅ New: AuthContext
const { user, loading } = useAuthContext();
useEffect(() => {
  if (!loading && !user) navigate('/signin');
}, [user, loading]);
```

#### Step 3: Replace Backend Auth Checks

**Search and replace in backend routes:**
```typescript
// ❌ Old: Custom header check
const userId = req.headers['x-user-id'] as string;
if (!userId) return res.status(401).json({...});

// ✅ New: requireAuth middleware
import { requireAuth } from '../middleware/auth.middleware.js';
router.use(requireAuth);
// Then use: req.user.id
```

**Search command:**
```bash
grep -r "x-user-id" backend/src/
```

---

### 1. Install Dependencies

**Frontend:**
```bash
cd frontend && npm install @supabase/supabase-js
```

**Backend:**
```bash
cd backend && npm install @supabase/supabase-js
```

### 2. Frontend Setup

Create these files in order:

1. **`frontend/src/lib/supabase.ts`** - Supabase client (hardcoded credentials)
2. **`frontend/src/contexts/AuthContext.tsx`** - Global auth state provider
3. **`frontend/src/hooks/use-auth.ts`** - Auth mutations (signUp, signIn, signOut, googleSignIn, sendOtp, verifyOtp)
4. **`frontend/src/lib/api-client.ts`** - Axios with auto JWT injection
5. **Login/SignUp pages** - Using hooks and context

**Critical:** Wrap app with `<AuthProvider>` in `App.tsx`.

### 3. Backend Setup

Create:
1. **`backend/src/middleware/auth.middleware.ts`** - JWT verification + auto user sync
2. Apply `requireAuth` middleware to protected routes

### 4. Database Schema

**CRITICAL: Before adding User model, scan existing code for User-related queries to identify all required fields.**

**Search for patterns like:**
- `prisma.user.findUnique({ select: { ... } })`
- `user.subscriptionTier`
- `user.apiKey`
- `user.avatarUrl`
- Any other `user.*` field references in backend code

**Recommended comprehensive User model in `prisma/schema.prisma`:**

```prisma
enum SubscriptionTier {
  FREE
  PRO
  ENTERPRISE
}

model User {
  id               String           @id  // Matches Supabase user.id (UUID)
  email            String           @unique
  name             String
  avatarUrl        String?          @map("avatar_url")
  apiKey           String?          @default(uuid()) @map("api_key")
  subscriptionTier SubscriptionTier @default(FREE) @map("subscription_tier")
  createdAt        DateTime         @default(now()) @map("created_at")
  updatedAt        DateTime         @updatedAt @map("updated_at")

  // Add relations based on existing models
  // Example:
  // projects      Project[]
  // usageMetrics  UsageMetric[]

  @@map("users")
}
```

**After updating schema, run migration:**
```bash
cd backend && npx prisma db push
```

## Quick Reference

### Supabase Credentials (Hardcoded)

```typescript
const SUPABASE_URL = 'https://jvyddfsojtsipmiwjcdr.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imp2eWRkZnNvanRzaXBtaXdqY2RyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIxNDE3NTAsImV4cCI6MjA3NzcxNzc1MH0.BvEXIgONC2GxAUzRIx8I7P3PsTH3p10vh68CEyRPRMg';
```

### Auth Methods

| Method | Frontend Call |
|--------|--------------|
| Email Sign Up | `supabase.auth.signUp({ email, password })` |
| Email Sign In | `supabase.auth.signInWithPassword({ email, password })` |
| Send Email OTP | `supabase.auth.signInWithOtp({ email })` |
| Verify Email OTP | `supabase.auth.verifyOtp({ email, token, type: 'email' })` |
| Google OAuth | `supabase.auth.signInWithOAuth({ provider: 'google' })` |
| Sign Out | `supabase.auth.signOut()` |
| Get Session | `supabase.auth.getSession()` |
| Get User | `supabase.auth.getUser(token)` |

### Protected Route Pattern

```typescript
const { user, loading } = useAuthContext();

useEffect(() => {
  if (!loading && !user) navigate('/login');
}, [loading, user]);
```

## Critical Rules

1. **ALL auth operations use Supabase SDK on frontend** - No backend auth routes
2. **Always use AuthContext** - Single source of truth for user state  
3. **Backend only verifies JWT** - Use `requireAuth` middleware only
4. **API client must be async** - Interceptor needs `await supabase.auth.getSession()`
5. **Disable queries when not authenticated** - Use `enabled: !!user` to prevent 401 loops
6. **Verify Prisma User schema** - Ensure all fields match existing queries

## Detailed Implementation

For complete code examples and troubleshooting, see:
- **[references/supabase-auth.md](references/supabase-auth.md)** - Full implementation guide with all code samples
