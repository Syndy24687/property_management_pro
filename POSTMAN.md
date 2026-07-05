# Postman Testing Guide — Property Management Pro API

A complete guide for testing every API endpoint using Postman.

---

## 1. Initial Setup

### Import Base Configuration

1. Open Postman
2. Click **Environments** → **Create Environment**
3. Add these variables:

| Variable | Initial Value | Current Value |
|----------|--------------|---------------|
| `base_url` | `http://localhost:8000/api/v1` | `http://localhost:8000/api/v1` |
| `token` | *(leave empty)* | *(auto-filled on login)* |

4. Select this environment in the top-right dropdown

### Set Up Authorization

For **every** request folder (except Auth/Login and Auth/Register):

1. Go to the folder's **Authorization** tab
2. Set **Type** = `Bearer Token`
3. Set **Token** = `{{token}}`

This ensures all requests within the folder automatically send the JWT.

---

## 2. Authentication Workflow

### 2.1 — Register (Optional)

```
POST {{base_url}}/auth/register
```

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

### 2.2 — Login ⭐ (Do this first)

```
POST {{base_url}}/auth/login
```

**Body (JSON):**
```json
{
    "email": "admin@propertymanagement.test",
    "password": "password"
}
```

**Auto-save token:** Go to the request's **Scripts → Post-response** tab and paste:
```javascript
if (pm.response.code === 200) {
    var response = pm.response.json();
    pm.environment.set("token", response.data.authorization.token);
    console.log("Token saved successfully!");
}
```

**Available demo accounts:**

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@propertymanagement.test` | `password` |
| Admin | `admin@propertymanagement.test` | `password` |
| Manager | `manager@propertymanagement.test` | `password` |
| Owner | `owner@propertymanagement.test` | `password` |
| Tenant | `tenant@propertymanagement.test` | `password` |

### 2.3 — Get Profile

```
GET {{base_url}}/auth/me
Authorization: Bearer {{token}}
```

### 2.4 — Update Profile

```
PUT {{base_url}}/auth/profile
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Updated Name",
    "phone": "+1987654321",
    "emergency_contact_name": "Jane Doe",
    "emergency_contact_phone": "+1122334455"
}
```

### 2.5 — Change Password

```
PUT {{base_url}}/auth/change-password
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "current_password": "password",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

> ⚠️ After changing the password, a new token is returned. Update `{{token}}` accordingly.

### 2.6 — Refresh Token

```
POST {{base_url}}/auth/refresh
Authorization: Bearer {{token}}
```

### 2.7 — Logout

```
POST {{base_url}}/auth/logout
Authorization: Bearer {{token}}
```

---

## 3. System Settings (Admin+ Only)

### 3.1 — Get All Settings

```
GET {{base_url}}/settings
Authorization: Bearer {{token}}
```

**Response shows settings grouped by:** `general`, `billing`, `localization`, `notifications`

### 3.2 — Get Settings by Group

```
GET {{base_url}}/settings/billing
Authorization: Bearer {{token}}
```

### 3.3 — Update Settings

```
PUT {{base_url}}/settings
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "settings": {
        "currency": "NGN",
        "currency_symbol": "₦",
        "tax_rate": 7.5,
        "late_fee_percentage": 10,
        "grace_period_days": 7,
        "invoice_prefix": "PMP",
        "language": "en",
        "date_format": "d/m/Y"
    }
}
```

---

## 4. Properties

### 4.1 — List Properties

```
GET {{base_url}}/properties
Authorization: Bearer {{token}}
```

**Optional Query Params:**
- `?status=active`
- `?type=residential`
- `?city=Lagos`
- `?search=sunset`
- `?per_page=10`

### 4.2 — Create Property

```
POST {{base_url}}/properties
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Sunset Apartments",
    "address": "123 Main Street",
    "city": "Lagos",
    "state": "Lagos",
    "zip_code": "100001",
    "type": "residential",
    "description": "Luxury apartment complex with modern amenities",
    "status": "active",
    "year_built": 2020
}
```

### 4.3 — Create Property with Images

```
POST {{base_url}}/properties
Authorization: Bearer {{token}}
```

**Body (form-data — NOT JSON):**

| Key | Value | Type |
|-----|-------|------|
| `name` | Sunset Apartments | Text |
| `address` | 123 Main Street | Text |
| `city` | Lagos | Text |
| `state` | Lagos | Text |
| `zip_code` | 100001 | Text |
| `type` | residential | Text |
| `images[0]` | *(select file)* | File |
| `images[1]` | *(select file)* | File |

### 4.4 — Get Property Details

```
GET {{base_url}}/properties/1
Authorization: Bearer {{token}}
```

