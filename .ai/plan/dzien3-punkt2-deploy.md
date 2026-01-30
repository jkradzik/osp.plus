# Technical Task Plan – Deploy (Dzień 3, Punkt 2)

## 1. Scope Summary

- **Opis:** Konfiguracja produkcyjna Docker dla self-hosted deploy
- **Faza:** POC
- **Zakres:**
  - Dockerfile dla backend (PHP-FPM + Nginx)
  - Dockerfile dla frontend (Nginx static)
  - docker-compose.prod.yml
  - Konfiguracja Nginx

## 2. Task Breakdown

### Task 2.1 – Backend Dockerfile
- Multi-stage build
- PHP 8.4 FPM
- Composer install --no-dev
- Optimize autoloader

### Task 2.2 – Frontend Dockerfile
- Node build stage
- Nginx serve static

### Task 2.3 – docker-compose.prod.yml
- Services: backend, frontend, db
- Volumes for data persistence
- Environment variables
