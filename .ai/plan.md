# Plan implementacji POC osp.plus

## Cel
POC na zaliczenie kursu - 3 dni robocze.

## Wymagania zaliczeniowe
- [ ] Auth (login/logout + JWT)
- [ ] CRUD (ewidencja czlonkow)
- [ ] Logika biznesowa (walidacja skladek - oznaczanie zaleglych)
- [ ] Unit test (dla walidacji skladek)
- [ ] CI/CD (GitHub Actions)

## Stack technologiczny
- Backend: Symfony 7 + API Platform
- Frontend: React (Vite)
- Baza: PostgreSQL
- Auth: lexik/jwt-authentication-bundle
- Dev env: DDEV

---

## Struktura projektu

```
osp_plus/
├── backend/                    # Symfony 7 API
│   ├── src/
│   │   ├── Entity/
│   │   │   ├── Member.php
│   │   │   ├── MembershipFee.php
│   │   │   └── User.php
│   │   ├── Enum/
│   │   │   ├── MembershipStatus.php
│   │   │   └── FeeStatus.php
│   │   ├── Repository/
│   │   ├── Service/
│   │   │   └── MembershipFeeValidationService.php
│   │   └── Controller/
│   │       └── MembershipFeeController.php
│   ├── tests/Unit/Service/
│   │   └── MembershipFeeValidationServiceTest.php
│   └── config/packages/
│       ├── security.yaml
│       ├── lexik_jwt_authentication.yaml
│       └── api_platform.yaml
├── frontend/                   # React SPA
│   └── src/
│       ├── components/
│       │   ├── LoginForm.jsx
│       │   ├── MemberList.jsx
│       │   ├── MemberForm.jsx
│       │   └── FeeList.jsx
│       ├── services/api.js
│       └── context/AuthContext.jsx
├── .ddev/config.yaml
└── .github/workflows/ci.yml
```

---

## Model danych

### Member (Czlonek)
| Pole | Typ | Wymagane |
|------|-----|----------|
| id | int | auto |
| firstName | string(100) | tak |
| lastName | string(100) | tak |
| pesel | string(11) | tak, unique |
| address | text | nie |
| phone | string(20) | nie |
| email | string(255) | nie |
| birthDate | date | tak |
| joinDate | date | tak |
| deathDate | date | nie |
| membershipStatus | enum | tak |
| boardPosition | string(100) | nie |

### MembershipStatus (enum)
- active, inactive, honorary, supporting, youth, removed, deceased

### MembershipFee (Skladka)
| Pole | Typ | Wymagane |
|------|-----|----------|
| id | int | auto |
| member_id | FK | tak |
| year | int | tak |
| amount | decimal(10,2) | tak |
| status | enum | tak |
| paidAt | date | nie |

### FeeStatus (enum)
- unpaid, paid, overdue, exempt, not_applicable

### User (do auth)
| Pole | Typ |
|------|-----|
| id | int |
| email | string, unique |
| password | string |
| roles | json |

---

## Logika biznesowa - walidacja skladek

**Regula:** Skladka jest "zalegla" jesli:
- status = UNPAID
- aktualna data > 31 marca danego roku

**Wyjatki (nie podlegaja walidacji):**
- status = EXEMPT (zwolniony)
- status = NOT_APPLICABLE (nie dotyczy)

**Metody MembershipFeeValidationService:**
- `isOverdue(fee, referenceDate)` - sprawdza czy skladka jest przeterminowana
- `markAsOverdueIfApplicable(fee)` - oznacza jesli spelnia warunki
- `validateAndMarkAllOverdue()` - procesuje wszystkie nieoplacone
- `getOverdueFees()` - zwraca liste zaleglych

---

## Endpointy API

### Auth
| Metoda | Endpoint | Opis |
|--------|----------|------|
| POST | /api/login_check | Logowanie, zwraca JWT |

### Members (API Platform auto)
| Metoda | Endpoint | Auth |
|--------|----------|------|
| GET | /api/members | JWT |
| GET | /api/members/{id} | JWT |
| POST | /api/members | JWT |
| PUT | /api/members/{id} | JWT |
| DELETE | /api/members/{id} | JWT+ADMIN |

