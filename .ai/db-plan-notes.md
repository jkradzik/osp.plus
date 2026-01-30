# Plan schematu bazy danych - osp.plus

## Podsumowanie decyzji

| Kwestia | Decyzja |
|---------|---------|
| Multi-tenant | Model gotowy na multi, MVP = single-tenant |
| Tożsamość globalna | Tabela `persons` (PESEL jako identyfikator) |
| Odznaczenia | Globalne, widoczne we wszystkich jednostkach |
| Wyposażenie | Per tenant (każda jednostka osobno) |
| Składki | Per tenant, bez modułu zbiórek w MVP |
| Role użytkowników | Per tenant (w `user_tenants`) |
| Audit trail | Proste: created_at, updated_at, created_by, updated_by |

---

## Diagram ERD (tekstowy)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              WARSTWA GLOBALNA                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────┐     ┌──────────────┐     ┌────────────────────┐          │
│  │    users     │     │   persons    │     │  decoration_types  │          │
│  ├──────────────┤     ├──────────────┤     ├────────────────────┤          │
│  │ id           │     │ id           │     │ id                 │          │
│  │ email        │     │ pesel        │     │ name               │          │
│  │ password     │     │ first_name   │     │ category           │          │
│  │ is_superadmin│     │ last_name    │     │ required_years     │          │
│  └──────┬───────┘     └──────┬───────┘     └─────────┬──────────┘          │
│         │                    │                       │                      │
│         │                    │    ┌──────────────────┘                      │
│         │                    │    │                                         │
│         │                    ▼    ▼                                         │
│         │             ┌─────────────────────┐                               │
│         │             │ person_decorations  │                               │
│         │             ├─────────────────────┤                               │
│         │             │ person_id (FK)      │                               │
│         │             │ decoration_type_id  │                               │
│         │             │ awarded_at          │                               │
│         │             │ awarded_by_level    │                               │
│         │             └─────────────────────┘                               │
│         │                                                                   │
└─────────┼───────────────────────────────────────────────────────────────────┘
          │
          │
┌─────────┼───────────────────────────────────────────────────────────────────┐
│         │                    WARSTWA TENANT                                 │
├─────────┼───────────────────────────────────────────────────────────────────┤
│         │                                                                   │
│         │         ┌──────────────┐                                          │
│         │         │   tenants    │                                          │
│         │         ├──────────────┤                                          │
│         │         │ id           │                                          │
│         │         │ name         │                                          │
│         │         │ slug         │                                          │
│         └────────►└──────┬───────┘                                          │
│                          │                                                  │
│         ┌────────────────┼────────────────┐                                 │
│         │                │                │                                 │
│         ▼                ▼                ▼                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐                        │
│  │user_tenants │  │   members   │  │ fee_settings │                        │
│  ├─────────────┤  ├─────────────┤  ├──────────────┤                        │
│  │ user_id(FK) │  │ id          │  │ tenant_id    │                        │
│  │ tenant_id   │  │ tenant_id   │  │ year         │                        │
│  │ roles (JSON)│  │ person_id   │  │ amount       │                        │
│  │ is_active   │  │ status      │  │ deadline     │                        │
│  └─────────────┘  │ join_date   │  └──────────────┘                        │
│                   └──────┬──────┘                                           │
│                          │                                                  │
│         ┌────────────────┼────────────────┬─────────────────┐              │
│         │                │                │                 │              │
│         ▼                ▼                ▼                 ▼              │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌────────────────┐    │
│  │membership_   │ │member_       │ │financial_    │ │financial_      │    │
│  │fees          │ │equipment     │ │records       │ │categories      │    │
│  ├──────────────┤ ├──────────────┤ ├──────────────┤ ├────────────────┤    │
│  │ member_id    │ │ member_id    │ │ tenant_id    │ │ id             │    │
│  │ year         │ │ equipment_   │ │ category_id  │ │ tenant_id(null)│    │
│  │ amount       │ │ type_id      │ │ type         │ │ name           │    │
│  │ status       │ │ size         │ │ amount       │ │ type           │    │
│  └──────────────┘ │ issued_at    │ │ member_id    │ └────────────────┘    │
│                   └──────────────┘ └──────────────┘                        │
│                          ▲                                                  │
│                          │                                                  │
│                   ┌──────────────┐                                          │
│                   │equipment_    │                                          │
│                   │types         │                                          │
│                   ├──────────────┤                                          │
│                   │ id           │                                          │
│                   │ tenant_id    │  ◄── nullable (NULL = globalny)         │
│                   │ name         │                                          │
│                   │ category     │                                          │
│                   │ has_sizes    │                                          │
│                   └──────────────┘                                          │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Szczegółowy schemat tabel

