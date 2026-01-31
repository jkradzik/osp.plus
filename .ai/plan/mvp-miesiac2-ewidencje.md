# Technical Task Plan – MVP Miesiąc 2: Ewidencje

## 1. Scope Summary

- **Opis:** Implementacja modułów odznaczeń i wyposażenia osobistego ze słownikami
- **Faza:** MVP (Miesiąc 2)
- **Explicit inclusions:**
  - Encja Decoration + DecorationDictionary
  - Encja PersonalEquipment + EquipmentDictionary
  - CRUD API dla wszystkich encji
  - Frontend: listy i formularze
  - Testy integracyjne
- **Explicit exclusions:**
  - Import z Excel (osobny plan)
  - Audit log (osobny plan)
  - Sugestie kandydatów do odznaczeń (post-MVP)

## 2. Related Requirements

- **PRD Moduł 3:** Ewidencja odznaczeń
- **PRD Moduł 4:** Ewidencja wyposażenia osobistego
- **faza-mvp.md:** Miesiąc 2 - Ewidencje

## 3. Task Breakdown

### Stage 1 – Słowniki (Backend)

#### Task 1.1 – Encja DecorationDictionary
- **Opis:** Słownik typów odznaczeń (OSP, państwowe, inne)
- **Pola:**
  - id: int
  - name: string
  - category: enum (osp|state|other)
  - requiredYears: int (nullable) - wymagany staż
  - sortOrder: int
- **Pliki:** `backend/src/Entity/DecorationDictionary.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.2 – Encja EquipmentDictionary
- **Opis:** Słownik typów wyposażenia
- **Pola:**
  - id: int
  - name: string
  - category: enum (clothing|protective|other)
  - hasSizes: bool
- **Pliki:** `backend/src/Entity/EquipmentDictionary.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.3 – Fixtures słowników
- **Opis:** Dane początkowe dla słowników
- **Dane DecorationDictionary:**
  - Odznaka "Strażak Wzorowy" (osp)
  - Medal "Za Zasługi dla Pożarnictwa" brązowy/srebrny/złoty (osp)
  - Odznaka "Za wysługę lat" 5/10/15/20/25/30/35/40/45/50 (osp)
  - Złoty Znak Związku OSP RP (osp)
- **Dane EquipmentDictionary:**
  - Ubranie specjalne - kurtka (clothing, hasSizes=true)
  - Ubranie specjalne - spodnie (clothing, hasSizes=true)
  - Hełm strażacki (protective, hasSizes=true)
  - Buty specjalne (protective, hasSizes=true)
  - Rękawice (protective, hasSizes=true)
  - Kominiarka (protective, hasSizes=false)
  - Latarka (other, hasSizes=false)
- **Pliki:** `backend/src/DataFixtures/DictionaryFixtures.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 1.4 – Migracja bazy danych
- **Opis:** Generowanie migracji dla słowników
- **Pliki:** `backend/migrations/`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 2 – Moduł odznaczeń (Backend)

#### Task 2.1 – Encja Decoration
- **Opis:** Odznaczenie przypisane do członka
- **Pola:**
  - id: int
  - member: Member (FK)
  - type: DecorationDictionary (FK)
  - awardedAt: date
  - awardedBy: string (nullable) - kto nadał
  - certificateNumber: string (nullable)
  - notes: text (nullable)
  - createdAt: datetime
- **Relacja:** Member hasMany Decoration
- **Pliki:** `backend/src/Entity/Decoration.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 2.2 – API Platform dla Decoration
- **Opis:** Konfiguracja API z filtrami
- **Operacje:** GET collection, GET item, POST, PATCH, DELETE
- **Filtry:**
  - member (exact)
  - type (exact)
  - awardedAt (date range)
- **Pliki:** `backend/src/Entity/Decoration.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 2.3 – Testy integracyjne Decoration
- **Pliki:** `backend/tests/Integration/DecorationApiTest.php`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 3 – Moduł wyposażenia (Backend)

#### Task 3.1 – Encja PersonalEquipment
- **Opis:** Wyposażenie przypisane do członka
- **Pola:**
  - id: int
  - member: Member (FK)
  - type: EquipmentDictionary (FK)
  - size: string (nullable)
  - serialNumber: string (nullable)
  - issuedAt: date
  - notes: text (nullable)
  - createdAt: datetime
- **Relacja:** Member hasMany PersonalEquipment
- **Pliki:** `backend/src/Entity/PersonalEquipment.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 3.2 – API Platform dla PersonalEquipment
- **Opis:** Konfiguracja API z filtrami
- **Operacje:** GET collection, GET item, POST, PATCH, DELETE
- **Filtry:**
  - member (exact)
  - type (exact)
  - size (partial)
