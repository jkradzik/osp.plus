# REST API Plan - osp.plus

## 1. Resources

| Resource | Database Table(s) | Description |
|----------|-------------------|-------------|
| Auth | users, user_tenants | Authentication & session management |
| Users | users | User account management |
| Tenants | tenants, user_tenants | Fire station units |
| Persons | persons | Global identity (PESEL-based) |
| Members | members | Unit members |
| Decorations | person_decorations, decoration_types | Awards and honors |
| Equipment | member_equipment, equipment_types | Personal equipment |
| Fees | membership_fees, fee_settings | Membership fees |
| Finance | financial_records, financial_categories | Financial records |

---

## 2. Endpoints

### 2.1 Authentication

#### POST /api/auth/login
Authenticate user and receive JWT token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```

**Response (200 OK):**
```json
{
  "token": "eyJhbGciOiJSUzI1NiIs...",
  "refresh_token": "def50200...",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "email": "user@example.com",
    "is_superadmin": false
  },
  "tenants": [
    {
      "id": 1,
      "name": "OSP Siółkowa",
      "slug": "siolkowa",
      "roles": ["ROLE_USER", "ROLE_SKARBNIK"]
    }
  ]
}
```

**Errors:**
- `401 Unauthorized` - Invalid credentials
- `423 Locked` - Account disabled

---

#### POST /api/auth/refresh
Refresh JWT token.

**Request Body:**
```json
{
  "refresh_token": "def50200..."
}
```

**Response (200 OK):**
```json
{
  "token": "eyJhbGciOiJSUzI1NiIs...",
  "refresh_token": "def50200...",
  "expires_in": 3600
}
```

---

#### GET /api/auth/me
Get current user profile.

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "email": "user@example.com",
  "is_superadmin": false,
  "current_tenant": {
    "id": 1,
    "name": "OSP Siółkowa",
    "roles": ["ROLE_USER", "ROLE_SKARBNIK"]
  }
}
```

---

#### POST /api/auth/select-tenant
Select active tenant for session.

**Request Body:**
```json
{
  "tenant_id": 1
}
```

**Response (200 OK):**
```json
{
  "token": "eyJhbGciOiJSUzI1NiIs...",
  "tenant": {
    "id": 1,
    "name": "OSP Siółkowa",
    "roles": ["ROLE_USER", "ROLE_SKARBNIK"]
  }
}
```

**Errors:**
- `403 Forbidden` - User has no access to this tenant

---

### 2.2 Tenants

#### GET /api/tenants
List tenants accessible by current user.

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "OSP Siółkowa",
      "slug": "siolkowa",
      "city": "Siółkowa",
      "roles": ["ROLE_USER", "ROLE_SKARBNIK"],
      "is_active": true
    }
  ],
  "meta": {
    "total": 1
  }
}
```

---

#### GET /api/tenants/{id}
Get tenant details.

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "OSP Siółkowa",
  "slug": "siolkowa",
  "address": "ul. Strażacka 1",
  "city": "Siółkowa",
  "postal_code": "33-100",
  "phone": "+48 123 456 789",
  "email": "osp@siolkowa.pl",
  "nip": "1234567890",
  "regon": "123456789",
  "krs": null,
  "is_active": true,
  "created_at": "2024-01-15T10:30:00Z"
}
```

---

### 2.3 Members

