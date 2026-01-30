# Schemat bazy danych PostgreSQL - osp.plus

## 1. Tabele

### 1.1 Warstwa globalna

#### users
Konta użytkowników systemu (autentykacja).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Adres email (login) |
| password | VARCHAR(255) | NOT NULL | Hash hasła (bcrypt/argon2) |
| is_superadmin | BOOLEAN | NOT NULL DEFAULT FALSE | Dostęp do wszystkich tenantów |
| is_active | BOOLEAN | NOT NULL DEFAULT TRUE | Czy konto aktywne |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |

#### persons
Globalna tożsamość osoby (identyfikacja przez PESEL).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| pesel | VARCHAR(11) | NOT NULL, UNIQUE | Numer PESEL |
| first_name | VARCHAR(100) | NOT NULL | Imię |
| last_name | VARCHAR(100) | NOT NULL | Nazwisko |
| birth_date | DATE | NOT NULL | Data urodzenia |
| death_date | DATE | NULL | Data śmierci |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |

#### decoration_types
Słownik typów odznaczeń (globalny).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| name | VARCHAR(255) | NOT NULL | Nazwa odznaczenia |
| category | decoration_category | NOT NULL | Kategoria (ENUM) |
| required_years | INT | NULL | Wymagany staż (lata) |
| description | TEXT | NULL | Opis |
| sort_order | INT | NOT NULL DEFAULT 0 | Kolejność wyświetlania |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |

**ENUM decoration_category:**
`'osp'` | `'zosp'` | `'state'` | `'municipal'` | `'other'`

#### person_decorations
Odznaczenia przypisane do osób (globalne).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| person_id | INT | NOT NULL, FK → persons | Osoba |
| decoration_type_id | INT | NOT NULL, FK → decoration_types | Typ odznaczenia |
| awarded_at | DATE | NOT NULL | Data nadania |
| awarded_by_level | awarded_by_level | NOT NULL | Poziom nadania (ENUM) |
| awarded_by_name | VARCHAR(255) | NULL | Nazwa instytucji nadającej |
| certificate_number | VARCHAR(100) | NULL | Numer legitymacji |
| notes | TEXT | NULL | Uwagi |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| created_by | INT | NULL, FK → users | Kto utworzył |

**ENUM awarded_by_level:**
`'unit'` | `'municipal'` | `'district'` | `'voivodeship'` | `'national'`

**UNIQUE:** (person_id, decoration_type_id) - jedna osoba nie może mieć dwa razy tego samego odznaczenia

---

### 1.2 Warstwa tenant (jednostka OSP)

#### tenants
Jednostki OSP (multi-tenant).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| name | VARCHAR(255) | NOT NULL | Nazwa jednostki |
| slug | VARCHAR(100) | NOT NULL, UNIQUE | Subdomena (np. "siolkowa") |
| address | TEXT | NULL | Adres siedziby |
| city | VARCHAR(100) | NULL | Miejscowość |
| postal_code | VARCHAR(10) | NULL | Kod pocztowy |
| phone | VARCHAR(20) | NULL | Telefon |
| email | VARCHAR(255) | NULL | Email jednostki |
| nip | VARCHAR(13) | NULL | NIP |
| regon | VARCHAR(14) | NULL | REGON |
| krs | VARCHAR(20) | NULL | KRS |
| is_active | BOOLEAN | NOT NULL DEFAULT TRUE | Czy jednostka aktywna |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |

#### user_tenants
Powiązanie użytkowników z jednostkami (role per tenant).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| user_id | INT | NOT NULL, FK → users | Użytkownik |
| tenant_id | INT | NOT NULL, FK → tenants | Jednostka |
| roles | JSONB | NOT NULL DEFAULT '["ROLE_USER"]' | Role w jednostce |
| is_active | BOOLEAN | NOT NULL DEFAULT TRUE | Czy aktywny w jednostce |
| joined_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data dołączenia |
| left_at | TIMESTAMP | NULL | Data opuszczenia |

**UNIQUE:** (user_id, tenant_id)

**Przykład roles:** `["ROLE_USER", "ROLE_SKARBNIK"]`

