# Technical Task Plan – MVP Miesiąc 1: Fundament

## 1. Scope Summary

- **Opis:** Rozszerzenie istniejących modułów POC do pełnej funkcjonalności MVP
- **Faza:** MVP (Miesiąc 1)
- **Explicit inclusions:**
  - Rozszerzenie encji Member (updatedAt)
  - Rozszerzenie encji MembershipFee (notes)
  - Filtrowanie i wyszukiwanie członków (API Platform filters)
  - Filtrowanie składek
  - Paginacja
  - Frontend: wyszukiwarka i filtry
- **Explicit exclusions:**
  - Import z Excel (osobny plan)
  - Audit log (osobny plan)
  - Nowe moduły (Miesiąc 2)

## 2. Related Requirements

- **PRD M-1:** Lista wszystkich członków z filtrowaniem i wyszukiwaniem
- **PRD S-1:** Lista składek ze statusami
- **faza-mvp.md:** Rozszerzenie modułu członków, moduł składek

## 3. Task Breakdown

### Stage 1 – Rozszerzenie encji (Backend)

#### Task 1.1 – Dodanie updatedAt do Member
- **Opis:** Pole updatedAt automatycznie aktualizowane przy każdej zmianie
- **Szczegóły:** ORM PreUpdate lifecycle callback
- **Pliki:** `backend/src/Entity/Member.php`
- **Status:** ✅ DO ZROBIENIA

#### Task 1.2 – Dodanie notes do MembershipFee
- **Opis:** Opcjonalne pole tekstowe na notatki przy składce
- **Pliki:** `backend/src/Entity/MembershipFee.php`
- **Status:** ✅ DO ZROBIENIA

#### Task 1.3 – Migracja bazy danych
- **Opis:** Wygenerowanie i wykonanie migracji dla nowych pól
- **Pliki:** `backend/migrations/`
- **Status:** ✅ DO ZROBIENIA

---

### Stage 2 – Filtry API Platform (Backend)

#### Task 2.1 – Filtry dla Member
- **Opis:** Dodanie filtrów: search (imię, nazwisko), status, wyszukiwanie
- **Filtry:**
  - `SearchFilter`: firstName, lastName, pesel (partial)
  - `SearchFilter`: email (exact)
  - `EnumFilter`: membershipStatus
  - `DateFilter`: joinDate, birthDate
  - `OrderFilter`: lastName, firstName, joinDate
- **Pliki:** `backend/src/Entity/Member.php`
- **Status:** ⏳ NASTĘPNE

#### Task 2.2 – Filtry dla MembershipFee
- **Opis:** Dodanie filtrów dla składek
- **Filtry:**
  - `SearchFilter`: member (exact)
  - `EnumFilter`: status
  - `NumericFilter`: year
  - `OrderFilter`: year, status
- **Pliki:** `backend/src/Entity/MembershipFee.php`
- **Status:** ⏳ NASTĘPNE

#### Task 2.3 – Paginacja
- **Opis:** Konfiguracja paginacji (20 elementów/strona)
- **Pliki:** `backend/config/packages/api_platform.yaml`
- **Status:** ⏳ NASTĘPNE

---

### Stage 3 – Frontend: Wyszukiwarka i filtry

#### Task 3.1 – Komponent SearchFilter dla Member
- **Opis:** Pole wyszukiwania + dropdown statusu
- **Pliki:** `frontend/src/components/MemberList.jsx`
- **Status:** ⏳ PÓŹNIEJ

#### Task 3.2 – Komponent filtrów dla składek
- **Opis:** Filtr roku i statusu składki
- **Pliki:** `frontend/src/components/FeeList.jsx`
- **Status:** ⏳ PÓŹNIEJ

#### Task 3.3 – Paginacja w listach
- **Opis:** Komponent paginacji + obsługa w API service
- **Pliki:**
  - `frontend/src/components/Pagination.jsx`
  - `frontend/src/services/api.js`
- **Status:** ⏳ PÓŹNIEJ

---

### Stage 4 – Testy

#### Task 4.1 – Testy filtrów Member
- **Pliki:** `backend/tests/Integration/MemberApiTest.php`
- **Status:** ⏳ PÓŹNIEJ

#### Task 4.2 – Testy filtrów MembershipFee
- **Pliki:** `backend/tests/Integration/MembershipFeeApiTest.php`
- **Status:** ⏳ PÓŹNIEJ

## 4. Progress Tracking

| Task | Status | Data |
|------|--------|------|
| 1.1 Member updatedAt | ✅ Zrobione | 2026-01-30 |
| 1.2 MembershipFee notes | ✅ Zrobione | 2026-01-30 |
| 1.3 Migracja DB | ✅ Zrobione | 2026-01-30 |
| 2.1 Filtry Member | ✅ Zrobione | 2026-01-30 |
| 2.2 Filtry MembershipFee | ✅ Zrobione | 2026-01-30 |
| 2.3 Paginacja | ✅ Zrobione | 2026-01-30 |
| 3.1 Frontend MemberList | ✅ Zrobione | 2026-01-30 |
| 3.2 Frontend FeeList | ✅ Zrobione | 2026-01-30 |
| 3.3 Style CSS | ✅ Zrobione | 2026-01-30 |
| 4.1-4.2 Testy filtrów | ✅ Zrobione | 2026-01-30 |
| 5.1 CSS full-width | ✅ Zrobione | 2026-01-30 |

## 5. Changelog

### 2026-01-30 (sesja 4)
- ✅ Naprawiono testy integracyjne (zakres lat w MembershipFeeApiTest)
- ✅ Wszystkie 61 testów przechodzi (197 assertions)
- ✅ CSS: usunięto max-width z app-main (panele na pełną szerokość)
- ✅ CSS: wycentrowano login panel pionowo i poziomo
- ✅ CSS: usunięto ograniczenia szerokości z member-form i dashboard-links
- ✅ Build frontend przeszedł pomyślnie

### 2026-01-30 (sesja 3)
- ✅ Rozszerzono api.js o obsługę filtrów i paginacji (getMembers/getFees zwracają {items, totalItems, view})
- ✅ Zaktualizowano MemberList z wyszukiwarką po nazwisku, filtrem statusu i paginacją
- ✅ Zaktualizowano FeeList z filtrem roku, statusu i paginacją
- ✅ Dodano style CSS dla filtrów i paginacji
- ✅ Build frontend przeszedł pomyślnie

### 2026-01-30 (sesja 2)
- ✅ Dodano API Platform filtry dla Member:
  - SearchFilter: firstName, lastName, pesel (partial), email (exact), phone (partial), membershipStatus (exact)
  - DateFilter: birthDate, joinDate, deathDate
  - OrderFilter: lastName, firstName, joinDate, birthDate, createdAt
- ✅ Dodano API Platform filtry dla MembershipFee:
  - SearchFilter: member (exact), status (exact)
  - NumericFilter: year
  - OrderFilter: year, status, amount, paidAt, createdAt
- ✅ Skonfigurowano paginację (20/strona, max 100, client-enabled)
- ✅ Zaktualizowano tytuł API na "OSP.plus API"

### 2026-01-30 (sesja 1)
- ✅ Dodano pole `updatedAt` do encji Member z automatyczną aktualizacją (PreUpdate)
- ✅ Dodano pole `notes` do encji MembershipFee
- ✅ Wygenerowano i wykonano migrację `Version20260130223207`
- ✅ Wszystkie 48 testów integracyjnych przechodzi
