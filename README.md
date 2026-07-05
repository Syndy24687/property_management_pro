# Property Management Pro

A comprehensive multi-tenant property management system built with **Laravel 12**, featuring JWT authentication, role-based access control, automated invoicing, utility billing, and maintenance workflow management.

## Architecture

```
┌───────────────────────────────────────────────────┐
│                    API Layer                       │
│   Routes → Middleware → Controllers → Resources   │
├───────────────────────────────────────────────────┤
│                  Service Layer                     │
│         Business Logic & Orchestration             │
├───────────────────────────────────────────────────┤
│                Repository Layer                    │
│           Data Access & Eloquent ORM               │
├───────────────────────────────────────────────────┤
│                 Database Layer                     │
│              MySQL (32+ Tables)                    │
└───────────────────────────────────────────────────┘
```

**Design Pattern:** 3-Tier Architecture with Repository Pattern
- **Controllers** — Handle HTTP requests, validation, response formatting
- **Services** — Business logic, orchestration, cross-cutting concerns
- **Repositories** — Data access abstraction, Eloquent queries

## Prerequisites

| Requirement | Version |
|-------------|---------|
| PHP | 8.2+ |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x |
| XAMPP (or equivalent) | Latest |

**Required PHP Extensions:** `zip`, `pdo_mysql`, `mbstring`, `openssl`, `gd` (for image processing)

## Installation

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/your-username/property_management_pro.git
cd property_management_pro
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=property_management_pro
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=  # Will be generated in step 3
```

### 3. Generate Keys

```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. Database Setup

Create the database in MySQL:
```sql
CREATE DATABASE property_management_pro;
```

Run migrations and seed:
```bash
php artisan migrate:fresh --seed
```

### 5. Storage Link (for image uploads)

```bash
php artisan storage:link
```

### 6. Start the Server

```bash
php artisan serve
```

The API is now available at `http://localhost:8000/api/v1/`

---

## Authentication (JWT)

All protected endpoints require a JWT token in the `Authorization` header:

```
Authorization: Bearer <your-jwt-token>
```

### Login Flow

```bash
# 1. Login to get a token
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "admin@propertymanagement.test",
    "password": "password"
}

# Response includes:
{
    "data": {
        "authorization": {
            "token": "eyJ0eXAiOiJKV1Qi...",
            "type": "Bearer",
            "expires_in": 3600
        }
    }
}
```

### Token Refresh

When your token expires, refresh it:

```bash
POST /api/v1/auth/refresh
Authorization: Bearer <expired-token>
```

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@propertymanagement.test | password |
| Admin | admin@propertymanagement.test | password |
| Manager | manager@propertymanagement.test | password |
| Owner | owner@propertymanagement.test | password |
| Tenant | tenant@propertymanagement.test | password |

---

## Role-Based Access Control (RBAC)

| Role | Description | Key Access |
|------|-------------|------------|
| **super-admin** | System administrator | Full access (bypasses all checks) |
| **admin** | Company administrator | All operations |
| **owner** | Property owner | CRUD own properties, units, leases, invoices |
| **manager** | Property manager | Manage assigned properties, billing, maintenance |
| **tenant** | Lease holder | View own data, create payments & maintenance |
| **occupant** | Non-primary resident | View-only, create maintenance requests |

---

## API Endpoints

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/auth/register` | ✗ | Register new user |
| POST | `/api/v1/auth/login` | ✗ | Login, get JWT |
| POST | `/api/v1/auth/logout` | ✓ | Invalidate token |
| POST | `/api/v1/auth/refresh` | ✓ | Refresh JWT |
| GET | `/api/v1/auth/me` | ✓ | Get profile |
| PUT | `/api/v1/auth/profile` | ✓ | Update profile |
| PUT | `/api/v1/auth/change-password` | ✓ | Change password |

### System Settings

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/settings` | Admin+ | Get all settings (grouped) |
| GET | `/api/v1/settings/{group}` | Admin+ | Get settings by group |
| PUT | `/api/v1/settings` | Admin+ | Bulk update settings |

**Settings body example:**
```json
{
    "settings": {
        "currency": "EUR",
        "currency_symbol": "€",
        "tax_rate": 7.5,
        "language": "fr"
    }
}
```

### Properties

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/properties` | List properties (filter: status, type, city, state, search) |
| POST | `/api/v1/properties` | Create property (supports `images[]` multipart upload) |
| GET | `/api/v1/properties/{id}` | Get property details with images, units, managers |
| PUT | `/api/v1/properties/{id}` | Update property |
| DELETE | `/api/v1/properties/{id}` | Soft delete property |
| GET | `/api/v1/properties/{id}/units` | List units for property |
| POST | `/api/v1/properties/{id}/images` | Upload images (multipart: `images[]`) |
| DELETE | `/api/v1/properties/{id}/images/{imageId}` | Delete an image |

### Units

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/units` | List units (filter: status, property_id, bedrooms) |
| POST | `/api/v1/units` | Create unit (supports `images[]` upload) |
| GET | `/api/v1/units/{id}` | Get unit details |
| PUT | `/api/v1/units/{id}` | Update unit |
| DELETE | `/api/v1/units/{id}` | Soft delete unit |
| POST | `/api/v1/units/{id}/images` | Upload images |
| DELETE | `/api/v1/units/{id}/images/{imageId}` | Delete an image |

