# Architecture Reference

## Backend Architecture

### Module Pattern

Routes + handlers in single file, each exports Router:

```typescript
// src/modules/user.ts
import { Router } from 'express';
import { prisma } from '../config/database';
import { validate } from '../middleware/validation';
import { listUsersSchema } from '../types/user.types';

const router = Router();

router.post('/list', validate(listUsersSchema), async (req, res) => {
  const { page, limit, filters } = req.body;
  // Implementation
});

export default router;
```

### Key Backend Files

| File | Purpose |
|------|---------|
| `src/index.ts` | Server entry, graceful shutdown |
| `src/app.ts` | Middleware setup, route mounting |
| `src/config/env.ts` | Zod-validated environment |
| `src/config/database.ts` | Prisma singleton |
| `src/config/logger.ts` | Pino logger factory |
| `src/middleware/error-handler.ts` | AppError class, global handler |
| `src/middleware/validation.ts` | Generic Zod middleware |

### Commands

```bash
npm run prisma:migrate -- --name init    # Migration (MUST specify --name)
npm run test                             # Run tests
```

## Frontend Architecture

### Key Files

| File | Purpose |
|------|---------|
| `index.html` | HTML entry, `<div id="root">` |
| `src/main.tsx` | ReactDOM.createRoot |
| `src/App.tsx` | Providers + React Router |
| `src/index.css` | Tailwind + CSS variables |
| `vite.config.ts` | Proxy: `/api` → `localhost:3000` |
| `src/lib/api-client.ts` | Axios with auth interceptor |
| `src/lib/utils.ts` | cn() for className merging |

### Provider Order (DO NOT CHANGE)

```tsx
<QueryClientProvider>
  <TooltipProvider>
    <Toaster />
    <BrowserRouter>{/* routes */}</BrowserRouter>
  </TooltipProvider>
</QueryClientProvider>
```

### shadcn/ui Components

53 components in `src/components/ui/`. DO NOT create custom versions.

Key variants:
- Button: `default` | `destructive` | `outline` | `secondary` | `ghost` | `link`
- Sizes: `default` | `sm` | `lg` | `icon`

### TanStack Query

Defaults: `staleTime: 60s`, `gcTime: 5m`, `retry: 1`

### API Client

- Base URL `/api` (proxied via Vite)
- Auto-adds Bearer token from `localStorage.getItem('auth_token')`
- 401: clears token, 403/500: logs error

### Toast

```typescript
import { toast } from "sonner";
toast.success('Done');
toast.error('Failed');
```

### Hooks

- `useIsMobile()` - Mobile detection (< 768px)
- Custom hooks in `src/hooks/use-[name].ts`

## System API Routes

**GET (No Parameters):**
- `/api/` - Welcome
- `/api/health` - Health check
- `/api/health/ready` - Readiness (includes DB)
- `/api/health/live` - Liveness
- `/api/version` - Version info
- `/api/status` - System status

**POST (JSON Body):**
- `/api/[resource]/list` - List with pagination
- `/api/[resource]/search` - Complex search
- `/api/[resource]` - Create