#### members
Członkowie jednostki OSP.

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| tenant_id | INT | NOT NULL, FK → tenants | Jednostka |
| person_id | INT | NOT NULL, FK → persons | Osoba (globalna tożsamość) |
| membership_status | membership_status | NOT NULL DEFAULT 'active' | Status członkostwa |
| join_date | DATE | NOT NULL | Data wstąpienia |
| leave_date | DATE | NULL | Data wystąpienia |
| address | TEXT | NULL | Adres (może różnić się od persons) |
| phone | VARCHAR(20) | NULL | Telefon kontaktowy |
| email | VARCHAR(255) | NULL | Email kontaktowy |
| board_position | VARCHAR(100) | NULL | Funkcja w zarządzie |
| notes | TEXT | NULL | Uwagi |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |
| created_by | INT | NULL, FK → users | Kto utworzył |
| updated_by | INT | NULL, FK → users | Kto zmodyfikował |

**ENUM membership_status:**
`'active'` | `'inactive'` | `'honorary'` | `'supporting'` | `'youth'` | `'removed'` | `'deceased'`

**UNIQUE:** (tenant_id, person_id) - jedna osoba może być członkiem jednostki tylko raz

#### fee_settings
Ustawienia składek członkowskich per tenant/rok.

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| tenant_id | INT | NOT NULL, FK → tenants | Jednostka |
| year | INT | NOT NULL | Rok |
| annual_fee_amount | DECIMAL(10,2) | NOT NULL | Kwota składki rocznej |
| payment_deadline | DATE | NOT NULL DEFAULT '03-31' | Termin płatności |
| youth_pays_fee | BOOLEAN | NOT NULL DEFAULT FALSE | Czy MDP płaci |
| honorary_pays_fee | BOOLEAN | NOT NULL DEFAULT FALSE | Czy honorowi płacą |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |

**UNIQUE:** (tenant_id, year)

#### membership_fees
Składki członkowskie.

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| member_id | INT | NOT NULL, FK → members | Członek |
| year | INT | NOT NULL | Rok składki |
| amount | DECIMAL(10,2) | NOT NULL | Kwota |
| status | fee_status | NOT NULL DEFAULT 'unpaid' | Status płatności |
| is_custom_amount | BOOLEAN | NOT NULL DEFAULT FALSE | Czy kwota indywidualna |
| paid_at | DATE | NULL | Data opłacenia |
| notes | TEXT | NULL | Uwagi |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |
| created_by | INT | NULL, FK → users | Kto utworzył |
| updated_by | INT | NULL, FK → users | Kto zmodyfikował |

**ENUM fee_status:**
`'unpaid'` | `'paid'` | `'overdue'` | `'exempt'` | `'not_applicable'`

**UNIQUE:** (member_id, year)

**CHECK:** status != 'paid' OR paid_at IS NOT NULL

---

### 1.3 Wyposażenie

#### equipment_types
Typy wyposażenia (globalne + per tenant).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| tenant_id | INT | NULL, FK → tenants | NULL = globalny, wartość = lokalny |
| name | VARCHAR(255) | NOT NULL | Nazwa typu |
| category | equipment_category | NOT NULL | Kategoria |
| has_sizes | BOOLEAN | NOT NULL DEFAULT FALSE | Czy ma rozmiary |
| description | TEXT | NULL | Opis |
| sort_order | INT | NOT NULL DEFAULT 0 | Kolejność |
| is_active | BOOLEAN | NOT NULL DEFAULT TRUE | Czy aktywny |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |

**ENUM equipment_category:**
`'clothing'` | `'protective'` | `'tools'` | `'other'`

#### member_equipment
Wyposażenie przypisane do członków.

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| member_id | INT | NOT NULL, FK → members | Członek |
| equipment_type_id | INT | NOT NULL, FK → equipment_types | Typ wyposażenia |
| size | VARCHAR(20) | NULL | Rozmiar |
| serial_number | VARCHAR(100) | NULL | Numer seryjny |
| issued_at | DATE | NOT NULL | Data wydania |
| returned_at | DATE | NULL | Data zwrotu |
| condition | VARCHAR(50) | NULL | Stan techniczny |
| notes | TEXT | NULL | Uwagi |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |
| created_by | INT | NULL, FK → users | Kto utworzył |
| updated_by | INT | NULL, FK → users | Kto zmodyfikował |

---

### 1.4 Finanse

#### financial_categories
Kategorie finansowe (globalne + per tenant).

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| tenant_id | INT | NULL, FK → tenants | NULL = globalna, wartość = lokalna |
| name | VARCHAR(255) | NOT NULL | Nazwa kategorii |
| type | financial_type | NOT NULL | Typ (przychód/koszt) |
| sort_order | INT | NOT NULL DEFAULT 0 | Kolejność |
| is_active | BOOLEAN | NOT NULL DEFAULT TRUE | Czy aktywna |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |

**ENUM financial_type:**
`'income'` | `'expense'`

#### financial_records
Rekordy finansowe.

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|--------------|------|
| id | SERIAL | PRIMARY KEY | Identyfikator |
| tenant_id | INT | NOT NULL, FK → tenants | Jednostka |
| category_id | INT | NOT NULL, FK → financial_categories | Kategoria |
| type | financial_type | NOT NULL | Typ (redundancja dla wydajności) |
| amount | DECIMAL(10,2) | NOT NULL | Kwota |
| description | TEXT | NOT NULL | Opis operacji |
| document_number | VARCHAR(100) | NULL | Numer dokumentu |
| recorded_at | DATE | NOT NULL | Data operacji |
| member_id | INT | NULL, FK → members | Powiązany członek (opcjonalne) |
| notes | TEXT | NULL | Uwagi |
| created_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data utworzenia |
| updated_at | TIMESTAMP | NOT NULL DEFAULT NOW() | Data modyfikacji |
| created_by | INT | NULL, FK → users | Kto utworzył |
| updated_by | INT | NULL, FK → users | Kto zmodyfikował |

---

## 2. Relacje między tabelami

```
users (1) ←──────────────────────────────────────────┐
  │                                                   │
  │ 1:N                                               │
  ▼                                                   │
user_tenants (N) ───── N:1 ────► tenants (1)         │
                                    │                 │
                                    │ 1:N             │
                    ┌───────────────┼───────────────┐ │
                    │               │               │ │
                    ▼               ▼               ▼ │
               members (N)    fee_settings (N)  financial_records (N)
                    │               │                 │
                    │ N:1           │                 │
                    ▼               │                 │
               persons (1) ◄───────┘                 │
                    │                                 │
                    │ 1:N                             │
                    ▼                                 │
           person_decorations (N)                    │
                    │                                 │
                    │ N:1                             │
                    ▼                                 │
           decoration_types (1)                      │
                                                      │
members (1) ──── 1:N ────► membership_fees (N)       │
                                                      │
members (1) ──── 1:N ────► member_equipment (N)      │
                                  │                   │
                                  │ N:1               │
                                  ▼                   │
                          equipment_types (1)        │
                                                      │
financial_records (N) ──── N:1 ────► financial_categories (1)
                                                      │
                          (audit: created_by, updated_by) ──────────┘
```

### Kardynalności

| Relacja | Kardynalność | Opis |
|---------|--------------|------|
| users → user_tenants | 1:N | User może być w wielu jednostkach |
| tenants → user_tenants | 1:N | Jednostka może mieć wielu userów |
| tenants → members | 1:N | Jednostka ma wielu członków |
| persons → members | 1:N | Osoba może być członkiem wielu jednostek |
| persons → person_decorations | 1:N | Osoba może mieć wiele odznaczeń |
| decoration_types → person_decorations | 1:N | Typ może być nadany wielu osobom |
| tenants → fee_settings | 1:N | Jednostka ma ustawienia per rok |
| members → membership_fees | 1:N | Członek ma składki per rok |
| members → member_equipment | 1:N | Członek może mieć wiele wyposażenia |
| equipment_types → member_equipment | 1:N | Typ może być przypisany wielu członkom |
| tenants → financial_records | 1:N | Jednostka ma wiele rekordów |
| financial_categories → financial_records | 1:N | Kategoria grupuje rekordy |
| members → financial_records | 1:N (opcjonalne) | Rekord może być powiązany z członkiem |

---

## 3. Indeksy

### Indeksy podstawowe (PRIMARY KEY)
Tworzone automatycznie dla wszystkich kolumn `id`.

### Indeksy unikalne (UNIQUE)
```sql
-- users
CREATE UNIQUE INDEX idx_users_email ON users(email);

-- persons
CREATE UNIQUE INDEX idx_persons_pesel ON persons(pesel);

-- tenants
CREATE UNIQUE INDEX idx_tenants_slug ON tenants(slug);

-- user_tenants
CREATE UNIQUE INDEX idx_user_tenants_unique ON user_tenants(user_id, tenant_id);

-- members
CREATE UNIQUE INDEX idx_members_tenant_person ON members(tenant_id, person_id);

-- fee_settings
CREATE UNIQUE INDEX idx_fee_settings_tenant_year ON fee_settings(tenant_id, year);

-- membership_fees
CREATE UNIQUE INDEX idx_membership_fees_member_year ON membership_fees(member_id, year);

-- person_decorations
CREATE UNIQUE INDEX idx_person_decorations_unique ON person_decorations(person_id, decoration_type_id);
```