### 1. Tabele globalne (bez tenant_id)

#### users
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_superadmin BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
```

#### persons
```sql
CREATE TABLE persons (
    id SERIAL PRIMARY KEY,
    pesel VARCHAR(11) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    death_date DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_persons_pesel ON persons(pesel);
CREATE INDEX idx_persons_name ON persons(last_name, first_name);
```

#### decoration_types
```sql
CREATE TYPE decoration_category AS ENUM ('osp', 'zosp', 'state', 'municipal', 'other');

CREATE TABLE decoration_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category decoration_category NOT NULL,
    required_years INT,
    description TEXT,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

#### person_decorations
```sql
CREATE TYPE awarded_by_level AS ENUM (
    'unit',        -- jednostka OSP
    'municipal',   -- związek gminny / gmina
    'district',    -- powiat
    'voivodeship', -- województwo
    'national'     -- poziom krajowy
);

CREATE TABLE person_decorations (
    id SERIAL PRIMARY KEY,
    person_id INT NOT NULL REFERENCES persons(id) ON DELETE CASCADE,
    decoration_type_id INT NOT NULL REFERENCES decoration_types(id),
    awarded_at DATE NOT NULL,
    awarded_by_level awarded_by_level NOT NULL,
    awarded_by_name VARCHAR(255),
    certificate_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT REFERENCES users(id),

    UNIQUE(person_id, decoration_type_id)
);

CREATE INDEX idx_person_decorations_person ON person_decorations(person_id);
```

---

### 2. Tabele tenant (jednostka OSP)

#### tenants
```sql
CREATE TABLE tenants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    nip VARCHAR(13),
    regon VARCHAR(14),
    krs VARCHAR(20),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tenants_slug ON tenants(slug);
```

#### user_tenants
```sql
CREATE TABLE user_tenants (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    roles JSONB NOT NULL DEFAULT '["ROLE_USER"]',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP,

    UNIQUE(user_id, tenant_id)
);

CREATE INDEX idx_user_tenants_user ON user_tenants(user_id);
CREATE INDEX idx_user_tenants_tenant ON user_tenants(tenant_id);
```

#### members
```sql
CREATE TYPE membership_status AS ENUM (
    'active',      -- aktywny
    'inactive',    -- nieaktywny
    'honorary',    -- honorowy
    'supporting',  -- wspierający
    'youth',       -- MDP
    'removed',     -- skreślony
    'deceased'     -- zmarły
);

CREATE TABLE members (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    person_id INT NOT NULL REFERENCES persons(id),
    membership_status membership_status NOT NULL DEFAULT 'active',
    join_date DATE NOT NULL,
    leave_date DATE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    board_position VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT REFERENCES users(id),
    updated_by INT REFERENCES users(id),

    UNIQUE(tenant_id, person_id)
);

CREATE INDEX idx_members_tenant ON members(tenant_id);
CREATE INDEX idx_members_person ON members(person_id);
CREATE INDEX idx_members_status ON members(tenant_id, membership_status);
```

#### fee_settings
```sql
CREATE TABLE fee_settings (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    year INT NOT NULL,
    annual_fee_amount DECIMAL(10,2) NOT NULL,
    payment_deadline DATE NOT NULL DEFAULT '03-31',
    youth_pays_fee BOOLEAN NOT NULL DEFAULT FALSE,
    honorary_pays_fee BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(tenant_id, year)
);
```

#### membership_fees
```sql
CREATE TYPE fee_status AS ENUM (
    'unpaid',         -- nieopłacona
    'paid',           -- opłacona
    'overdue',        -- zaległa
    'exempt',         -- zwolniony
    'not_applicable'  -- nie dotyczy
);

CREATE TABLE membership_fees (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    year INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status fee_status NOT NULL DEFAULT 'unpaid',
    is_custom_amount BOOLEAN NOT NULL DEFAULT FALSE,
    paid_at DATE,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT REFERENCES users(id),
    updated_by INT REFERENCES users(id),

    UNIQUE(member_id, year)
);

CREATE INDEX idx_membership_fees_member ON membership_fees(member_id);
CREATE INDEX idx_membership_fees_status ON membership_fees(status);
CREATE INDEX idx_membership_fees_year ON membership_fees(year);
```

---

### 3. Tabele wyposażenia

#### equipment_types
```sql
CREATE TYPE equipment_category AS ENUM ('clothing', 'protective', 'tools', 'other');

CREATE TABLE equipment_types (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE, -- NULL = globalny
    name VARCHAR(255) NOT NULL,
    category equipment_category NOT NULL,
    has_sizes BOOLEAN NOT NULL DEFAULT FALSE,
    description TEXT,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_equipment_types_tenant ON equipment_types(tenant_id);
```

#### member_equipment
```sql
CREATE TABLE member_equipment (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    equipment_type_id INT NOT NULL REFERENCES equipment_types(id),
    size VARCHAR(20),
    serial_number VARCHAR(100),
    issued_at DATE NOT NULL,
    returned_at DATE,
    condition VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT REFERENCES users(id),
    updated_by INT REFERENCES users(id)
);

CREATE INDEX idx_member_equipment_member ON member_equipment(member_id);
CREATE INDEX idx_member_equipment_type ON member_equipment(equipment_type_id);
```

---

### 4. Tabele finansowe

#### financial_categories
```sql
CREATE TYPE financial_type AS ENUM ('income', 'expense');

CREATE TABLE financial_categories (
    id SERIAL PRIMARY KEY,
    tenant_id INT REFERENCES tenants(id) ON DELETE CASCADE, -- NULL = globalny
    name VARCHAR(255) NOT NULL,
    type financial_type NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_financial_categories_tenant ON financial_categories(tenant_id);
CREATE INDEX idx_financial_categories_type ON financial_categories(type);
```

#### financial_records
```sql
CREATE TABLE financial_records (
    id SERIAL PRIMARY KEY,
    tenant_id INT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    category_id INT NOT NULL REFERENCES financial_categories(id),
    type financial_type NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    document_number VARCHAR(100),
    recorded_at DATE NOT NULL,
    member_id INT REFERENCES members(id), -- opcjonalne powiązanie
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INT REFERENCES users(id),
    updated_by INT REFERENCES users(id)
);

CREATE INDEX idx_financial_records_tenant ON financial_records(tenant_id);
CREATE INDEX idx_financial_records_date ON financial_records(recorded_at);
CREATE INDEX idx_financial_records_category ON financial_records(category_id);
CREATE INDEX idx_financial_records_type ON financial_records(type);
```

---

## Dane początkowe (seedy)

### decoration_types
```sql
INSERT INTO decoration_types (name, category, required_years, sort_order) VALUES
('Odznaka "Strażak Wzorowy"', 'osp', NULL, 1),
('Odznaka "Za wysługę 5 lat"', 'osp', 5, 10),
('Odznaka "Za wysługę 10 lat"', 'osp', 10, 11),
('Odznaka "Za wysługę 15 lat"', 'osp', 15, 12),
('Odznaka "Za wysługę 20 lat"', 'osp', 20, 13),
('Odznaka "Za wysługę 25 lat"', 'osp', 25, 14),
('Odznaka "Za wysługę 30 lat"', 'osp', 30, 15),
('Odznaka "Za wysługę 35 lat"', 'osp', 35, 16),
('Odznaka "Za wysługę 40 lat"', 'osp', 40, 17),
('Odznaka "Za wysługę 45 lat"', 'osp', 45, 18),
('Odznaka "Za wysługę 50 lat"', 'osp', 50, 19),
('Medal "Za Zasługi dla Pożarnictwa" - brązowy', 'zosp', NULL, 20),
('Medal "Za Zasługi dla Pożarnictwa" - srebrny', 'zosp', NULL, 21),
('Medal "Za Zasługi dla Pożarnictwa" - złoty', 'zosp', NULL, 22),
('Złoty Znak Związku OSP RP', 'zosp', NULL, 30);
```

### equipment_types (globalne)
```sql
INSERT INTO equipment_types (tenant_id, name, category, has_sizes, sort_order) VALUES
(NULL, 'Ubranie specjalne - kurtka', 'clothing', TRUE, 1),
(NULL, 'Ubranie specjalne - spodnie', 'clothing', TRUE, 2),
(NULL, 'Hełm strażacki', 'protective', TRUE, 3),
(NULL, 'Buty specjalne', 'protective', TRUE, 4),
(NULL, 'Rękawice specjalne', 'protective', TRUE, 5),
(NULL, 'Kominiarka', 'protective', TRUE, 6),
(NULL, 'Pas strażacki', 'protective', FALSE, 7),
(NULL, 'Latarka', 'tools', FALSE, 8);
```

### financial_categories (globalne)
```sql
INSERT INTO financial_categories (tenant_id, name, type, sort_order) VALUES
-- Przychody
(NULL, 'Dotacja z gminy', 'income', 1),
(NULL, 'Składki członkowskie', 'income', 2),
(NULL, 'Darowizny', 'income', 3),
(NULL, 'Wynajem remizy', 'income', 4),
(NULL, '1,5% podatku', 'income', 5),
(NULL, 'Inne przychody', 'income', 99),
-- Koszty
(NULL, 'Paliwo', 'expense', 1),
(NULL, 'Przeglądy i naprawy pojazdów', 'expense', 2),
(NULL, 'Zakup sprzętu', 'expense', 3),
(NULL, 'Szkolenia', 'expense', 4),
(NULL, 'Ubezpieczenia', 'expense', 5),
(NULL, 'Media i utrzymanie remizy', 'expense', 6),
(NULL, 'Umundurowanie', 'expense', 7),
(NULL, 'Inne koszty', 'expense', 99);
```

---

## Triggery i funkcje

### Automatyczna aktualizacja updated_at
```sql
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplikuj do wszystkich tabel z updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_persons_updated_at BEFORE UPDATE ON persons
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_tenants_updated_at BEFORE UPDATE ON tenants
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_members_updated_at BEFORE UPDATE ON members
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_membership_fees_updated_at BEFORE UPDATE ON membership_fees
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_member_equipment_updated_at BEFORE UPDATE ON member_equipment
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_financial_records_updated_at BEFORE UPDATE ON financial_records
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_fee_settings_updated_at BEFORE UPDATE ON fee_settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

---

## Constrainty biznesowe

```sql
-- Członek zmarły musi mieć datę śmierci w persons
-- (walidacja na poziomie aplikacji, nie bazy - elastyczność)

-- Członek z leave_date nie może mieć nowych składek
-- (walidacja na poziomie aplikacji)

-- Składka paid musi mieć paid_at
ALTER TABLE membership_fees
ADD CONSTRAINT chk_paid_has_date
CHECK (status != 'paid' OR paid_at IS NOT NULL);
```

---

## Przygotowanie na RLS (post-MVP)

```sql
-- Włączenie RLS na tabelach tenant
ALTER TABLE members ENABLE ROW LEVEL SECURITY;
ALTER TABLE membership_fees ENABLE ROW LEVEL SECURITY;
ALTER TABLE member_equipment ENABLE ROW LEVEL SECURITY;
ALTER TABLE financial_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE fee_settings ENABLE ROW LEVEL SECURITY;

-- Przykładowa polityka (do aktywacji przy multi-tenant)
-- CREATE POLICY tenant_isolation ON members
--     USING (tenant_id = current_setting('app.current_tenant_id')::INT);
```

---

## Mapowanie na encje Doctrine

| Tabela PostgreSQL | Encja Doctrine | Uwagi |
|-------------------|----------------|-------|
| users | User | Symfony Security |
| persons | Person | Globalna tożsamość |
| tenants | Tenant | Jednostka OSP |
| user_tenants | UserTenant | ManyToMany z atrybutami |
| members | Member | Członek per tenant |
| decoration_types | DecorationType | Słownik globalny |
| person_decorations | PersonDecoration | Odznaczenia |
| equipment_types | EquipmentType | Słownik (global + tenant) |
| member_equipment | MemberEquipment | Wyposażenie |
| fee_settings | FeeSettings | Ustawienia składek |
| membership_fees | MembershipFee | Składki |
| financial_categories | FinancialCategory | Kategorie (global + tenant) |
| financial_records | FinancialRecord | Rekordy finansowe |

---

## Podsumowanie tabel

| Warstwa | Tabela | Opis |
|---------|--------|------|
| Globalna | users | Konta użytkowników |
| Globalna | persons | Tożsamość (PESEL) |
| Globalna | decoration_types | Słownik odznaczeń |
| Globalna | person_decorations | Przypisane odznaczenia |
| Tenant | tenants | Jednostki OSP |
| Tenant | user_tenants | User ↔ Tenant (role) |
| Tenant | members | Członkowie jednostki |
| Tenant | fee_settings | Ustawienia składek |
| Tenant | membership_fees | Składki członkowskie |
| Tenant | equipment_types | Typy wyposażenia |
| Tenant | member_equipment | Przypisane wyposażenie |
| Tenant | financial_categories | Kategorie finansowe |
| Tenant | financial_records | Rekordy finansowe |

**Łącznie: 13 tabel**