- **Pliki:** `backend/src/Entity/PersonalEquipment.php`
- **Status:** ⏳ DO ZROBIENIA

#### Task 3.3 – Testy integracyjne PersonalEquipment
- **Pliki:** `backend/tests/Integration/PersonalEquipmentApiTest.php`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 4 – Frontend: Odznaczenia

#### Task 4.1 – Strona listy odznaczeń
- **Opis:** Lista wszystkich odznaczeń z filtrowaniem po członku
- **Pliki:** `frontend/src/components/DecorationList.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 4.2 – Formularz dodawania odznaczenia
- **Opis:** Formularz z wyborem członka i typu odznaczenia
- **Pliki:** `frontend/src/components/DecorationForm.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 4.3 – Odznaczenia na profilu członka
- **Opis:** Sekcja odznaczeń w widoku szczegółów członka
- **Pliki:** `frontend/src/components/MemberDetail.jsx`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 5 – Frontend: Wyposażenie

#### Task 5.1 – Strona listy wyposażenia
- **Opis:** Lista wyposażenia z filtrowaniem
- **Pliki:** `frontend/src/components/EquipmentList.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 5.2 – Formularz przypisania wyposażenia
- **Opis:** Formularz z wyborem członka, typu i rozmiaru
- **Pliki:** `frontend/src/components/EquipmentForm.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 5.3 – Wyposażenie na profilu członka
- **Opis:** Sekcja wyposażenia w widoku szczegółów członka
- **Pliki:** `frontend/src/components/MemberDetail.jsx`
- **Status:** ⏳ DO ZROBIENIA

---

### Stage 6 – Routing i nawigacja

#### Task 6.1 – Aktualizacja routingu
- **Opis:** Dodanie tras dla odznaczeń i wyposażenia
- **Pliki:** `frontend/src/App.jsx`
- **Status:** ⏳ DO ZROBIENIA

#### Task 6.2 – Aktualizacja nawigacji
- **Opis:** Linki w headerze i dashboardzie
- **Pliki:**
  - `frontend/src/components/Layout.jsx`
  - `frontend/src/App.jsx` (Dashboard)
- **Status:** ⏳ DO ZROBIENIA

## 4. Progress Tracking

| Task | Status | Data |
|------|--------|------|
| 1.1 DecorationDictionary | ✅ Zrobione | 2026-01-30 |
| 1.2 EquipmentDictionary | ✅ Zrobione | 2026-01-30 |
| 1.3 Fixtures słowników | ✅ Zrobione | 2026-01-30 |
| 1.4 Migracja DB | ✅ Zrobione | 2026-01-30 |
| 2.1 Encja Decoration | ✅ Zrobione | 2026-01-30 |
| 2.2 API Decoration | ✅ Zrobione | 2026-01-30 |
| 2.3 Testy Decoration | ✅ Zrobione | 2026-01-30 |
| 3.1 Encja PersonalEquipment | ✅ Zrobione | 2026-01-30 |
| 3.2 API PersonalEquipment | ✅ Zrobione | 2026-01-30 |
| 3.3 Testy PersonalEquipment | ✅ Zrobione | 2026-01-30 |
| 4.1-4.3 Frontend odznaczenia | ✅ Zrobione | 2026-01-30 |
| 5.1-5.3 Frontend wyposażenie | ✅ Zrobione | 2026-01-30 |
| 6.1-6.2 Routing i nawigacja | ✅ Zrobione | 2026-01-30 |

## 5. Changelog

### 2026-01-30 (sesja 1)
- ✅ Utworzono enumy: DecorationCategory, EquipmentCategory
- ✅ Utworzono encje słownikowe: DecorationDictionary, EquipmentDictionary
- ✅ Utworzono encje: Decoration, PersonalEquipment z relacjami do Member
- ✅ Utworzono repozytoria dla wszystkich nowych encji
- ✅ Utworzono fixtures z danymi słowników i przykładowymi danymi
- ✅ Wygenerowano i wykonano migrację Version20260130225423
- ✅ Dodano 21 testów integracyjnych (DecorationApiTest, PersonalEquipmentApiTest)
- ✅ Wszystkie 82 testy przechodzą
- ✅ Rozszerzono api.js o metody dla odznaczeń i wyposażenia
- ✅ Utworzono komponenty: DecorationList, EquipmentList
- ✅ Zaktualizowano routing w App.jsx
- ✅ Dodano linki w nawigacji (Layout.jsx) i dashboardzie
- ✅ Build frontend przeszedł pomyślnie