### Fees (API Platform auto)
| Metoda | Endpoint | Auth |
|--------|----------|------|
| GET | /api/membership_fees | JWT |
| POST | /api/membership_fees | JWT |
| PUT | /api/membership_fees/{id} | JWT |

### Custom (MembershipFeeController)
| Metoda | Endpoint | Opis |
|--------|----------|------|
| POST | /api/membership-fees/validate-overdue | Oznacz zaległe |
| GET | /api/membership-fees/overdue | Lista zaleglych |

---

## Harmonogram - 3 dni

### DZIEN 1 - Backend (8h)
1. Setup projektu (1h)
   - `symfony new backend --webapp`
   - DDEV init
   - composer require: api-platform, lexik/jwt, nelmio/cors

2. Konfiguracja (1h)
   - security.yaml (JWT firewall)
   - lexik_jwt_authentication.yaml
   - nelmio_cors.yaml
   - .env (DATABASE_URL)

3. Encje + migracje (2h)
   - Member.php z ApiResource
   - MembershipFee.php z ApiResource
   - User.php
   - Enumy (MembershipStatus, FeeStatus)
   - `php bin/console doctrine:migrations:migrate`

4. Logika biznesowa (2h)
   - MembershipFeeValidationService.php
   - MembershipFeeController.php

5. Fixtures + test manualny (2h)
   - DataFixtures z przykladowymi danymi
   - Test przez curl/Postman

### DZIEN 2 - Testy + Frontend (8h)
1. Unit testy (2h)
   - MembershipFeeValidationServiceTest.php
   - ~12 test cases

2. Frontend setup (1h)
   - `npm create vite@latest frontend -- --template react`
   - `npm install react-router-dom`

3. Komponenty React (5h)
   - api.js (serwis API)
   - AuthContext.jsx
   - LoginForm.jsx
   - MemberList.jsx
   - MemberForm.jsx
   - FeeList.jsx (z przyciskiem walidacji)
   - App.jsx (routing)
   - Minimalne CSS

### DZIEN 3 - CI/CD + Deploy (8h)
1. GitHub Actions (2h)
   - .github/workflows/ci.yml
   - Job: backend-tests (PHPUnit)
   - Job: frontend-build

2. Deploy (4h)
   - Docker prod config
   - Deploy na serwer/hosting
   - SSL, subdomena

3. Polish (2h)
   - Testy E2E manualne
   - Bug fixes
   - README.md

---

## Krytyczne pliki do implementacji

1. `backend/src/Service/MembershipFeeValidationService.php` - logika biznesowa
2. `backend/src/Entity/Member.php` - CRUD encja
3. `backend/tests/Unit/Service/MembershipFeeValidationServiceTest.php` - unit test
4. `backend/config/packages/security.yaml` - JWT auth
5. `.github/workflows/ci.yml` - CI/CD

---

## Komendy startowe

```bash
# Backend
cd osp_plus
mkdir backend frontend
cd backend
composer create-project symfony/skeleton:"7.0.*" .
composer require api
composer require symfony/orm-pack
composer require lexik/jwt-authentication-bundle
composer require nelmio/cors-bundle
composer require --dev phpunit/phpunit symfony/test-pack
php bin/console lexik:jwt:generate-keypair

# Frontend
cd ../frontend
npm create vite@latest . -- --template react
npm install react-router-dom

# DDEV
cd ..
ddev config --project-type=php --php-version=8.3 --database=postgres:16
ddev start
```

---

## Weryfikacja

Po implementacji sprawdz:

1. **Auth:** `curl -X POST http://localhost:8000/api/login_check -H "Content-Type: application/json" -d '{"email":"admin@osp.plus","password":"admin123"}'`

2. **CRUD Members:** Dodaj/edytuj/usun czlonka przez frontend

3. **Logika biznesowa:** Kliknij "Oznacz zaległe skladki" - skladki z ubieglych lat powinny zmienic status na "overdue"

4. **Testy:** `cd backend && vendor/bin/phpunit --testdox`

5. **CI/CD:** Push do GitHub -> Actions powinny przejsc