# Technical Task Plan – Konfiguracja (Dzień 1, Punkt 2)

## 1. Scope Summary

- **Opis:** Konfiguracja backendu Symfony dla obsługi JWT authentication, CORS i połączenia z bazą danych PostgreSQL w środowisku DDEV
- **Faza:** POC
- **Zakres:**
  - Konfiguracja `security.yaml` z JWT firewall
  - Konfiguracja `lexik_jwt_authentication.yaml` z TTL tokena
  - Konfiguracja `nelmio_cors.yaml` dla frontendu React
  - Konfiguracja `.env` dla DDEV (DATABASE_URL, APP_SECRET)
  - Generowanie kluczy JWT
  - Utworzenie encji User dla providera autentykacji
- **Wykluczenia:**
  - Encje Member, MembershipFee (Dzień 1, punkt 3)
  - Logika biznesowa (Dzień 1, punkt 4)
  - Frontend (Dzień 2)

## 2. Related Requirements

### PRD References
- **Sekcja 6 - Wymagania niefunkcjonalne / Bezpieczeństwo:**
  - JWT z expiration 1h
  - RBAC (role-based access control)
  - bcrypt dla haseł
- **Sekcja 7 - Architektura:**
  - JWT stateless - brak sesji na serwerze
  - REST API z JSON

### Business Rules
- Token JWT ważny 1 godzinę
- Hasła hashowane algorytmem bcrypt (auto)
- CORS umożliwia requesty z frontendu React (localhost:5173 w DDEV)

## 3. Task Breakdown

### Stage 1 – Encja User

**Goal:** Utworzenie encji User wymaganej przez Symfony Security jako user provider

#### Task 1.1 – Utworzenie encji User
- **Opis:** Utworzenie klasy `User.php` implementującej `UserInterface` i `PasswordAuthenticatedUserInterface`
- **Szczegóły techniczne:**
  - Pola: `id` (int, auto), `email` (string, unique), `password` (string), `roles` (json)
  - Implementacja metod: `getUserIdentifier()`, `getRoles()`, `getPassword()`, `eraseCredentials()`
  - Atrybut `#[ORM\Entity]` dla Doctrine
- **Pliki:**
  - `backend/src/Entity/User.php` (nowy)
- **Zależności:** Brak
- **Kryterium ukończenia:** Encja kompiluje się bez błędów, `bin/console lint:container` przechodzi

#### Task 1.2 – Utworzenie UserRepository
- **Opis:** Repository dla encji User z metodą `upgradePassword()`
- **Szczegóły techniczne:**
  - Implementacja `PasswordUpgraderInterface`
  - Metoda `upgradePassword()` dla automatycznej aktualizacji hashy
- **Pliki:**
  - `backend/src/Repository/UserRepository.php` (nowy)
- **Zależności:** Task 1.1
- **Kryterium ukończenia:** Repository zarejestrowane w kontenerze

---

### Stage 2 – Konfiguracja Security

**Goal:** Skonfigurowanie Symfony Security dla JWT authentication

#### Task 2.1 – Konfiguracja security.yaml
- **Opis:** Konfiguracja firewalli, providerów i access control dla JWT
- **Szczegóły techniczne:**
  ```yaml
  security:
    password_hashers:
      Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
      app_user_provider:
        entity:
          class: App\Entity\User
          property: email

    firewalls:
      dev:
        pattern: ^/(_(profiler|wdt)|css|images|js)/
        security: false
      login:
        pattern: ^/api/login
        stateless: true
        json_login:
          check_path: /api/login_check
          success_handler: lexik_jwt_authentication.handler.authentication_success
          failure_handler: lexik_jwt_authentication.handler.authentication_failure
      api:
        pattern: ^/api
        stateless: true
        jwt: ~

    access_control:
      - { path: ^/api/docs, roles: PUBLIC_ACCESS }
      - { path: ^/api/login, roles: PUBLIC_ACCESS }
      - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
  ```
- **Pliki:**
  - `backend/config/packages/security.yaml` (edycja)
- **Zależności:** Task 1.1, Task 1.2
- **Kryterium ukończenia:** `bin/console debug:config security` pokazuje poprawną konfigurację

#### Task 2.2 – Konfiguracja routes dla JWT
- **Opis:** Dodanie route dla endpointu login_check
- **Szczegóły techniczne:**
  - Route: `POST /api/login_check`
- **Pliki:**
  - `backend/config/routes.yaml` (edycja)
- **Zależności:** Brak
- **Kryterium ukończenia:** `bin/console debug:router` pokazuje route `api_login_check`

---

### Stage 3 – Konfiguracja JWT

**Goal:** Konfiguracja lexik/jwt-authentication-bundle z czasem życia tokena

#### Task 3.1 – Konfiguracja lexik_jwt_authentication.yaml
- **Opis:** Ustawienie TTL tokena na 3600 sekund (1h) zgodnie z PRD
- **Szczegóły techniczne:**
  ```yaml
  lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600
  ```
- **Pliki:**
  - `backend/config/packages/lexik_jwt_authentication.yaml` (edycja)
