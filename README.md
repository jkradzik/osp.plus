# osp.plus

Centrum operacyjne dla Ochotniczych Strazy Pozarnych.

## O projekcie

**osp.plus** to platforma SaaS do zarzadzania jednostkami OSP. System integruje wszystkie aspekty funkcjonowania jednostki - od ewidencji czlonkow, przez dokumentacje akcji ratowniczych, po zarzadzanie finansami i wynajmem remizy.

### Problem

Jednostki OSP zmagaja sie z rosnacym obciazeniem administracyjnym. Dane o czlonkach, szkoleniach, skladkach i majatku sa rozproszone miedzy Excelami, zeszytami i wiedza kilku osob z zarzadu. Brak jednego zrodla prawdy prowadzi do utraty informacji przy zmianach w zarzadzie i trudnosci w generowaniu raportow.

### Rozwiazanie

Jedna aplikacja, ktora laczy wszystko:
- Ewidencja czlonkow z przypomnieniami o badaniach i szkoleniach
- Rozliczanie skladek czlonkowskich
- Ewidencja odznaczen i wyposazenia osobistego
- Dokumentacja akcji ratowniczych
- Zarzadzanie finansami jednostki
- Kalendarz wynajmu remizy (wyroznik konkurencyjny)

## Tech Stack

| Warstwa | Technologia |
|---------|-------------|
| Backend | PHP 8.3, Symfony 7, API Platform 3 |
| Frontend | React 18, Vite 5, TypeScript |
| Baza danych | PostgreSQL 16 |
| Auth | JWT (lexik/jwt-authentication-bundle) |
| Dev environment | DDEV, Docker |
| CI/CD | GitHub Actions |

### Architektura

```
┌─────────────────────────────────────────┐
│           Frontend (React)              │
│   Admin Panel / Public Pages / Mobile   │
└───────────────┬─────────────────────────┘
                │ REST API (JSON)
                ▼
┌─────────────────────────────────────────┐
│      Backend (Symfony + API Platform)   │
│         JWT Authentication              │
└───────────────┬─────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────┐
│           PostgreSQL 16                 │
└─────────────────────────────────────────┘
```

**Kluczowe zasady:**
- API-first - backend to czyste REST API
- JWT stateless - brak sesji na serwerze
- Single source of truth - jeden backend dla web i mobile

## Wymagania

- PHP 8.3+
- Node.js 20+
- PostgreSQL 16+
- Composer 2+
- DDEV (zalecane) lub Docker

## Instalacja

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/your-username/osp-plus.git
cd osp-plus
```

### 2. Uruchomienie przez DDEV (zalecane)

```bash
ddev start
ddev composer install -d backend
ddev exec -d backend php bin/console lexik:jwt:generate-keypair
ddev exec -d backend php bin/console doctrine:migrations:migrate --no-interaction
ddev exec -d backend php bin/console doctrine:fixtures:load --no-interaction
cd frontend && npm install && npm run dev
```

### 3. Alternatywnie - Docker Compose

```bash
docker-compose up -d
cd backend && composer install
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate
cd ../frontend && npm install && npm run dev
```

## Struktura projektu

```
osp_plus/
├── backend/                 # Symfony 7 API
│   ├── src/
│   │   ├── Entity/          # Doctrine entities
│   │   ├── Enum/            # PHP enums
│   │   ├── Repository/      # Doctrine repositories
│   │   ├── Service/         # Business logic
│   │   └── Controller/      # Custom API endpoints
│   ├── config/              # Symfony configuration
│   └── tests/               # PHPUnit tests
├── frontend/                # React SPA
│   └── src/
│       ├── components/      # React components
│       ├── pages/           # Page components
│       ├── services/        # API client
│       └── context/         # React context (auth)
├── .ai/                     # Project documentation
│   ├── prd.md               # Product Requirements
│   ├── tech-stack.md        # Tech stack details
│   ├── plan.md              # Implementation plan
│   ├── faza-poc.md          # POC phase details
│   └── faza-mvp.md          # MVP phase details
└── .github/
    └── workflows/
        └── ci.yml           # GitHub Actions
```

## Uruchomienie

### Backend (API)

```bash
cd backend
symfony server:start
# lub przez DDEV
ddev exec -d backend symfony server:start
```

API dostepne pod: `http://localhost:8000/api`

Dokumentacja API (OpenAPI): `http://localhost:8000/api/docs`

### Frontend

```bash
cd frontend
npm run dev
```

Aplikacja dostepna pod: `http://localhost:5173`

## Testowanie

### Backend

```bash
cd backend
vendor/bin/phpunit
# lub
vendor/bin/phpunit --testdox
```

### Analiza statyczna

```bash
cd backend
vendor/bin/phpstan analyse src --level=5
```

## API Endpoints

### Autentykacja

```
POST /api/login_check    # Logowanie, zwraca JWT token
```

### Czlonkowie (CRUD)

```
GET    /api/members          # Lista czlonkow
GET    /api/members/{id}     # Szczegoly czlonka
POST   /api/members          # Dodaj czlonka
PUT    /api/members/{id}     # Edytuj czlonka
DELETE /api/members/{id}     # Usun czlonka
```

### Skladki

```
GET    /api/membership_fees              # Lista skladek
POST   /api/membership_fees              # Dodaj skladke
PUT    /api/membership_fees/{id}         # Edytuj skladke
POST   /api/membership-fees/validate-overdue  # Oznacz zalegle
GET    /api/membership-fees/overdue      # Lista zaleglych
```

## Roadmapa

- [x] **POC** - Auth, CRUD czlonkow, walidacja skladek, testy, CI/CD
- [ ] **MVP** - Pelna ewidencja, odznaczenia, wyposazenie, finanse
- [ ] **Growth** - Multi-tenant, akcje ratownicze, panel gminy
- [ ] **Scale** - Modul wynajmow, mobile app, integracje

## Dokumentacja

Szczegolowa dokumentacja projektu znajduje sie w katalogu `.ai/`:

| Plik | Opis |
|------|------|
| `prd.md` | Product Requirements Document |
| `tech-stack.md` | Szczegoly stacku technologicznego |
| `plan.md` | Plan implementacji |
| `faza-poc.md` | Wymagania fazy POC |
| `faza-mvp.md` | Wymagania fazy MVP |

## Licencja

Projekt prywatny. Wszelkie prawa zastrzezone.

---

**osp.plus** - Nowoczesne narzedzie dla Ochotniczych Strazy Pozarnych