#### GET /api/members
List members of current tenant.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | Page number (default: 1) |
| limit | int | Items per page (default: 20, max: 100) |
| search | string | Search by name |
| status | string | Filter by membership_status |
| sort | string | Sort field (default: last_name) |
| order | string | asc/desc (default: asc) |

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "person": {
        "id": 1,
        "first_name": "Jan",
        "last_name": "Kowalski",
        "pesel": "90010112345"
      },
      "membership_status": "active",
      "join_date": "2015-03-20",
      "board_position": "Naczelnik",
      "phone": "+48 600 123 456",
      "email": "jan@example.com"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "total_pages": 3
  }
}
```

---

#### GET /api/members/{id}
Get member details with related data.

**Response (200 OK):**
```json
{
  "id": 1,
  "tenant_id": 1,
  "person": {
    "id": 1,
    "first_name": "Jan",
    "last_name": "Kowalski",
    "pesel": "90010112345",
    "birth_date": "1990-01-01",
    "decorations": [
      {
        "id": 1,
        "type": {
          "id": 5,
          "name": "Odznaka \"Za wysługę 10 lat\""
        },
        "awarded_at": "2025-03-15",
        "awarded_by_level": "unit"
      }
    ]
  },
  "membership_status": "active",
  "join_date": "2015-03-20",
  "leave_date": null,
  "address": "ul. Kwiatowa 5, 33-100 Siółkowa",
  "phone": "+48 600 123 456",
  "email": "jan@example.com",
  "board_position": "Naczelnik",
  "notes": null,
  "equipment": [
    {
      "id": 1,
      "type": { "id": 1, "name": "Ubranie specjalne - kurtka" },
      "size": "L",
      "issued_at": "2020-05-10"
    }
  ],
  "fees": [
    {
      "id": 1,
      "year": 2025,
      "amount": "50.00",
      "status": "paid",
      "paid_at": "2025-02-15"
    }
  ],
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-06-20T14:45:00Z"
}
```

---

#### POST /api/members
Create new member.

**Request Body:**
```json
{
  "person": {
    "pesel": "90010112345",
    "first_name": "Jan",
    "last_name": "Kowalski",
    "birth_date": "1990-01-01"
  },
  "membership_status": "active",
  "join_date": "2025-01-15",
  "address": "ul. Kwiatowa 5",
  "phone": "+48 600 123 456",
  "email": "jan@example.com",
  "board_position": null
}
```

**Note:** If person with given PESEL exists, it will be linked. Otherwise, new person is created.

**Response (201 Created):**
```json
{
  "id": 2,
  "person": {
    "id": 1,
    "first_name": "Jan",
    "last_name": "Kowalski",
    "pesel": "90010112345"
  },
  "membership_status": "active",
  "join_date": "2025-01-15"
}
```

**Errors:**
- `400 Bad Request` - Validation error (invalid PESEL, missing required fields)
- `409 Conflict` - Person already member of this tenant

---

#### PUT /api/members/{id}
Update member.

**Request Body:**
```json
{
  "membership_status": "inactive",
  "leave_date": "2025-06-30",
  "phone": "+48 600 999 888",
  "board_position": null
}
```

**Response (200 OK):** Updated member object

---

#### DELETE /api/members/{id}
Delete member (only for incorrectly entered data).

**Response (204 No Content)**

**Errors:**
- `403 Forbidden` - Cannot delete member with history (fees, equipment)
- `404 Not Found` - Member not found

---

### 2.4 Persons (Global)

#### GET /api/persons/search
Search person by PESEL.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| pesel | string | PESEL number (required) |

**Response (200 OK):**
```json
{
  "id": 1,
  "pesel": "90010112345",
  "first_name": "Jan",
  "last_name": "Kowalski",
  "birth_date": "1990-01-01",
  "is_member_of_current_tenant": true,
  "member_id": 5
}
```

**Response (404 Not Found):** Person with this PESEL not found

---

### 2.5 Decorations

#### GET /api/decoration-types
List all decoration types (global dictionary).

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| category | string | Filter by category |

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Odznaka \"Strażak Wzorowy\"",
      "category": "osp",
      "required_years": null
    },
    {
      "id": 5,
      "name": "Odznaka \"Za wysługę 10 lat\"",
      "category": "osp",
      "required_years": 10
    }
  ]
}
```

---