### Leases

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/leases` | List leases (filter: status, tenant_id, unit_id) |
| POST | `/api/v1/leases` | Create lease (auto-generates deposit invoice) |
| GET | `/api/v1/leases/{id}` | Get lease with co-tenants, invoices, documents |
| PUT | `/api/v1/leases/{id}` | Update lease |
| POST | `/api/v1/leases/{id}/renew` | Renew lease (body: start_date, end_date, rent_amount) |
| POST | `/api/v1/leases/{id}/terminate` | Early termination (body: reason) |

### Invoices

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/invoices` | List invoices (filter: status, lease_id, date range) |
| POST | `/api/v1/invoices` | Create manual invoice with line items |
| GET | `/api/v1/invoices/{id}` | Get invoice with items and payments |
| PUT | `/api/v1/invoices/{id}` | Update invoice (notes, status) |
| POST | `/api/v1/invoices/{id}/send` | Mark as sent |
| POST | `/api/v1/invoices/{id}/void` | Void invoice |

**Manual invoice creation body:**
```json
{
    "lease_id": 1,
    "due_date": "2026-08-05",
    "items": [
        {
            "type": "rent",
            "description": "Monthly Rent — August 2026",
            "unit_price": 2200.00
        },
        {
            "type": "other",
            "description": "Parking Space Fee",
            "quantity": 1,
            "unit_price": 150.00
        }
    ]
}
```

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/payments` | List payments (filter: status, lease_id, invoice_id) |
| POST | `/api/v1/payments` | Record payment (auto-updates invoice balance if invoice_id provided) |
| GET | `/api/v1/payments/{id}` | Get payment details |

### Utility Billing

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/utility-types` | List utility types (tariffs) |
| POST | `/api/v1/utility-types` | Create utility type |
| PUT | `/api/v1/utility-types/{id}` | Update tariff rate |
| GET | `/api/v1/units/{id}/meters` | List meters for a unit |
| POST | `/api/v1/units/{id}/meters` | Install a meter |
| PUT | `/api/v1/meters/{id}` | Update meter |
| DELETE | `/api/v1/meters/{id}` | Decommission meter |
| GET | `/api/v1/meters/{id}/readings` | Reading history |
| POST | `/api/v1/meters/{id}/readings` | Record reading (auto-calculates consumption & generates charge) |
| GET | `/api/v1/utility-charges` | List utility charges |

**Recording a meter reading:**
```json
POST /api/v1/meters/1/readings
{
    "reading_value": 5800.00,
    "reading_date": "2026-07-01"
}

// Response includes auto-calculated consumption and generated charge
```

### Maintenance

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/maintenance-requests` | List requests (filter: status, priority, assigned_to) |
| POST | `/api/v1/maintenance-requests` | Create request (supports `attachments[]` upload) |
| GET | `/api/v1/maintenance-requests/{id}` | Get with comments, attachments, assignee |
| PUT | `/api/v1/maintenance-requests/{id}` | Update status, priority, cost |
| POST | `/api/v1/maintenance-requests/{id}/assign` | Assign to staff (sends notification) |
| GET | `/api/v1/maintenance-requests/{id}/comments` | List comments (internal filtered for tenants) |
| POST | `/api/v1/maintenance-requests/{id}/comments` | Add comment |
| GET | `/api/v1/maintenance-requests/{id}/attachments` | List attachments |
| POST | `/api/v1/maintenance-requests/{id}/attachments` | Upload attachments |

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan invoices:generate` | Generate monthly invoices for all active leases |
| `php artisan invoices:generate --month=2026-08` | Generate for a specific month |
| `php artisan invoices:mark-overdue` | Mark overdue invoices and apply late fees |
| `php artisan route:list` | View all registered API routes |

### Scheduling (Production)

Add to your server's crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `JWT_SECRET` | JWT signing secret (auto-generated) | — |
| `JWT_TTL` | Token lifetime in minutes | 60 |
| `JWT_REFRESH_TTL` | Refresh token lifetime in minutes | 20160 (2 weeks) |
| `DB_DATABASE` | MySQL database name | property_management_pro |
| `FILESYSTEM_DISK` | Storage disk for uploads | public |

---

## Project Structure

```
property_management_pro/
├── app/
│   ├── Console/Commands/        # Artisan commands (invoice generation)
│   ├── Http/
│   │   ├── Controllers/Api/V1/  # API controllers
│   │   ├── Middleware/           # JWT auth, role checking
│   │   ├── Requests/            # Form request validation
│   │   └── Resources/           # API resource transformers
│   ├── Models/                  # Eloquent models (18 models)
│   ├── Notifications/           # Database notifications
│   ├── Repositories/            # Repository pattern (interfaces + eloquent)
│   └── Services/                # Business logic layer
├── config/
│   ├── auth.php                 # JWT guard configuration
│   └── jwt.php                  # JWT settings (published by package)
├── database/
│   ├── migrations/              # 27 migration files
│   └── seeders/                 # Demo data, roles, system settings
├── routes/
│   └── api.php                  # All API routes
└── storage/app/public/images/   # Uploaded images
```

---

## Testing with Postman

### Setup

1. Create a new Postman environment with variable `base_url` = `http://localhost:8000/api/v1`
2. Create a variable `token` (leave empty initially)

### Authentication Workflow

1. **Login:** Send `POST {{base_url}}/auth/login` with credentials
2. **Save Token:** In the Tests tab of the login request, add:
   ```javascript
   var response = pm.response.json();
   pm.environment.set("token", response.data.authorization.token);
   ```
3. **Use Token:** In all other requests, set the Authorization header:
   ```
   Authorization: Bearer {{token}}
   ```

### Quick Test Sequence

1. `POST /auth/login` → Get token
2. `GET /auth/me` → Verify auth works
3. `GET /properties` → List properties
4. `GET /properties/1` → Get property detail with images
5. `POST /maintenance-requests` → Create a maintenance request
6. `GET /settings` → View system settings
7. `GET /invoices` → View invoices

---

## License

MIT