### 4.5 — Update Property

```
PUT {{base_url}}/properties/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Sunset Apartments - Renovated",
    "status": "active"
}
```

### 4.6 — Delete Property

```
DELETE {{base_url}}/properties/1
Authorization: Bearer {{token}}
```

### 4.7 — Get Property Units

```
GET {{base_url}}/properties/1/units
Authorization: Bearer {{token}}
```

### 4.8 — Upload Property Images

```
POST {{base_url}}/properties/1/images
Authorization: Bearer {{token}}
```

**Body (form-data):**

| Key | Value | Type |
|-----|-------|------|
| `images[0]` | *(select image file)* | File |
| `images[1]` | *(select image file)* | File |

### 4.9 — Delete Property Image

```
DELETE {{base_url}}/properties/1/images/3
Authorization: Bearer {{token}}
```

---

## 5. Units

### 5.1 — List Units

```
GET {{base_url}}/units
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?property_id=1`, `?status=available`, `?bedrooms=2`

### 5.2 — Create Unit

```
POST {{base_url}}/units
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "property_id": 1,
    "unit_number": "A-101",
    "floor": 1,
    "bedrooms": 2,
    "bathrooms": 1,
    "area_sqft": 850,
    "rent_amount": 1500.00,
    "deposit_amount": 3000.00,
    "status": "available"
}
```

### 5.3 — Get Unit Details

```
GET {{base_url}}/units/1
Authorization: Bearer {{token}}
```

### 5.4 — Update Unit

```
PUT {{base_url}}/units/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "rent_amount": 1650.00,
    "status": "available"
}
```

### 5.5 — Upload Unit Images

```
POST {{base_url}}/units/1/images
Authorization: Bearer {{token}}
```

**Body (form-data):**

| Key | Value | Type |
|-----|-------|------|
| `images[0]` | *(select image file)* | File |

### 5.6 — Delete Unit Image

```
DELETE {{base_url}}/units/1/images/5
Authorization: Bearer {{token}}
```

---

## 6. Leases

### 6.1 — List Leases

```
GET {{base_url}}/leases
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?status=active`, `?tenant_id=4`, `?unit_id=1`

### 6.2 — Create Lease

```
POST {{base_url}}/leases
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "unit_id": 1,
    "tenant_id": 4,
    "start_date": "2026-08-01",
    "end_date": "2027-07-31",
    "rent_amount": 1500.00,
    "deposit_amount": 3000.00,
    "payment_frequency": "monthly",
    "payment_day_of_month": 1,
    "late_fee_amount": 75.00,
    "grace_period_days": 5,
    "status": "active"
}
```

> 💡 Creating a lease with `deposit_amount > 0` will automatically generate a deposit invoice.

### 6.3 — Get Lease Details

```
GET {{base_url}}/leases/1
Authorization: Bearer {{token}}
```

### 6.4 — Update Lease

```
PUT {{base_url}}/leases/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "rent_amount": 1650.00,
    "notes": "Rent increase effective August 2026"
}
```

### 6.5 — Renew Lease

```
POST {{base_url}}/leases/1/renew
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "start_date": "2027-08-01",
    "end_date": "2028-07-31",
    "rent_amount": 1700.00
}
```

> This expires the old lease and creates a new one with updated terms.

### 6.6 — Terminate Lease

```
POST {{base_url}}/leases/1/terminate
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "reason": "Tenant requested early termination due to relocation"
}
```

---

## 7. Invoices

### 7.1 — List Invoices

```
GET {{base_url}}/invoices
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?status=sent`, `?lease_id=1`, `?from_date=2026-01-01`, `?to_date=2026-12-31`

### 7.2 — Create Manual Invoice

```
POST {{base_url}}/invoices
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "lease_id": 1,
    "due_date": "2026-08-05",
    "items": [
        {
            "type": "rent",
            "description": "Monthly Rent — August 2026",
            "unit_price": 1500.00
        },
        {
            "type": "other",
            "description": "Parking Space Fee",
            "quantity": 1,
            "unit_price": 150.00
        },
        {
            "type": "utility",
            "description": "Water Bill — July 2026",
            "unit_price": 45.00
        }
    ]
}
```

**Item types:** `rent`, `deposit`, `late_fee`, `utility`, `maintenance`, `other`

### 7.3 — Get Invoice Details

```
GET {{base_url}}/invoices/1
Authorization: Bearer {{token}}
```

### 7.4 — Update Invoice

```
PUT {{base_url}}/invoices/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "notes": "Due date extended per tenant request",
    "status": "draft"
}
```

### 7.5 — Send Invoice