#### GET /api/members/{memberId}/decorations
List decorations of a member (through person).

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "decoration_type": {
        "id": 5,
        "name": "Odznaka \"Za wysługę 10 lat\"",
        "category": "osp"
      },
      "awarded_at": "2025-03-15",
      "awarded_by_level": "unit",
      "awarded_by_name": "OSP Siółkowa",
      "certificate_number": "123/2025"
    }
  ]
}
```

---

#### POST /api/members/{memberId}/decorations
Award decoration to member.

**Request Body:**
```json
{
  "decoration_type_id": 5,
  "awarded_at": "2025-03-15",
  "awarded_by_level": "unit",
  "awarded_by_name": "OSP Siółkowa",
  "certificate_number": "123/2025"
}
```

**Response (201 Created):** Created decoration object

**Errors:**
- `409 Conflict` - Person already has this decoration

---

#### DELETE /api/members/{memberId}/decorations/{id}
Remove decoration (correction).

**Response (204 No Content)**

---

### 2.6 Equipment

#### GET /api/equipment-types
List equipment types (global + tenant-specific).

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Ubranie specjalne - kurtka",
      "category": "clothing",
      "has_sizes": true,
      "is_global": true
    },
    {
      "id": 100,
      "name": "Latarka taktyczna XYZ",
      "category": "tools",
      "has_sizes": false,
      "is_global": false
    }
  ]
}
```

---

#### POST /api/equipment-types
Create tenant-specific equipment type.

**Request Body:**
```json
{
  "name": "Latarka taktyczna XYZ",
  "category": "tools",
  "has_sizes": false,
  "description": "Latarka LED 1000lm"
}
```

**Response (201 Created):** Created equipment type

---

#### GET /api/members/{memberId}/equipment
List equipment assigned to member.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "equipment_type": {
        "id": 1,
        "name": "Ubranie specjalne - kurtka",
        "category": "clothing"
      },
      "size": "L",
      "serial_number": "SN-2020-001",
      "issued_at": "2020-05-10",
      "returned_at": null,
      "condition": "good"
    }
  ]
}
```

---

#### POST /api/members/{memberId}/equipment
Assign equipment to member.

**Request Body:**
```json
{
  "equipment_type_id": 1,
  "size": "L",
  "serial_number": "SN-2025-050",
  "issued_at": "2025-01-20",
  "condition": "new"
}
```

**Response (201 Created):** Created equipment assignment

---

#### PUT /api/members/{memberId}/equipment/{id}
Update equipment assignment.

**Request Body:**
```json
{
  "returned_at": "2025-06-15",
  "condition": "worn"
}
```

**Response (200 OK):** Updated equipment assignment

---

#### DELETE /api/members/{memberId}/equipment/{id}
Remove equipment assignment.

**Response (204 No Content)**

---

#### GET /api/equipment/inventory
Equipment inventory report.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| equipment_type_id | int | Filter by type |
| assigned | boolean | true=assigned, false=returned |

**Response (200 OK):**
```json
{
  "data": [
    {
      "equipment_type": {
        "id": 1,
        "name": "Ubranie specjalne - kurtka"
      },
      "total_assigned": 25,
      "assignments": [
        {
          "member": { "id": 1, "name": "Jan Kowalski" },
          "size": "L",
          "issued_at": "2020-05-10"
        }
      ]
    }
  ],
  "summary": {
    "total_items": 150,
    "total_assigned": 120,
    "total_returned": 30
  }
}
```

---

### 2.7 Membership Fees

#### GET /api/fee-settings
List fee settings for tenant.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "year": 2025,
      "annual_fee_amount": "50.00",
      "payment_deadline": "2025-03-31",
      "youth_pays_fee": false,
      "honorary_pays_fee": false
    }
  ]
}
```

---

#### POST /api/fee-settings
Create fee settings for year.

**Request Body:**
```json
{
  "year": 2026,
  "annual_fee_amount": "60.00",
  "payment_deadline": "2026-03-31",
  "youth_pays_fee": false,
  "honorary_pays_fee": false
}
```

