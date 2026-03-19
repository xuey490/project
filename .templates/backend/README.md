# Backend API Template

Modern REST API template built with Express.js, TypeScript, and PostgreSQL.

## Tech Stack

- **Runtime**: Node.js
- **Framework**: Express.js 4.21+
- **Language**: TypeScript 5.9+
- **Database**: PostgreSQL
- **ORM**: Prisma 6.1+
- **Validation**: Zod
- **Testing**: Jest + Supertest

## Project Structure

```
backend/
├── prisma/
│   └── schema.prisma          # Database schema
├── src/
│   ├── __tests__/             # Test files
│   ├── config/                # Configuration
│   │   ├── database.ts        # Prisma client
│   │   ├── env.ts             # Environment validation
│   │   └── logger.ts          # Pino logger setup
│   ├── middleware/            # Express middleware
│   │   ├── errorHandler.ts   # Error handling
│   │   ├── logger.ts          # HTTP logging
│   │   └── validation.ts     # Zod validation
│   ├── modules/               # Feature modules (routes + handlers)
│   │   └── system.ts          # System & health checks
│   ├── types/                 # TypeScript types & Zod schemas
│   ├── app.ts                 # Express app setup
│   └── index.ts               # Server entry point
├── .env.example               # Environment template
├── package.json
└── tsconfig.json
```

## Getting Started

### Prerequisites

- Node.js 18+
- PostgreSQL 14+
- npm or yarn

### Installation

1. Install dependencies:
```bash
cd backend
npm install
```

2. Set up environment variables:
```bash
cp .env.example .env
# Edit .env with your database credentials
```

3. Generate Prisma client:
```bash
npm run prisma:generate
```

4. Run database migrations:
```bash
npm run prisma:migrate
```

### Development

Start the development server with hot reload:
```bash
npm run dev
```

Server will start at `http://localhost:3000`

### Testing

Run tests:
```bash
npm test
```

Run tests in watch mode:
```bash
npm run test:watch
```

### Production Build

Build the project:
```bash
npm run build
```

Start production server:
```bash
npm start
```

## API Endpoints

### System Routes

- `GET /api/v1/` - API welcome message
- `GET /api/v1/health` - Basic health check
- `GET /api/v1/health/ready` - Readiness check (includes database connection)
- `GET /api/v1/health/live` - Liveness check
- `GET /api/v1/version` - API version information
- `GET /api/v1/ping` - Simple ping endpoint
- `GET /api/v1/status` - System status (uptime, memory, etc.)

### Your Domain Routes

Add your business logic as new modules in `src/modules/`. Each module combines routes and handlers in a single file for simplicity. See `src/modules/README.md` for detailed examples and best practices.

#### Quick Example

Create a new module `src/modules/user.ts`:

```typescript
import { Router } from 'express'
import { prisma } from '../config/database.js'

export const userRouter = Router()

userRouter.get('/', async (_req, res) => {
  const users = await prisma.user.findMany()
  res.json(users)
})

userRouter.post('/', async (req, res) => {
  const user = await prisma.user.create({ data: req.body })
  res.status(201).json(user)
})
```

Register it in `src/app.ts`:

```typescript
import { userRouter } from './modules/user.js'
app.use(`${env.API_PREFIX}/users`, userRouter)
```

## API Examples

### Health Check
```bash
curl http://localhost:3000/api/v1/health
```

### Readiness Check
```bash
curl http://localhost:3000/api/v1/health/ready
```

### System Status
```bash
curl http://localhost:3000/api/v1/status
```

## Database Management

### Prisma Studio
Open Prisma Studio to view and edit data:
```bash
npm run prisma:studio
```

### Create Migration
```bash
npx prisma migrate dev --name migration_name
```

### Reset Database
```bash
npx prisma migrate reset
```

## Security Features

- **CORS**: Configured cross-origin resource sharing
- **Input Validation**: Zod schema validation
- **Error Handling**: Centralized error management

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `NODE_ENV` | Environment mode | `development` |
| `PORT` | Server port | `3000` |
| `API_PREFIX` | API route prefix | `/api/v1` |
| `DATABASE_URL` | PostgreSQL connection string | - |
| `CORS_ORIGIN` | Allowed CORS origin (URL or `*` for all) | `*` |

**Note on CORS:** Default is `*` (allow all origins). This disables credentials (cookies, authorization headers). For production, specify exact origins.

## Performance Optimizations

- Connection pooling for database
- Response compression
- Efficient database indexing
- Pagination for large datasets

## Error Handling

The API uses consistent error responses:

```json
{
  "status": "error",
  "message": "Error description",
  "errors": [] // Optional validation errors
}
```

HTTP Status Codes:
- `200` - Success
- `201` - Created
- `204` - No Content
- `400` - Bad Request
- `404` - Not Found
- `409` - Conflict
- `500` - Internal Server Error

## License

MIT
