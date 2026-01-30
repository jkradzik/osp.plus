# OSP.plus

System zarządzania jednostką Ochotniczej Straży Pożarnej.

## Stack technologiczny

- **Backend:** PHP 8.4, Symfony 8, API Platform 4
- **Frontend:** React 19, Vite 7, React Router 7
- **Baza danych:** PostgreSQL 17
- **Auth:** JWT (lexik/jwt-authentication-bundle)
- **Dev:** DDEV

## Wymagania

- Docker + DDEV
- Node.js 22+
- PHP 8.4+ (dla lokalnego developmentu bez DDEV)

## Instalacja (Development)

```bash
# Klonowanie
git clone https://github.com/your-repo/osp_plus.git
cd osp_plus

# Start DDEV
ddev start

# Backend - instalacja zależności
ddev composer install

# Backend - generowanie kluczy JWT
ddev exec "cd backend && php bin/console lexik:jwt:generate-keypair"

# Backend - migracje
ddev exec "cd backend && php bin/console doctrine:migrations:migrate --no-interaction"

# Backend - dane testowe
ddev exec "cd backend && php bin/console doctrine:fixtures:load --no-interaction"

# Frontend - instalacja zależności (opcjonalnie, Vite startuje automatycznie)
ddev exec "cd frontend && npm install"
```

## Uruchomienie

```bash
ddev start
```

Aplikacja dostępna pod:
- **Frontend:** https://osp-plus.ddev.site:5173
- **API:** https://osp-plus.ddev.site/api
- **API Docs:** https://osp-plus.ddev.site/api/docs

## Dane testowe

| Email | Hasło | Rola |
|-------|-------|------|
| admin@osp.plus | admin123 | ROLE_ADMIN |
| user@osp.plus | user123 | ROLE_USER |

## Testy

```bash
# Unit testy backend
ddev exec "cd backend && vendor/bin/phpunit --testdox"

# Lint frontend
ddev exec "cd frontend && npm run lint"

# Build frontend
ddev exec "cd frontend && npm run build"
```

## API Endpoints

### Auth
```
POST /api/login_check    # Login, zwraca JWT token
```

### Members (CRUD)
```
GET    /api/members          # Lista członków
GET    /api/members/{id}     # Szczegóły członka
POST   /api/members          # Dodaj członka
PATCH  /api/members/{id}     # Edytuj członka
DELETE /api/members/{id}     # Usuń członka (ROLE_ADMIN)
```

### Membership Fees
```
GET    /api/membership_fees          # Lista składek
GET    /api/membership_fees/{id}     # Szczegóły składki
POST   /api/membership_fees          # Dodaj składkę
PATCH  /api/membership_fees/{id}     # Edytuj składkę
```

### Custom (Business Logic)
```
POST /api/membership-fees/validate-overdue  # Oznacz zaległe składki
GET  /api/membership-fees/overdue           # Lista zaległych składek
```

## Logika biznesowa

### Walidacja składek

Składka jest oznaczana jako **zaległa** gdy:
- Status = `unpaid`
- Aktualna data > 31 marca roku składki

Wyjątki (nie podlegają walidacji):
- Status = `exempt` (zwolniony)
- Status = `not_applicable` (nie dotyczy)

## Deploy (Production)

```bash
# Skopiuj i skonfiguruj zmienne środowiskowe
cp .env.prod.example .env.prod

# Edytuj .env.prod - ustaw hasła i sekrety
nano .env.prod

# Build i uruchomienie
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build

# Migracje
docker compose -f docker-compose.prod.yml exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Generowanie kluczy JWT (pierwszy raz)
docker compose -f docker-compose.prod.yml exec backend php bin/console lexik:jwt:generate-keypair
```

## Struktura projektu

```
osp_plus/
├── backend/                 # Symfony API
│   ├── src/
│   │   ├── Controller/      # Custom controllers
│   │   ├── Entity/          # Doctrine entities
│   │   ├── Enum/            # PHP enums
│   │   ├── Repository/      # Doctrine repositories
│   │   └── Service/         # Business logic
│   ├── tests/               # PHPUnit tests
│   └── config/              # Symfony config
├── frontend/                # React SPA
│   ├── src/
│   │   ├── components/      # React components
│   │   ├── context/         # React context (Auth)
│   │   └── services/        # API service
│   └── dist/                # Build output
├── docker/                  # Docker configs
├── .ddev/                   # DDEV config
└── .github/workflows/       # CI/CD
```

## Licencja

MIT