**Response (201 Created):** Created fee settings

**Errors:**
- `409 Conflict` - Settings for this year already exist

---

#### PUT /api/fee-settings/{id}
Update fee settings.

**Response (200 OK):** Updated settings

---

#### GET /api/membership-fees
List membership fees.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| year | int | Filter by year |
| status | string | Filter by status |
| member_id | int | Filter by member |
| page | int | Page number |
| limit | int | Items per page |

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "member": {
        "id": 1,
        "name": "Jan Kowalski"
      },
      "year": 2025,
      "amount": "50.00",
      "status": "paid",
      "is_custom_amount": false,
      "paid_at": "2025-02-15"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 45
  }
}
```

---

#### POST /api/membership-fees
Create membership fee.

**Request Body:**
```json
{
  "member_id": 1,
  "year": 2025,
  "amount": "50.00",
  "status": "unpaid"
}
```

**Response (201 Created):** Created fee

---

#### PUT /api/membership-fees/{id}
Update membership fee.

**Request Body:**
```json
{
  "status": "paid",
  "paid_at": "2025-02-15"
}
```

**Response (200 OK):** Updated fee

---

#### POST /api/membership-fees/generate
Generate fees for all active members for a year.

**Request Body:**
```json
{
  "year": 2025
}
```

**Response (200 OK):**
```json
{
  "created": 35,
  "skipped": 10,
  "skipped_reasons": {
    "already_exists": 5,
    "youth": 3,
    "honorary": 2
  }
}
```

---

#### POST /api/membership-fees/validate-overdue
Mark overdue fees (business logic).

**Request Body:**
```json
{
  "year": 2025,
  "reference_date": "2025-04-01"
}
```

**Response (200 OK):**
```json
{
  "marked_overdue": 5,
  "fees": [
    { "id": 10, "member_name": "Anna Nowak", "year": 2025 }
  ]
}
```

---

#### GET /api/membership-fees/summary
Fee collection summary.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| year | int | Year (required) |

**Response (200 OK):**
```json
{
  "year": 2025,
  "total_members": 45,
  "fees": {
    "paid": { "count": 30, "amount": "1500.00" },
    "unpaid": { "count": 5, "amount": "250.00" },
    "overdue": { "count": 3, "amount": "150.00" },
    "exempt": { "count": 2, "amount": "0.00" },
    "not_applicable": { "count": 5, "amount": "0.00" }
  },
  "collection_rate": "66.67%"
}
```

---

### 2.8 Financial Records

#### GET /api/financial-categories
List financial categories (global + tenant-specific).

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Dotacja z budżetu gminy",
      "type": "income",
      "is_global": true
    },
    {
      "id": 100,
      "name": "Zakup wyposażenia specjalnego",
      "type": "expense",
      "is_global": false
    }
  ]
}
```

---

#### POST /api/financial-categories
Create tenant-specific category.

**Request Body:**
```json
{
  "name": "Dotacja z funduszu sołeckiego",
  "type": "income"
}
```

**Response (201 Created):** Created category

---

