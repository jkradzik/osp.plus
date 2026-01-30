# Technical Task Plan – CI/CD (Dzień 3, Punkt 1)

## 1. Scope Summary

- **Opis:** Konfiguracja GitHub Actions dla automatycznych testów i buildu
- **Faza:** POC
- **Zakres:**
  - Workflow CI z dwoma jobami
  - Job: backend-tests (PHPUnit)
  - Job: frontend-build (npm build)

## 2. Task Breakdown

### Task 1.1 – GitHub Actions workflow
- Trigger: push do main, pull requests
- Job backend-tests:
  - PHP 8.4
  - PostgreSQL service
  - Composer install
  - PHPUnit
- Job frontend-build:
  - Node.js 22
  - npm ci
  - npm run build

**Plik:** `.github/workflows/ci.yml`
