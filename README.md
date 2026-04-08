# IP Management — Setup Guide

This project consists of four services:

- **API Gateway** — Node.js/Express reverse proxy
- **Auth Service** — Laravel app handling authentication & user audit logs
- **IP Management Service** — Laravel app handling IP address CRUD & audit logs
- **Frontend** — Vue 3 / Vuetify SPA

---

## Quick Start (Docker)

If you have Docker installed and want to get the entire stack up and running immediately without manual configuration:

```bash
docker compose -f docker-compose.local.yml up -d
```

This will spin up all required services (API Gateway, Auth Service, IP Management Service, database, and Redis) in the background.

Once the containers are healthy, you can access the application at:

- Frontend: http://localhost:5173
- API Gateway: http://localhost:3000

### Some Test Credentials

You can log in using the following test accounts:

| Role        | Email            | Password |
| ----------- | ---------------- | -------- |
| Super Admin | john@example.com | password |
| User        | jane@example.com | password |
| User        | tim@example.com  | password |
| User        | lin@example.com  | password |

---

## 1. API Gateway Setup

```bash
cd services/api-gateway
npm install
```

### Environment Variables

Copy or create a `.env` file in `services/api-gateway/`:

```env
# Port the gateway listens on
PORT=3000

# Downstream service URLs
AUTH_SERVICE_URL=http://localhost:8000
IP_MANAGEMENT_URL=http://localhost:8001

# Optional: override the default proxy timeout (milliseconds)
# DEFAULT_TIMEOUT=30000
```

### Route Mapping

| Gateway Path              | Forwards To                                 |
| ------------------------- | ------------------------------------------- |
| `GET /health`             | Gateway health check (no proxy)             |
| `/api/v1/auth/*`          | Auth Service (`AUTH_SERVICE_URL`)           |
| `/api/v1/ip-management/*` | IP Management Service (`IP_MANAGEMENT_URL`) |

The path is **preserved** when proxied — e.g., a request to `GET /api/v1/auth/login` arrives at the Auth Service as `GET /api/v1/auth/login`.

### Running the Gateway

```bash
npm run dev
```

---

## 2. Auth Service Setup

```bash
cd services/auth

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate Laravel application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database (creates roles, and test users — see DatabaseSeeder.php)
php artisan db:seed
```

### Running the Auth Service

```bash
php artisan serve --port=8000
```

---

## 3. IP Management Service Setup

```bash
cd services/ip-management

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Seed with 100 sample IP address records
php artisan db:seed
```

### Running the IP Management Service

```bash
php artisan serve --port=8001
```

---

## 4. Frontend Setup

```bash
cd services/frontend
npm install
```

### Environment Variables

Copy or create a `.env` file in `services/frontend/`:

```env
VITE_BACKEND_URL=http://localhost:3000/api/v1
```

### Running the Frontend

```bash
npm run dev
```

The frontend is a Vue 3 + Vuetify SPA. It communicates exclusively with the API Gateway. Features include:

- Login / logout
- IP address management (create, edit, delete)
- Audit log views for users and IP addresses (super-admin only)

---

## 5. JWT Configuration (`tymon/jwt-auth`)

Both Laravel services use `tymon/jwt-auth`. The Auth Service **signs** tokens; the IP Management Service **verifies** them only.

### Option A — HS256 (Quick Start, Recommended for Development)

HS256 uses a single shared secret across both services.

**In the Auth Service:**

```bash
cd services/auth
php artisan jwt:secret
```

This writes `JWT_SECRET=<generated_key>` into `services/auth/.env`.

**Copy that exact value** into `services/ip-management/.env`:

```env
JWT_ALGO=HS256
JWT_SECRET=<paste_the_same_secret_here>
```

---

### Option B — RS256 (Asymmetric Keys, Recommended for Production)

#### Step 1 — Generate the key pair

```bash
openssl genrsa -out jwt-private.pem 4096
openssl rsa -in jwt-private.pem -pubout -out jwt-public.pem
```

#### Step 2 — Place the keys

The Auth Service needs both keys; the IP Management Service needs the public key only.

```
services/auth/storage/keys/
├── jwt-private.pem
└── jwt-public.pem

services/ip-management/storage/keys/
└── jwt-public.pem
```

#### Step 3 — Configure `.env` for both services

**Auth Service** (`services/auth/.env`):

```env
JWT_ALGO=RS256
JWT_PRIVATE_KEY=file:///absolute/path/to/services/auth/storage/keys/jwt-private.pem
JWT_PUBLIC_KEY=file:///absolute/path/to/services/auth/storage/keys/jwt-public.pem
JWT_TTL=15
JWT_REFRESH_TTL=10080
```

**IP Management Service** (`services/ip-management/.env`):

```env
JWT_ALGO=RS256
JWT_PRIVATE_KEY=1
JWT_PUBLIC_KEY=file:///absolute/path/to/services/ip-management/storage/keys/jwt-public.pem
```

> **Note:** `JWT_PRIVATE_KEY` cannot be null due to a `tymon/jwt-auth` requirement. Setting it to `1` is an accepted workaround for the IP Management Service, which only verifies tokens.

---

## 6. Database Setup

Both Laravel services use **MariaDB** and **Redis** (for caching).

If you prefer your own database instances, update the `DB_*` and `REDIS_*` variables in each service's `.env` accordingly.

Then run migrations for each service:

```bash
cd services/auth && php artisan migrate
cd services/ip-management && php artisan migrate
```
---

## 7. Running All Services Together

```bash
# Terminal 1 — API Gateway
cd services/api-gateway && npm run dev

# Terminal 2 — Auth Service
cd services/auth && php artisan serve --port=8000

# Terminal 3 — IP Management Service
cd services/ip-management && php artisan serve --port=8001

# Terminal 4 — Frontend
cd services/frontend && npm run dev
```

---

## 8. Running Tests

Tests for both Laravel services use an in-memory SQLite database by default (configured in `phpunit.xml`) — no extra database setup required.

### Auth Service (PHPUnit)

```bash
cd services/auth

# Run all tests
composer test

# Or directly via Artisan
php artisan test

# Run a specific test class
php artisan test --filter LoginTest
```

### IP Management Service (PHPUnit)

```bash
cd services/ip-management

# Run all tests
composer test

# Or directly via Artisan
php artisan test

# Run a specific test class
php artisan test --filter IndexTest
```