### Indeksy wydajnościowe
```sql
-- Wyszukiwanie osób
CREATE INDEX idx_persons_name ON persons(last_name, first_name);

-- Filtrowanie członków
CREATE INDEX idx_members_tenant ON members(tenant_id);
CREATE INDEX idx_members_status ON members(tenant_id, membership_status);
CREATE INDEX idx_members_person ON members(person_id);

-- Filtrowanie składek
CREATE INDEX idx_membership_fees_status ON membership_fees(status);
CREATE INDEX idx_membership_fees_year ON membership_fees(year);

-- Wyszukiwanie wyposażenia
CREATE INDEX idx_member_equipment_member ON member_equipment(member_id);
CREATE INDEX idx_member_equipment_type ON member_equipment(equipment_type_id);

-- Filtrowanie finansów
CREATE INDEX idx_financial_records_tenant ON financial_records(tenant_id);
CREATE INDEX idx_financial_records_date ON financial_records(recorded_at);
CREATE INDEX idx_financial_records_type ON financial_records(type);
CREATE INDEX idx_financial_records_category ON financial_records(category_id);

-- Słowniki per tenant
CREATE INDEX idx_equipment_types_tenant ON equipment_types(tenant_id);
CREATE INDEX idx_financial_categories_tenant ON financial_categories(tenant_id);

-- User tenants
CREATE INDEX idx_user_tenants_user ON user_tenants(user_id);
CREATE INDEX idx_user_tenants_tenant ON user_tenants(tenant_id);

-- Odznaczenia
CREATE INDEX idx_person_decorations_person ON person_decorations(person_id);
```

---

## 4. Typy ENUM PostgreSQL

```sql
-- Kategorie odznaczeń
CREATE TYPE decoration_category AS ENUM (
    'osp',        -- Odznaczenia OSP
    'zosp',       -- Odznaczenia ZOSP RP
    'state',      -- Odznaczenia państwowe
    'municipal',  -- Odznaczenia samorządowe
    'other'       -- Inne
);

-- Poziom nadania odznaczenia
CREATE TYPE awarded_by_level AS ENUM (
    'unit',        -- Jednostka OSP
    'municipal',   -- Gmina / Związek gminny
    'district',    -- Powiat
    'voivodeship', -- Województwo
    'national'     -- Poziom krajowy
);

-- Status członkostwa
CREATE TYPE membership_status AS ENUM (
    'active',      -- Aktywny
    'inactive',    -- Nieaktywny
    'honorary',    -- Honorowy
    'supporting',  -- Wspierający
    'youth',       -- MDP (młodzież)
    'removed',     -- Skreślony
    'deceased'     -- Zmarły
);

-- Status składki
CREATE TYPE fee_status AS ENUM (
    'unpaid',         -- Nieopłacona
    'paid',           -- Opłacona
    'overdue',        -- Zaległa
    'exempt',         -- Zwolniony
    'not_applicable'  -- Nie dotyczy
);

-- Kategoria wyposażenia
CREATE TYPE equipment_category AS ENUM (
    'clothing',    -- Odzież
    'protective',  -- Ochronne
    'tools',       -- Narzędzia
    'other'        -- Inne
);

-- Typ finansowy
CREATE TYPE financial_type AS ENUM (
    'income',   -- Przychód
    'expense'   -- Koszt
);
```

---

## 5. Ograniczenia (Constraints)