- **Zależności:** Brak
- **Kryterium ukończenia:** `bin/console debug:config lexik_jwt_authentication` pokazuje `token_ttl: 3600`

#### Task 3.2 – Generowanie kluczy JWT
- **Opis:** Wygenerowanie pary kluczy RSA dla JWT
- **Szczegóły techniczne:**
  - Komenda: `php bin/console lexik:jwt:generate-keypair`
  - Pliki wynikowe: `config/jwt/private.pem`, `config/jwt/public.pem`
- **Pliki:**
  - `backend/config/jwt/private.pem` (nowy, gitignored)
  - `backend/config/jwt/public.pem` (nowy, gitignored)
- **Zależności:** Brak
- **Kryterium ukończenia:** Pliki kluczy istnieją i mają poprawne uprawnienia

---

### Stage 4 – Konfiguracja CORS

**Goal:** Konfiguracja CORS dla frontendu React na porcie Vite

#### Task 4.1 – Aktualizacja nelmio_cors.yaml
- **Opis:** Rozszerzenie CORS o domenę DDEV i port Vite
- **Szczegóły techniczne:**
  ```yaml
  nelmio_cors:
    defaults:
      origin_regex: true
      allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
      allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
      allow_headers: ['Content-Type', 'Authorization', 'Accept']
      expose_headers: ['Link']
      max_age: 3600
    paths:
      '^/api/': null
  ```
- **Pliki:**
  - `backend/config/packages/nelmio_cors.yaml` (edycja)
- **Zależności:** Brak
- **Kryterium ukończenia:** Preflight OPTIONS request na `/api/` zwraca poprawne nagłówki CORS

---

### Stage 5 – Konfiguracja Environment

**Goal:** Konfiguracja zmiennych środowiskowych dla DDEV

#### Task 5.1 – Aktualizacja .env
- **Opis:** Ustawienie DATABASE_URL dla DDEV PostgreSQL i wygenerowanie APP_SECRET
- **Szczegóły techniczne:**
  - `DATABASE_URL` dla DDEV: `postgresql://db:db@db:5432/db?serverVersion=17&charset=utf8`
  - `APP_SECRET` - wygenerować losowy hash
  - `CORS_ALLOW_ORIGIN` - rozszerzyć regex o `osp-plus.ddev.site`
- **Pliki:**
  - `backend/.env` (edycja)
  - `backend/.env.local` (nowy, opcjonalny dla nadpisań)
- **Zależności:** Brak
- **Kryterium ukończenia:** `bin/console doctrine:database:create` działa w DDEV

#### Task 5.2 – Weryfikacja konfiguracji
- **Opis:** Test połączenia z bazą i konfiguracji JWT
- **Szczegóły techniczne:**
  - `ddev exec bin/console doctrine:database:create --if-not-exists`
  - `ddev exec bin/console debug:config lexik_jwt_authentication`
  - `ddev exec bin/console debug:config security`
- **Pliki:** Brak
- **Zależności:** Task 3.2, Task 5.1
- **Kryterium ukończenia:** Wszystkie komendy wykonują się bez błędów

---

## 4. Assumptions & Risks

### Assumptions
1. **DDEV działa** - środowisko Docker jest zainstalowane i `ddev start` przechodzi
2. **Klucze JWT w .gitignore** - klucze prywatne nie będą commitowane do repo
3. **PostgreSQL 17** - wersja z config DDEV, kompatybilna z Doctrine
4. **Użytkownik admin** - będzie utworzony w Fixtures (Dzień 1, punkt 5)

### Risks
1. **Konflikt wersji PHP** - DDEV ma PHP 8.4, Symfony 8.0 wymaga PHP 8.2+, ale niektóre bundla mogą mieć problemy
   - Mitygacja: Sprawdzić composer.json wszystkich zależności
2. **CORS w produkcji** - regex może być zbyt permisywny
   - Mitygacja: W `when@prod` ograniczyć do konkretnej domeny
3. **JWT passphrase w .env** - passphrase jest w repozytorium
   - Mitygacja: Dla POC akceptowalne, w produkcji użyć secrets

## 5. Implementation Readiness

### Dlaczego plan jest wykonalny krok po kroku:
1. **Jasna sekwencja** - każdy task ma zdefiniowane zależności
2. **Konkretne pliki** - wiadomo co edytować/tworzyć
3. **Weryfikowalne kryteria** - każdy task ma komendę sprawdzającą
4. **Izolowane zmiany** - konfiguracja nie wymaga kodu biznesowego

### Notes for AI-assisted implementation:
- **Task 1.1-1.2:** Można użyć `make:user` z MakerBundle, ale dla POC lepiej utworzyć ręcznie dla pełnej kontroli
- **Task 2.1:** Kluczowe jest zachowanie kolejności firewalli (dev → login → api)
- **Task 3.2:** Jeśli klucze już istnieją (są w .env), można pominąć generowanie
- **Task 5.1:** W DDEV host bazy to `db`, nie `127.0.0.1`
- **Parallel execution:** Tasks w różnych Stage'ach mogą być wykonywane równolegle, jeśli nie mają zależności