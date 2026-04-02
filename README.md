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

# Seed the database (for email:test@example.com and password:password and other sample records)
php artisan db:seed
```

### Running the Auth Service

```bash
php artisan serve --port=8000
```

---

## 3. JWT Configuration (`tymon/jwt-auth`)

### Option A — HS256 (Quick Start, Recommended for Development)

HS256 uses a single secret key shared across both services. This is the fastest way to get running.

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

Both services will sign and verify tokens with the same key.

---

### Option B — RS256 (Asymmetric Keys)

#### Step 1 — Generate the key pair

```bash
# Generate a 4096-bit RSA private key
openssl genrsa -out jwt-private.pem 4096

# Derive the public key from it
openssl rsa -in jwt-private.pem -pubout -out jwt-public.pem
```

#### Step 2 — Store the keys

Place both files in the Auth Service's designated keys directory:

```
services/auth/storage/keys/
├── jwt-private.pem
└── jwt-public.pem
```

The IP Management Service only needs the **public key** to verify tokens, so copy it there as well:

```
services/ip-management/storage/keys/
└── jwt-public.pem
```

#### Step 3 — Configure `.env` for both services

**Auth Service** (`services/auth/.env`):

```env
JWT_ALGO=RS256
JWT_PRIVATE_KEY=file:///absolute/path/to/services/auth/storage/keys/jwt-private.pem
JWT_PUBLIC_KEY=file:///absolute/path/to/services/auth/storage/keys/jwt-public.pem
```

**IP Management Service** (`services/ip-management/.env`):

```env
JWT_ALGO=RS256
JWT_PUBLIC_KEY=file:///absolute/path/to/services/ip-management/storage/keys/jwt-public.pem
```

---

## 5. Database Setup

Run `docker compose up` to create the database, else you can creaste your own and adjust each service's `.env` file.

Then run migrations per service:

```bash
cd services/auth && php artisan migrate
cd services/ip-management && php artisan migrate
```

---

## 6. Running All Services Together

```bash
# Terminal 1 — API Gateway
cd services/api-gateway && npm run dev

# Terminal 2 — Auth Service
cd services/auth && php artisan serve --port=8000

# Terminal 3 — IP Management Service
cd services/ip-management && php artisan serve --port=8001
```

The frontend (when added) will communicate exclusively with the API Gateway on `http://localhost:3000`.

---

## 7. Running Tests

### Auth Service (PHPUnit)

```bash
cd services/auth

# Run all tests
composer test

# Or directly via PHPUnit
php artisan test

# Run a specific test class
php artisan test --filter LoginTest
```

Tests use an in-memory SQLite database by default (configured in `phpunit.xml`) so no additional database setup is needed for testing.

---

TODO: revisit doc once dockerized