```sql
-- Składka opłacona musi mieć datę płatności
ALTER TABLE membership_fees
ADD CONSTRAINT chk_paid_has_date
CHECK (status != 'paid' OR paid_at IS NOT NULL);

-- Kwota składki musi być dodatnia
ALTER TABLE membership_fees
ADD CONSTRAINT chk_fee_amount_positive
CHECK (amount >= 0);

-- Kwota rekordu finansowego musi być dodatnia
ALTER TABLE financial_records
ADD CONSTRAINT chk_financial_amount_positive
CHECK (amount >= 0);

-- Data wystąpienia nie może być przed datą wstąpienia
ALTER TABLE members
ADD CONSTRAINT chk_leave_after_join
CHECK (leave_date IS NULL OR leave_date >= join_date);

-- Data zwrotu wyposażenia nie może być przed datą wydania
ALTER TABLE member_equipment
ADD CONSTRAINT chk_return_after_issue
CHECK (returned_at IS NULL OR returned_at >= issued_at);

-- Rok składki musi być rozsądny
ALTER TABLE membership_fees
ADD CONSTRAINT chk_fee_year_range
CHECK (year >= 1900 AND year <= 2100);

-- Rok ustawień musi być rozsądny
ALTER TABLE fee_settings
ADD CONSTRAINT chk_settings_year_range
CHECK (year >= 1900 AND year <= 2100);
```

---

## 6. Triggery i funkcje

### Automatyczna aktualizacja updated_at
```sql
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplikacja triggerów
CREATE TRIGGER trg_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_persons_updated_at
    BEFORE UPDATE ON persons
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_tenants_updated_at
    BEFORE UPDATE ON tenants
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_members_updated_at
    BEFORE UPDATE ON members
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_membership_fees_updated_at
    BEFORE UPDATE ON membership_fees
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_member_equipment_updated_at
    BEFORE UPDATE ON member_equipment
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_financial_records_updated_at
    BEFORE UPDATE ON financial_records
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_fee_settings_updated_at
    BEFORE UPDATE ON fee_settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

---

## 7. Row Level Security (RLS) - przygotowanie na multi-tenant

```sql
-- Włączenie RLS na tabelach tenant-scoped
ALTER TABLE members ENABLE ROW LEVEL SECURITY;
ALTER TABLE membership_fees ENABLE ROW LEVEL SECURITY;
ALTER TABLE member_equipment ENABLE ROW LEVEL SECURITY;
ALTER TABLE financial_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE fee_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_tenants ENABLE ROW LEVEL SECURITY;

-- Polityki do aktywacji przy multi-tenant
-- (zakomentowane w MVP - single-tenant)