#### GET /api/financial-records
List financial records.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| type | string | income/expense |
| category_id | int | Filter by category |
| date_from | date | Start date |
| date_to | date | End date |
| page | int | Page number |
| limit | int | Items per page |

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "category": {
        "id": 1,
        "name": "Dotacja z budżetu gminy"
      },
      "type": "income",
      "amount": "15000.00",
      "description": "Dotacja roczna 2025",
      "document_number": "DOT/2025/001",
      "recorded_at": "2025-01-15",
      "member": null,
      "created_by": { "id": 1, "email": "skarbnik@osp.pl" }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 120
  }
}
```

---

#### POST /api/financial-records
Create financial record.

**Request Body:**
```json
{
  "category_id": 1,
  "type": "income",
  "amount": "15000.00",
  "description": "Dotacja roczna 2025",
  "document_number": "DOT/2025/001",
  "recorded_at": "2025-01-15",
  "member_id": null
}
```

**Response (201 Created):** Created record

---

#### PUT /api/financial-records/{id}
Update financial record.

**Response (200 OK):** Updated record

---

#### DELETE /api/financial-records/{id}
Delete financial record.

**Response (204 No Content)**

---

#### GET /api/financial-records/balance
Financial balance.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| date_from | date | Start date |
| date_to | date | End date |

**Response (200 OK):**
```json
{
  "period": {
    "from": "2025-01-01",
    "to": "2025-12-31"
  },
  "income": {
    "total": "25000.00",
    "by_category": [
      { "category": "Dotacja z budżetu gminy", "amount": "15000.00" },
      { "category": "Składki członkowskie", "amount": "2500.00" }
    ]
  },
  "expense": {
    "total": "18000.00",
    "by_category": [
      { "category": "Paliwo do pojazdów", "amount": "5000.00" },
      { "category": "Szkolenia i kursy", "amount": "3000.00" }
    ]
  },
  "balance": "7000.00"
}
```

---

#### GET /api/financial-records/monthly-summary
Monthly financial summary.

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| year | int | Year (required) |

**Response (200 OK):**
```json
{
  "year": 2025,
  "months": [
    {
      "month": 1,
      "income": "15000.00",
      "expense": "2500.00",
      "balance": "12500.00"
    },
    {
      "month": 2,
      "income": "500.00",
      "expense": "1800.00",
      "balance": "-1300.00"
    }
  ],
  "yearly_total": {
    "income": "25000.00",
    "expense": "18000.00",
    "balance": "7000.00"
  }
}
```

---

## 3. Authentication & Authorization

### 3.1 Authentication Mechanism

**JWT (JSON Web Token)** via `lexik/jwt-authentication-bundle`

**Token Structure:**
```json
{
  "iat": 1704067200,
  "exp": 1704070800,
  "sub": "1",
  "email": "user@example.com",
  "tenant_id": 1,
  "roles": ["ROLE_USER", "ROLE_SKARBNIK"]
}
```

**Token Lifetime:** 1 hour (3600 seconds)
**Refresh Token Lifetime:** 30 days

### 3.2 Authorization - Role-Based Access Control (RBAC)

**Roles per tenant:**

| Role | Description | Permissions |
|------|-------------|-------------|
| ROLE_USER | Regular member | Read own data |
| ROLE_DRUH | Active member | Read all members, own equipment |
| ROLE_NACZELNIK | Fire chief | Full access to members, equipment |
| ROLE_SKARBNIK | Treasurer | Full access to fees, finance |
| ROLE_PREZES | President | Full access to all resources |
| ROLE_ADMIN | Unit admin | Full access + settings |
| ROLE_SUPERADMIN | Platform admin | Access to all tenants |

### 3.3 Endpoint Authorization Matrix

| Endpoint | DRUH | NACZELNIK | SKARBNIK | PREZES | ADMIN |
|----------|------|-----------|----------|--------|-------|
| GET /members | ✓ | ✓ | ✓ | ✓ | ✓ |
| POST /members | - | ✓ | - | ✓ | ✓ |
| PUT /members | - | ✓ | - | ✓ | ✓ |
| DELETE /members | - | - | - | - | ✓ |
| GET /membership-fees | own | ✓ | ✓ | ✓ | ✓ |
| POST /membership-fees | - | - | ✓ | ✓ | ✓ |
| GET /financial-records | - | - | ✓ | ✓ | ✓ |
| POST /financial-records | - | - | ✓ | ✓ | ✓ |
| */decorations | - | ✓ | - | ✓ | ✓ |
| */equipment | - | ✓ | - | ✓ | ✓ |

---

## 4. Validation & Business Logic

### 4.1 Validation Rules

#### Person
| Field | Rules |
|-------|-------|
| pesel | Required, exactly 11 digits, valid checksum, unique |
| first_name | Required, max 100 chars |
| last_name | Required, max 100 chars |
| birth_date | Required, must match PESEL |

#### Member
| Field | Rules |
|-------|-------|
| person_id | Required, must exist |
| membership_status | Required, valid enum |
| join_date | Required, not in future |
| leave_date | Optional, must be >= join_date |
| email | Optional, valid email format |
| phone | Optional, valid phone format |

#### Membership Fee
| Field | Rules |
|-------|-------|
| member_id | Required, must exist in tenant |
| year | Required, 1900-2100 |
| amount | Required, >= 0 |
| status | Required, valid enum |
| paid_at | Required if status = 'paid' |

#### Financial Record
| Field | Rules |
|-------|-------|
| category_id | Required, must exist (global or tenant) |
| type | Required, must match category type |
| amount | Required, > 0 |
| description | Required, max 1000 chars |
| recorded_at | Required, valid date |

### 4.2 Business Logic

#### Fee Status Validation
```
When: current_date > payment_deadline AND status = 'unpaid'
Then: status can be changed to 'overdue'

