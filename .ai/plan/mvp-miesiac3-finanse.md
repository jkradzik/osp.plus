# Technical Task Plan – MVP Miesiąc 3: Finanse + Role

## 1. Scope Summary

- **Opis:** Implementacja modułu ewidencji finansowej i systemu ról
- **Faza:** MVP (Miesiąc 3)
- **Explicit inclusions:**
  - Encja FinancialRecord + FinancialCategory
  - CRUD API dla finansów
  - Podsumowania miesięczne/roczne
  - System ról (Admin, Prezes, Skarbnik, Naczelnik, Druh)
  - Frontend: lista operacji, formularze, podsumowania
  - Testy integracyjne
- **Explicit exclusions:**
  - Raporty zaawansowane (post-MVP)
  - Eksport do PDF/Excel (post-MVP)

## 2. Related Requirements

- **PRD Moduł 5:** Ewidencja finansowa
- **PRD Role:** Tabela uprawnień
- **faza-mvp.md:** Miesiąc 3 - Finanse + Role

## 3. Task Breakdown

### Stage 1 – Moduł finansowy (Backend)

#### Task 1.1 – Enum FinancialType
- **Opis:** Typ operacji (przychód/koszt)
- **Wartości:** income, expense
- **Pliki:** `backend/src/Enum/FinancialType.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.2 – Encja FinancialCategory
- **Opis:** Słownik kategorii finansowych
- **Pola:**
  - id: int
  - name: string
  - type: FinancialType (income|expense)
  - sortOrder: int
- **Pliki:** `backend/src/Entity/FinancialCategory.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.3 – Encja FinancialRecord
- **Opis:** Operacja finansowa (przychód/koszt)
- **Pola:**
  - id: int
  - type: FinancialType
  - category: FinancialCategory (FK)
  - amount: decimal(10,2)
  - description: text
  - documentNumber: string (nullable)
  - recordedAt: date
  - createdBy: User (FK)
  - createdAt: datetime
- **Pliki:** `backend/src/Entity/FinancialRecord.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.4 – Fixtures kategorii finansowych
- **Dane przychodów:**
  - Dotacja z gminy
  - Składki członkowskie
  - Darowizny
  - Wynajem remizy
  - Inne przychody
- **Dane kosztów:**
  - Paliwo
  - Przeglądy i naprawy pojazdów
  - Zakup sprzętu
  - Szkolenia
  - Ubezpieczenia
  - Media i utrzymanie remizy
  - Inne koszty
- **Pliki:** `backend/src/DataFixtures/FinancialFixtures.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.5 – Migracja bazy danych
- **Pliki:** `backend/migrations/`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 2 – API finansowe

#### Task 2.1 – Endpoint podsumowania
- **Opis:** Custom endpoint zwracający bilans
- **Endpoint:** GET /api/financial-summary?year=2024&month=1
- **Response:** { totalIncome, totalExpense, balance, byCategory[] }
- **Pliki:** `backend/src/Controller/FinancialSummaryController.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 2.2 – Testy integracyjne finansów
- **Pliki:** `backend/tests/Integration/FinancialApiTest.php`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 3 – Frontend: Finanse

#### Task 3.1 – Strona listy operacji finansowych
- **Opis:** Lista operacji z filtrowaniem po typie, kategorii, dacie
- **Pliki:** `frontend/src/components/FinancialList.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 3.2 – Formularz dodawania operacji
- **Opis:** Formularz z wyborem typu, kategorii, kwoty
- **Pliki:** `frontend/src/components/FinancialForm.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 3.3 – Komponent podsumowania
- **Opis:** Bilans miesięczny/roczny z wykresem
- **Pliki:** `frontend/src/components/FinancialSummary.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 3.4 – Routing i nawigacja
- **Pliki:** `frontend/src/App.jsx`, `frontend/src/components/Layout.jsx`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 4 – System ról (opcjonalnie w tym miesiącu)

#### Task 4.1 – Rozszerzenie User o role
- **Opis:** Dodanie pola role do User (prezes, skarbnik, naczelnik, druh)
- **Status:** ⏳ PÓŹNIEJ

#### Task 4.2 – Voters dla uprawnień
- **Opis:** Symfony Voters sprawdzające uprawnienia
- **Status:** ⏳ PÓŹNIEJ

## 4. Progress Tracking

| Task | Status | Data |
|------|--------|------|
| 1.1 FinancialType enum | ✅ Zrobione | 2026-01-31 |
| 1.2 FinancialCategory | ✅ Zrobione | 2026-01-31 |
| 1.3 FinancialRecord | ✅ Zrobione | 2026-01-31 |
| 1.4 Fixtures | ✅ Zrobione | 2026-01-31 |
| 1.5 Migracja DB | ✅ Zrobione | 2026-01-31 |
| 2.1 Endpoint summary | ✅ Zrobione | 2026-01-31 |
| 2.2 Testy | ✅ Zrobione | 2026-01-31 |
| 3.1-3.4 Frontend | ✅ Zrobione | 2026-01-31 |
| 4.1-4.2 Role | ✅ Zrobione | 2026-01-31 |

## 5. Changelog

### 2026-01-31 (sesja 2)
- ✅ Dodano hierarchię ról w security.yaml (ROLE_ADMIN > ROLE_PREZES/SKARBNIK/NACZELNIK > ROLE_USER)
- ✅ Utworzono 5 Voterów: MemberVoter, MembershipFeeVoter, DecorationVoter, PersonalEquipmentVoter, FinancialRecordVoter
- ✅ Zaktualizowano security w encjach API Platform zgodnie z tabelą uprawnień
- ✅ Dodano użytkowników testowych: prezes@osp.plus, skarbnik@osp.plus, naczelnik@osp.plus
- ✅ Wszystkie 97 testów przechodzi

### 2026-01-31 (sesja 1)
- ✅ Utworzono enum FinancialType (income/expense)
- ✅ Utworzono encję FinancialCategory (słownik kategorii)
- ✅ Utworzono encję FinancialRecord z filtrami API Platform
- ✅ Utworzono repozytoria z metodą getSummary()
- ✅ Utworzono FinancialSummaryController dla endpointu /api/financial-summary
- ✅ Utworzono fixtures z kategoriami i przykładowymi danymi
- ✅ Wygenerowano i wykonano migrację Version20260131065215
- ✅ Dodano 15 testów integracyjnych (FinancialApiTest)
- ✅ Wszystkie 97 testów przechodzi (364 assertions)
- ✅ Rozszerzono api.js o metody dla finansów
- ✅ Utworzono komponent FinancialList z podsumowaniem, filtrami i formularzem
- ✅ Dodano style CSS dla kart podsumowania finansowego
- ✅ Zaktualizowano routing i nawigację
- ✅ Build frontend przeszedł pomyślnie