```
POST {{base_url}}/invoices/1/send
Authorization: Bearer {{token}}
```

### 7.6 — Void Invoice

```
POST {{base_url}}/invoices/1/void
Authorization: Bearer {{token}}
```

---

## 8. Payments

### 8.1 — List Payments

```
GET {{base_url}}/payments
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?status=completed`, `?lease_id=1`, `?invoice_id=1`, `?method=bank_transfer`

### 8.2 — Record Payment

```
POST {{base_url}}/payments
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "lease_id": 1,
    "invoice_id": 1,
    "amount": 1500.00,
    "payment_date": "2026-08-01",
    "due_date": "2026-08-05",
    "method": "bank_transfer",
    "status": "completed",
    "reference_number": "TXN-2026-001",
    "transaction_id": "BNK-REF-12345",
    "notes": "August rent payment"
}
```

> 💡 Providing `invoice_id` will automatically update the invoice's `amount_paid`, `balance_due`, and `status`.

**Payment methods:** `cash`, `bank_transfer`, `credit_card`, `check`, `online`

### 8.3 — Get Payment Details

```
GET {{base_url}}/payments/1
Authorization: Bearer {{token}}
```

---

## 9. Utility Billing

### 9.1 — List Utility Types (Tariffs)

```
GET {{base_url}}/utility-types
Authorization: Bearer {{token}}
```

### 9.2 — Create Utility Type

```
POST {{base_url}}/utility-types
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Electricity",
    "unit_of_measure": "kWh",
    "default_rate": 0.12,
    "is_active": true
}
```

### 9.3 — Update Utility Type (Change Tariff)

```
PUT {{base_url}}/utility-types/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "default_rate": 0.15,
    "name": "Electricity (Peak)"
}
```

### 9.4 — List Meters for a Unit

```
GET {{base_url}}/units/1/meters
Authorization: Bearer {{token}}
```

### 9.5 — Install a Meter

```
POST {{base_url}}/units/1/meters
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "utility_type_id": 1,
    "meter_number": "ELC-001-A101",
    "installation_date": "2026-01-15"
}
```

### 9.6 — Update Meter

```
PUT {{base_url}}/meters/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "meter_number": "ELC-002-A101",
    "is_active": true
}
```

### 9.7 — Decommission Meter

```
DELETE {{base_url}}/meters/1
Authorization: Bearer {{token}}
```

### 9.8 — Get Reading History

```
GET {{base_url}}/meters/1/readings
Authorization: Bearer {{token}}
```

### 9.9 — Record Meter Reading ⭐

```
POST {{base_url}}/meters/1/readings
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "reading_value": 5800.50,
    "reading_date": "2026-08-01"
}
```

> 💡 This automatically:
> 1. Calculates consumption from the previous reading
> 2. Generates a utility charge using the tariff rate
> 3. The charge becomes "pending" and will be included in the next monthly invoice

**With custom rate override:**
```json
{
    "reading_value": 5800.50,
    "reading_date": "2026-08-01",
    "custom_rate": 0.18
}
```

### 9.10 — List Utility Charges

```
GET {{base_url}}/utility-charges
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?status=pending`, `?unit_id=1`

---

## 10. Maintenance Requests

### 10.1 — List Maintenance Requests

```
GET {{base_url}}/maintenance-requests
Authorization: Bearer {{token}}
```

**Optional Query Params:** `?status=open`, `?priority=high`, `?assigned_to=3`, `?category_id=1`, `?unit_id=1`

### 10.2 — Create Maintenance Request

```
POST {{base_url}}/maintenance-requests
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "unit_id": 1,
    "category_id": 1,
    "title": "Leaking kitchen faucet",
    "description": "The kitchen faucet has been dripping constantly for the past 3 days. Water is pooling under the sink.",
    "priority": "medium"
}
```

### 10.3 — Create with Attachments

```
POST {{base_url}}/maintenance-requests
Authorization: Bearer {{token}}
```

**Body (form-data):**

| Key | Value | Type |
|-----|-------|------|
| `unit_id` | 1 | Text |
| `category_id` | 1 | Text |
| `title` | Leaking kitchen faucet | Text |
| `description` | Faucet dripping for 3 days | Text |
| `priority` | high | Text |
| `attachments[0]` | *(select photo)* | File |
| `attachments[1]` | *(select photo)* | File |

### 10.4 — Get Request Details

```
GET {{base_url}}/maintenance-requests/1
Authorization: Bearer {{token}}
```

### 10.5 — Update Request

```
PUT {{base_url}}/maintenance-requests/1
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "status": "in_progress",
    "priority": "high",
    "estimated_cost": 250.00,
    "scheduled_date": "2026-08-05 10:00:00"
}
```

