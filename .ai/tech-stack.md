# Tech Stack - osp.plus

## Backend

| Technologia | Wersja | Cel |
|-------------|--------|-----|
| PHP | 8.3+ | Język backendu |
| Symfony | 7.x | Framework |
| API Platform | 3.x | REST API, automatyczna dokumentacja, CRUD |
| Doctrine ORM | 3.x | Mapowanie obiektowo-relacyjne |
| lexik/jwt-authentication-bundle | 3.x | Autentykacja JWT |
| nelmio/cors-bundle | 2.x | Obsługa CORS dla frontendu |

## Frontend

| Technologia | Wersja | Cel |
|-------------|--------|-----|
| React | 18.x | UI library |
| Vite | 5.x | Build tool |
| React Router | 6.x | Routing SPA |
| TypeScript | 5.x | Typowanie (opcjonalnie, zalecane) |

### Faza POC
- API Platform Admin (wbudowany) jako tymczasowy interfejs
- Prosty React dla wymagań zaliczeniowych

### Faza MVP
- Pełny React SPA
- Docelowo: React Native dla mobile

## Baza danych

| Technologia | Wersja | Cel |
|-------------|--------|-----|
| PostgreSQL | 16.x | Główna baza danych |

**Uzasadnienie PostgreSQL nad MySQL:**
- Natywne typy ENUM
- Lepsze wsparcie JSON (JSONB)
- Full-text search
- Lepsza wydajność przy złożonych zapytaniach

## Środowisko deweloperskie

| Technologia | Cel |
|-------------|-----|
| DDEV | Lokalne środowisko Docker |
| Docker | Konteneryzacja |
| Composer | Zarządzanie zależnościami PHP |
| npm | Zarządzanie zależnościami JS |

## CI/CD

| Technologia | Cel |
|-------------|-----|
| GitHub Actions | Pipeline CI/CD |
| PHPUnit | Testy jednostkowe backend |
| PHPStan | Analiza statyczna (level 5+) |

## Architektura

```
┌─────────────────────────────────────────────────────────┐
│                      FRONTEND                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────────┐  │
│  │ React    │  │ React    │  │ React Native         │  │
│  │ Admin    │  │ Public   │  │ Mobile App           │  │
│  │ Panel    │  │ Pages    │  │ (przyszłość)         │  │
│  └────┬─────┘  └────┬─────┘  └──────────┬───────────┘  │
│       │             │                    │              │
└───────┼─────────────┼────────────────────┼──────────────┘
        │             │                    │
        ▼             ▼                    ▼
┌─────────────────────────────────────────────────────────┐
│                    REST API (JSON)                      │
│                    JWT Authentication                   │
└─────────────────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────────────────┐
│                      BACKEND                            │
│  ┌──────────────────────────────────────────────────┐  │
│  │              Symfony 7 + API Platform            │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────────────┐    │  │
│  │  │ Entity  │ │ Service │ │ Controller      │    │  │
│  │  │ (Model) │ │ (Logic) │ │ (API Endpoints) │    │  │
│  │  └────┬────┘ └────┬────┘ └────────┬────────┘    │  │
│  └───────┼───────────┼───────────────┼──────────────┘  │
│          │           │               │                  │
│          ▼           ▼               ▼                  │
│  ┌──────────────────────────────────────────────────┐  │
│  │              Doctrine ORM                        │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────────────────┐
│                    PostgreSQL 16                        │
└─────────────────────────────────────────────────────────┘
```

## Kluczowe zasady

1. **API-first** - Backend to czyste REST API, zero renderowania HTML
2. **JWT stateless** - Brak sesji, każdy request zawiera token
3. **Single source of truth** - Jeden backend dla wszystkich frontendów
4. **No Twig** - Frontend całkowicie oddzielony od backendu

## Pakiety Composer (backend)

```json
{
    "require": {
        "php": ">=8.3",
        "symfony/framework-bundle": "^7.0",
        "api-platform/core": "^3.2",
        "doctrine/orm": "^3.0",
        "doctrine/dbal": "^4.0",
        "lexik/jwt-authentication-bundle": "^3.0",
        "nelmio/cors-bundle": "^2.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5",
        "phpstan/phpstan": "^1.10",
        "symfony/test-pack": "*",
        "doctrine/doctrine-fixtures-bundle": "^3.5"
    }
}
```

## Pakiety npm (frontend)

```json
{
    "dependencies": {
        "react": "^18.2",
        "react-dom": "^18.2",
        "react-router-dom": "^6.20"
    },
    "devDependencies": {
        "vite": "^5.0",
        "@vitejs/plugin-react": "^4.2",
        "typescript": "^5.3"
    }
}
```

## Środowisko produkcyjne (docelowo)

| Komponent | Rozwiązanie |
|-----------|-------------|
| Hosting | VPS / DigitalOcean / Hetzner |
| Web server | Nginx + PHP-FPM |
| SSL | Let's Encrypt (certbot) |
| CI/CD | GitHub Actions → deploy |
| Backup | Automatyczny dump PostgreSQL |