Endpoint: POST /api/membership-fees/validate-overdue
```

#### Automatic Fee Generation
```
When: POST /api/membership-fees/generate for year X
Then:
  - For each active member:
    - If youth AND fee_settings.youth_pays_fee = false → skip
    - If honorary AND fee_settings.honorary_pays_fee = false → skip
    - If supporting → create with is_custom_amount = true, amount = 0
    - Otherwise → create with amount from fee_settings
```

#### Decoration Uniqueness
```
Constraint: One person cannot receive the same decoration twice
Checked at: person_decorations (person_id, decoration_type_id) UNIQUE
```

#### Member Deletion Protection
```
When: DELETE /api/members/{id}
Then:
  - If member has fees → 403 Forbidden
  - If member has equipment → 403 Forbidden
  - If member has decorations → 403 Forbidden (through person)
  - Otherwise → 204 No Content
```

#### Tenant Isolation
```
All queries automatically filtered by current tenant_id from JWT.
Exception: superadmin can access all tenants.
```

### 4.3 API Platform Configuration

All endpoints utilize API Platform 3.x with:
- Automatic OpenAPI documentation at `/api/docs`
- JSON-LD support (`application/ld+json`)
- Standard JSON support (`application/json`)
- Pagination via `hydra:view` or custom meta
- Filtering via query parameters
- Validation via Symfony Validator

### 4.4 Error Response Format

```json
{
  "type": "https://tools.ietf.org/html/rfc7231#section-6.5.1",
  "title": "Validation Error",
  "status": 400,
  "detail": "pesel: This value is not a valid PESEL number.",
  "violations": [
    {
      "propertyPath": "pesel",
      "message": "This value is not a valid PESEL number.",
      "code": "INVALID_PESEL"
    }
  ]
}
```

### 4.5 Rate Limiting

| Endpoint Type | Limit |
|---------------|-------|
| Authentication | 5 requests/minute |
| Read operations | 100 requests/minute |
| Write operations | 30 requests/minute |
| Batch operations | 10 requests/minute |

---

## 5. Summary

### Endpoint Count by Resource

| Resource | GET | POST | PUT | DELETE | Total |
|----------|-----|------|-----|--------|-------|
| Auth | 1 | 3 | - | - | 4 |
| Tenants | 2 | - | - | - | 2 |
| Members | 2 | 1 | 1 | 1 | 5 |
| Persons | 1 | - | - | - | 1 |
| Decorations | 2 | 1 | - | 1 | 4 |
| Equipment | 3 | 2 | 1 | 1 | 7 |
| Fees | 4 | 3 | 1 | - | 8 |
| Finance | 4 | 2 | 1 | 1 | 8 |
| **Total** | **19** | **12** | **4** | **4** | **39** |