/*
-- Funkcja pomocnicza
CREATE OR REPLACE FUNCTION current_tenant_id()
RETURNS INT AS $$
BEGIN
    RETURN current_setting('app.current_tenant_id', TRUE)::INT;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Polityka dla members
CREATE POLICY tenant_isolation_members ON members
    USING (tenant_id = current_tenant_id());

-- Polityka dla membership_fees (przez member)
CREATE POLICY tenant_isolation_fees ON membership_fees
    USING (member_id IN (
        SELECT id FROM members WHERE tenant_id = current_tenant_id()
    ));

-- Polityka dla member_equipment (przez member)
CREATE POLICY tenant_isolation_equipment ON member_equipment
    USING (member_id IN (
        SELECT id FROM members WHERE tenant_id = current_tenant_id()
    ));

-- Polityka dla financial_records
CREATE POLICY tenant_isolation_financial ON financial_records
    USING (tenant_id = current_tenant_id());

-- Polityka dla fee_settings
CREATE POLICY tenant_isolation_fee_settings ON fee_settings
    USING (tenant_id = current_tenant_id());

-- Polityka dla user_tenants (user widzi swoje przypisania)
CREATE POLICY user_sees_own_tenants ON user_tenants
    USING (user_id = current_setting('app.current_user_id', TRUE)::INT);
*/
```

---

## 8. Dane początkowe (Seeds)

### decoration_types
```sql
INSERT INTO decoration_types (name, category, required_years, sort_order) VALUES
-- Odznaczenia za wysługę lat
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
-- Odznaczenia OSP
('Odznaka "Strażak Wzorowy"', 'osp', NULL, 1),
('Odznaka "Młodzieżowa Drużyna Pożarnicza"', 'osp', NULL, 2),
-- Odznaczenia ZOSP RP
('Medal "Za Zasługi dla Pożarnictwa" - brązowy', 'zosp', NULL, 20),
('Medal "Za Zasługi dla Pożarnictwa" - srebrny', 'zosp', NULL, 21),
('Medal "Za Zasługi dla Pożarnictwa" - złoty', 'zosp', NULL, 22),
('Złoty Znak Związku OSP RP', 'zosp', NULL, 30),
('Srebrny Znak Związku OSP RP', 'zosp', NULL, 31),
-- Odznaczenia państwowe
('Medal za Długoletnią Służbę - brązowy', 'state', 10, 40),
('Medal za Długoletnią Służbę - srebrny', 'state', 20, 41),
('Medal za Długoletnią Służbę - złoty', 'state', 30, 42);
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
(NULL, 'Latarka osobista', 'tools', FALSE, 8),
(NULL, 'Aparat ODO', 'protective', FALSE, 9),
(NULL, 'Maska do aparatu ODO', 'protective', TRUE, 10);
```

### financial_categories (globalne)
```sql
INSERT INTO financial_categories (tenant_id, name, type, sort_order) VALUES
-- Przychody
(NULL, 'Dotacja z budżetu gminy', 'income', 1),
(NULL, 'Dotacja z budżetu powiatu', 'income', 2),
(NULL, 'Dotacja z budżetu województwa', 'income', 3),
(NULL, 'Dotacja z MSWiA / KSRG', 'income', 4),
(NULL, 'Składki członkowskie', 'income', 10),
(NULL, 'Darowizny od osób fizycznych', 'income', 11),
(NULL, 'Darowizny od firm', 'income', 12),
(NULL, 'Wynajem remizy', 'income', 20),
(NULL, '1,5% podatku (OPP)', 'income', 21),
(NULL, 'Inne przychody', 'income', 99),
-- Koszty
(NULL, 'Paliwo do pojazdów', 'expense', 1),
(NULL, 'Przeglądy techniczne pojazdów', 'expense', 2),
(NULL, 'Naprawy pojazdów', 'expense', 3),
(NULL, 'Zakup sprzętu ratowniczego', 'expense', 10),
(NULL, 'Zakup umundurowania', 'expense', 11),
(NULL, 'Szkolenia i kursy', 'expense', 20),
(NULL, 'Ubezpieczenia', 'expense', 21),
(NULL, 'Media (prąd, gaz, woda)', 'expense', 30),
(NULL, 'Utrzymanie remizy', 'expense', 31),
(NULL, 'Materiały biurowe', 'expense', 40),
(NULL, 'Inne koszty', 'expense', 99);
```

---

## 9. Mapowanie na encje Doctrine

| Tabela PostgreSQL | Encja Doctrine | Repozytorium |
|-------------------|----------------|--------------|
| users | App\Entity\User | UserRepository |
| persons | App\Entity\Person | PersonRepository |
| tenants | App\Entity\Tenant | TenantRepository |
| user_tenants | App\Entity\UserTenant | UserTenantRepository |
| members | App\Entity\Member | MemberRepository |
| decoration_types | App\Entity\DecorationType | DecorationTypeRepository |
| person_decorations | App\Entity\PersonDecoration | PersonDecorationRepository |
| equipment_types | App\Entity\EquipmentType | EquipmentTypeRepository |
| member_equipment | App\Entity\MemberEquipment | MemberEquipmentRepository |
| fee_settings | App\Entity\FeeSettings | FeeSettingsRepository |
| membership_fees | App\Entity\MembershipFee | MembershipFeeRepository |
| financial_categories | App\Entity\FinancialCategory | FinancialCategoryRepository |
| financial_records | App\Entity\FinancialRecord | FinancialRecordRepository |

---

## 10. Podsumowanie

### Liczba tabel: 13

| Warstwa | Tabele | Ilość |
|---------|--------|-------|
| Globalna | users, persons, decoration_types, person_decorations | 4 |
| Tenant | tenants, user_tenants, members, fee_settings, membership_fees | 5 |
| Wyposażenie | equipment_types, member_equipment | 2 |
| Finanse | financial_categories, financial_records | 2 |

### Typy ENUM: 6
- decoration_category
- awarded_by_level
- membership_status
- fee_status
- equipment_category
- financial_type

### Kluczowe decyzje architektoniczne

1. **Globalna tożsamość przez `persons`** - PESEL jako unikalny identyfikator osoby, odznaczenia przypisane globalnie
2. **Multi-tenant ready** - wszystkie tabele tenant-scoped mają `tenant_id`, RLS przygotowane
3. **Słowniki hybrydowe** - globalne + lokalne per tenant dla equipment_types i financial_categories
4. **Audit trail prosty** - created_at, updated_at, created_by, updated_by
5. **Role per tenant** - JSONB w user_tenants zamiast osobnej tabeli
6. **Soft delete przez status** - brak fizycznego usuwania członków