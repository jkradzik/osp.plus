# Technical Task Plan – Fixtures + Test Manualny (Dzień 1, Punkt 5)

## 1. Scope Summary

- **Opis:** Utworzenie danych testowych i weryfikacja działania API
- **Faza:** POC
- **Zakres:**
  - Fixture dla User (admin@osp.plus / admin123)
  - Fixture dla Member (przykładowi członkowie)
  - Fixture dla MembershipFee (składki w różnych statusach)
  - Test manualny API przez curl

## 2. Task Breakdown

### Stage 1 – Fixtures

#### Task 1.1 – AppFixtures
- User admin z ROLE_ADMIN
- 5 przykładowych członków
- Składki za lata 2023-2026 w różnych statusach

**Plik:** `backend/src/DataFixtures/AppFixtures.php`

### Stage 2 – Test manualny

#### Task 2.1 – Weryfikacja przez curl
- Login i pobranie JWT
- CRUD members
- Walidacja składek