**Status flow:** `open` → `in_progress` → `resolved` → `closed`

### 10.6 — Assign Request

```
POST {{base_url}}/maintenance-requests/1/assign
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:**
```json
{
    "assigned_to": 3
}
```

> 💡 This automatically sets the status to `in_progress` and sends a database notification to the assigned user.

### 10.7 — List Comments

```
GET {{base_url}}/maintenance-requests/1/comments
Authorization: Bearer {{token}}
```

> Note: Tenants cannot see comments marked as `is_internal: true`.

### 10.8 — Add Comment

```
POST {{base_url}}/maintenance-requests/1/comments
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (visible to tenant):**
```json
{
    "comment": "We've scheduled a plumber for tomorrow morning between 9-11 AM.",
    "is_internal": false
}
```

**Body (internal — staff only):**
```json
{
    "comment": "Vendor quoted $250 for parts + labor. Awaiting owner approval.",
    "is_internal": true
}
```

### 10.9 — List Attachments

```
GET {{base_url}}/maintenance-requests/1/attachments
Authorization: Bearer {{token}}
```

### 10.10 — Upload Attachments

```
POST {{base_url}}/maintenance-requests/1/attachments
Authorization: Bearer {{token}}
```

**Body (form-data):**

| Key | Value | Type |
|-----|-------|------|
| `files[0]` | *(select file)* | File |
| `files[1]` | *(select file)* | File |

---

## 11. Tenants (Read-Only)

### 11.1 — List Tenants

```
GET {{base_url}}/tenants
Authorization: Bearer {{token}}
```

### 11.2 — Get Tenant Details

```
GET {{base_url}}/tenants/4
Authorization: Bearer {{token}}
```

---

## 12. Testing Workflow — Recommended Order

Here's the recommended sequence for a complete end-to-end test:

### Step 1: Auth
1. ✅ Login as `admin@propertymanagement.test`
2. ✅ Verify with `GET /auth/me`

### Step 2: Settings
3. ✅ `GET /settings` — view defaults
4. ✅ `PUT /settings` — update currency to your preference

### Step 3: Property Setup
5. ✅ `POST /properties` — create a property
6. ✅ `POST /properties/{id}/images` — upload photos
7. ✅ `POST /units` — create units in that property
8. ✅ `POST /units/{id}/images` — upload unit photos

### Step 4: Utility Setup
9. ✅ `POST /utility-types` — create Electricity & Water tariffs
10. ✅ `POST /units/{id}/meters` — install meters on units

### Step 5: Lease & Billing
11. ✅ `POST /leases` — create a lease (auto-generates deposit invoice)
12. ✅ `GET /invoices` — see the deposit invoice
13. ✅ `POST /payments` — pay the deposit
14. ✅ `POST /meters/{id}/readings` — record meter readings
15. ✅ `POST /invoices` — create a manual monthly invoice

### Step 6: Maintenance
16. ✅ Login as `tenant@propertymanagement.test`
17. ✅ `POST /maintenance-requests` — create a request
18. ✅ Login back as admin
19. ✅ `POST /maintenance-requests/{id}/assign` — assign to staff
20. ✅ `POST /maintenance-requests/{id}/comments` — add internal note
21. ✅ `PUT /maintenance-requests/{id}` — mark as resolved

---

## 13. Common Error Responses

| Status | Error | Meaning |
|--------|-------|---------|
| 401 | `token_absent` | No Authorization header sent |
| 401 | `token_expired` | Token expired — use `POST /auth/refresh` |
| 401 | `token_invalid` | Malformed or tampered token |
| 403 | Role error | User doesn't have required role |
| 422 | Validation error | Request body failed validation |
| 404 | Not found | Resource doesn't exist |

**Example error response:**
```json
{
    "success": false,
    "message": "Token has expired. Please refresh your token.",
    "error": "token_expired"
}
```

---

## 14. Tips & Tricks

### Auto-save Token Script
Add this to **every Login request's** Post-response Scripts tab:
```javascript
if (pm.response.code === 200) {
    var response = pm.response.json();
    pm.environment.set("token", response.data.authorization.token);
}
```

### Quickly Switch Roles
Create separate login requests for each demo account and label them clearly:
- `Login — Super Admin`
- `Login — Admin`
- `Login — Manager`
- `Login — Owner`
- `Login — Tenant`

### Collection Variable for IDs
After creating a property/unit/lease, save the ID to a collection variable:
```javascript
if (pm.response.code === 201) {
    var response = pm.response.json();
    pm.collectionVariables.set("property_id", response.data.id);
}
```

Then use `{{property_id}}` in subsequent requests.
