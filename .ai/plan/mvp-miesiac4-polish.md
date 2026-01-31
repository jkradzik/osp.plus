# Technical Task Plan – MVP Miesiąc 4: Polish + Wdrożenie

## 1. Scope Summary

- **Opis:** Finalne poprawki UX, testy i przygotowanie do wdrożenia
- **Faza:** MVP (Miesiąc 4)
- **Explicit inclusions:**
  - Bug fixes i poprawki UX
  - Ukrywanie nawigacji wg uprawnień
  - Wyświetlanie aktualnej roli użytkownika
  - Szybkie logowanie demo
  - Testy z zarządem jednostki
  - Dokumentacja użytkownika
- **Explicit exclusions:**
  - Nowe funkcjonalności (post-MVP)
  - Multi-tenant

## 2. Related Requirements

- **PRD Role:** Tabela uprawnień
- **faza-mvp.md:** Miesiąc 4 - Polish + Wdrożenie

## 3. Task Breakdown

### Stage 1 – Poprawki UX

#### Task 1.1 – Szybkie logowanie demo
- **Opis:** Przyciski w formularzu logowania do szybkiego wypełnienia danych demo
- **Pliki:** `frontend/src/components/LoginForm.jsx`, `frontend/src/App.css`
- **Status:** ✅ Zrobione

#### Task 1.2 – Wyświetlanie roli użytkownika
- **Opis:** Parsowanie JWT i pokazywanie email + rola w nagłówku
- **Pliki:** `frontend/src/services/api.js`, `frontend/src/context/AuthContext.jsx`, `frontend/src/components/Layout.jsx`
- **Status:** ✅ Zrobione

#### Task 1.3 – Ukrywanie nawigacji Finanse
- **Opis:** Finanse widoczne tylko dla Admin, Prezes, Skarbnik, Naczelnik
- **Pliki:** `frontend/src/components/Layout.jsx`, `frontend/src/App.jsx`
- **Status:** ✅ Zrobione

#### Task 1.4 – Poprawka Dashboard (Link zamiast a)
- **Opis:** Użycie React Router Link zamiast <a> w Dashboard
- **Pliki:** `frontend/src/App.jsx`
- **Status:** ✅ Zrobione

#### Task 1.5 – Ukrywanie przycisków akcji wg ról
- **Opis:** Ukrycie przycisków dodawania/edycji/usuwania dla użytkowników bez uprawnień
- **Pliki:**
  - `frontend/src/components/MemberList.jsx` - Admin może dodawać/usuwać, Admin/Prezes/Naczelnik mogą edytować
  - `frontend/src/components/FeeList.jsx` - Admin/Skarbnik mogą walidować zaległe
  - `frontend/src/components/DecorationList.jsx` - Admin/Prezes mogą dodawać
  - `frontend/src/components/EquipmentList.jsx` - Admin/Naczelnik mogą przypisywać
  - `frontend/src/components/FinancialList.jsx` - Admin/Skarbnik mogą dodawać operacje
- **Status:** ✅ Zrobione

#### Task 1.6 – Ochrona routingu dla Finansów
- **Opis:** Przekierowanie na dashboard gdy użytkownik bez uprawnień próbuje wejść na /finances
- **Pliki:** `frontend/src/App.jsx` (FinanceRoute component)
- **Status:** ✅ Zrobione

---

### Stage 2 – Testy i walidacja

#### Task 2.1 – Weryfikacja wszystkich testów
- **Status:** ✅ Zrobione (111 testów, 401 assertions)

#### Task 2.2 – Testy manualne z różnymi rolami
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 3 – Dokumentacja

#### Task 3.1 – Dokumentacja użytkownika
- **Opis:** Instrukcja obsługi systemu dla użytkowników końcowych
- **Pliki:** `docs/instrukcja-uzytkownika.md`
- **Status:** ✅ Zrobione

#### Task 3.2 – Instrukcja wdrożenia
- **Opis:** Instrukcja instalacji i konfiguracji dla administratorów
- **Pliki:** `docs/instrukcja-wdrozenia.md`
- **Status:** ✅ Zrobione

#### Task 3.3 – Komenda tworzenia użytkowników
- **Opis:** Komenda CLI do tworzenia użytkowników przy wdrożeniu
- **Pliki:** `backend/src/Command/CreateUserCommand.php`
- **Użycie:** `php bin/console app:create-user email@example.com haslo123 ROLE_ADMIN`
- **Status:** ✅ Zrobione

---

## 4. Progress Tracking

| Task | Status | Data |
|------|--------|------|
| 1.1 Szybkie logowanie demo | ✅ Zrobione | 2026-01-31 |
| 1.2 Wyświetlanie roli | ✅ Zrobione | 2026-01-31 |
| 1.3 Ukrywanie nawigacji | ✅ Zrobione | 2026-01-31 |
| 1.4 Dashboard Link | ✅ Zrobione | 2026-01-31 |
| 1.5 Ukrywanie przycisków akcji | ✅ Zrobione | 2026-01-31 |
| 1.6 Ochrona routingu Finanse | ✅ Zrobione | 2026-01-31 |
| 2.1 Weryfikacja testów | ✅ Zrobione | 2026-01-31 |
| 2.2 Testy manualne | ⏳ Do zrobienia | - |
| 3.1 Dokumentacja użytkownika | ✅ Zrobione | 2026-01-31 |
| 3.2 Instrukcja wdrożenia | ✅ Zrobione | 2026-01-31 |
| 3.3 Komenda create-user | ✅ Zrobione | 2026-01-31 |

## 5. Changelog

### 2026-01-31 (sesja 1)
- ✅ Dodano szybkie przyciski logowania demo z kolorowymi ikonami ról
- ✅ Poprawiono strukturę HTML (ul wewnątrz div zamiast p)
- ✅ Dodano parsowanie JWT dla informacji o użytkowniku (email, roles)
- ✅ Rozszerzono AuthContext o user, hasRole, canAccessFinances, getRoleName
- ✅ Nagłówek pokazuje email i rolę zalogowanego użytkownika
- ✅ Nawigacja do Finansów ukryta dla zwykłego Druha
- ✅ Dashboard używa Link zamiast <a> (brak pełnego przeładowania strony)
- ✅ Dashboard pokazuje powitanie z rolą użytkownika
- ✅ Dashboard ukrywa kartę Finanse dla Druha
- ✅ Ukrywanie przycisków akcji wg uprawnień we wszystkich komponentach:
  - MemberList: Dodaj (Admin), Edytuj (Admin/Prezes/Naczelnik), Usuń (Admin)
  - FeeList: Oznacz zaległe (Admin/Skarbnik)
  - DecorationList: Dodaj odznaczenie (Admin/Prezes)
  - EquipmentList: Przypisz wyposażenie (Admin/Naczelnik)
  - FinancialList: Dodaj operację (Admin/Skarbnik)
- ✅ Dodano FinanceRoute - ochrona routingu dla /finances (przekierowanie na dashboard)
- ✅ Utworzono dokumentację użytkownika (docs/instrukcja-uzytkownika.md)
- ✅ Utworzono instrukcję wdrożenia (docs/instrukcja-wdrozenia.md)
- ✅ Utworzono komendę CLI: `app:create-user` do tworzenia użytkowników
- ✅ Poprawiono test MembershipFeeApiTest (szerszy zakres losowych lat)
- ✅ Build frontend przeszedł pomyślnie
- ✅ Wszystkie 111 testów przechodzi (416 assertions)
