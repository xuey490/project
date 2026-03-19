# JSON-Based API Protocol Standard

All frontend-backend communication MUST use JSON-based protocols exclusively. URL parameters are PROHIBITED for data operations.

## Important: API Client Configuration

The `apiClient` in `frontend/src/lib/api-client.ts` has `baseURL: '/api'` configured. 

**When calling APIs from frontend, DO NOT include `/api` prefix** - it's automatically added.

```typescript
// ❌ WRONG - Double /api prefix results in /api/api/users
apiClient.post('/api/users/list', ...)

// ✅ CORRECT - Results in /api/users/list
apiClient.post('/users/list', ...)
```

## Protocol Requirements

### Backend API Design
- **POST, PUT, PATCH, DELETE** for data manipulation
- **GET** only for simple resource retrieval (no query parameters)
- **ALL request data** in JSON format in request body
- **ALL response data** in JSON format

### URL Parameter Restrictions

```typescript
// ❌ PROHIBITED
GET /api/users?page=1&limit=10&published=true
GET /api/products?category=electronics&sort=price

// ✅ REQUIRED
POST /api/users/list    // with JSON body
POST /api/products/search  // with JSON body
```

## Backend Implementation

### Route Definition Pattern

```typescript
// ❌ WRONG - URL parameters
router.get('/users', (req, res) => {
  const { page, limit, published } = req.query;
});

// ✅ CORRECT - JSON body
router.post('/users/list', validate(listUsersSchema), (req, res) => {
  const { page, limit, filters } = req.body;
});
```

Note: Backend routes are mounted under `/api` prefix in `app.ts`, so route handlers use paths without `/api`.

### Zod Schema Pattern

```typescript
export const listUsersSchema = z.object({
  body: z.object({
    page: z.number().int().positive().default(1),
    limit: z.number().int().positive().max(100).default(10),
    filters: z.object({
      published: z.boolean().optional(),
      category: z.string().optional(),
    }).optional(),
    sort: z.object({
      field: z.string(),
      direction: z.enum(['asc', 'desc'])
    }).optional(),
  }),
});
```

### Complete Route Handler Example

```typescript
router.post('/users/list', validate(listUsersSchema), async (req, res) => {
  const { page, limit, filters, sort } = req.body;
  
  const skip = (page - 1) * limit;
  
  const users = await prisma.user.findMany({
    skip,
    take: limit,
    where: filters ? { ...filters } : {},
    orderBy: sort ? { [sort.field]: sort.direction } : { createdAt: 'desc' }
  });
  
  res.json({ users, pagination: { page, limit, total: users.length } });
});
```

## Frontend Implementation

### API Call Pattern

```typescript
// ❌ WRONG - URL parameters
const response = await apiClient.get('/users', {
  params: { page: 1, limit: 10, published: true }
});

// ❌ WRONG - Including /api prefix (already in baseURL)
const response = await apiClient.post('/api/users/list', { ... });

// ✅ CORRECT - JSON body, no /api prefix
const response = await apiClient.post('/users/list', {
  page: 1,
  limit: 10,
  filters: { published: true }
});
```

## Route Design Patterns

### Resource Operations

```typescript
// Backend route handlers (without /api prefix - mounted in app.ts)
router.post('/users/list', ...);      // List with pagination/filters
router.post('/users/search', ...);    // Complex search operations
router.post('/users', ...);           // Create new user
router.put('/users/:id', ...);        // Full update
router.patch('/users/:id', ...);      // Partial update
router.delete('/users/:id', ...);     // Delete user

// Simple GET operations (no parameters)
router.get('/users/:id', ...);        // Get single user by ID
router.get('/users/count', ...);      // Get user count
```

### Frontend API Calls

```typescript
// Corresponding frontend calls (without /api prefix)
await apiClient.post('/users/list', { page: 1, limit: 10 });
await apiClient.post('/users/search', { query: 'john' });
await apiClient.post('/users', { name: 'John', email: 'john@example.com' });
await apiClient.put('/users/123', { name: 'John Doe' });
await apiClient.patch('/users/123', { status: 'active' });
await apiClient.delete('/users/123');
await apiClient.get('/users/123');
await apiClient.get('/users/count');
```

## Benefits

- **Type Safety**: Full TypeScript validation with Zod schemas
- **Consistency**: Uniform request/response format
- **Security**: No URL encoding issues or injection vulnerabilities
- **Flexibility**: Complex nested structures easily supported
- **Debugging**: Complete request/response visibility in logs
