# Faza POC - Proof of Concept

> **Cel:** Zaliczenie kursu
> **Czas realizacji:** 3 dni robocze
> **Środowisko:** Produkcyjna subdomena

---

## Wymagania zaliczeniowe

| # | Wymaganie | Realizacja | Status |
|---|-----------|------------|--------|
| 1 | Auth | Login/logout + JWT | [ ] |
| 2 | CRUD | Ewidencja członków (Member) | [ ] |
| 3 | Logika biznesowa | Walidacja składek - oznaczanie zaległych | [ ] |
| 4 | Test | Unit test dla MembershipFeeValidationService | [ ] |
| 5 | CI/CD | GitHub Actions (testy + build) | [ ] |

---

## Zakres funkcjonalny POC

### Encje

```
Member (Członek)
├── id: int
├── firstName: string
├── lastName: string
├── pesel: string (unique)
├── email: string (nullable)
├── phone: string (nullable)
├── birthDate: date
├── joinDate: date
├── membershipStatus: enum
└── createdAt: datetime

MembershipFee (Składka)
├── id: int
├── member: Member (FK)
├── year: int
├── amount: decimal
├── status: enum (unpaid|paid|overdue|exempt|not_applicable)
├── paidAt: date (nullable)
└── createdAt: datetime

User (Użytkownik systemu)
├── id: int
├── email: string (unique)
├── password: string (hashed)
└── roles: json
```

### Endpointy API

```
POST   /api/login_check              → JWT token
GET    /api/members                  → Lista członków
GET    /api/members/{id}             → Szczegóły członka
POST   /api/members                  → Dodaj członka
PUT    /api/members/{id}             → Edytuj członka
DELETE /api/members/{id}             → Usuń członka
GET    /api/membership_fees          → Lista składek
POST   /api/membership_fees          → Dodaj składkę
PUT    /api/membership_fees/{id}     → Edytuj składkę
POST   /api/membership-fees/validate-overdue → Oznacz zaległe
GET    /api/membership-fees/overdue  → Lista zaległych
```

### Logika biznesowa

**MembershipFeeValidationService:**
- `isOverdue(fee, referenceDate)` - sprawdza czy składka przeterminowana
- `markAsOverdueIfApplicable(fee)` - oznacza jeśli spełnia warunki
- `validateAndMarkAllOverdue()` - procesuje wszystkie nieopłacone

**Reguła:** Składka jest zaległa gdy:
- status = `unpaid`
- aktualna data > 31 marca danego roku
- status ≠ `exempt` i ≠ `not_applicable`

### Frontend (minimalny)

- Strona logowania
- Lista członków (tabela)
- Formularz dodawania/edycji członka
- Lista składek z przyciskiem "Oznacz zaległe"

---

## Harmonogram (3 dni)

### Dzień 1: Backend
| Godziny | Zadanie |
|---------|---------|
| 0-1 | Setup projektu (Symfony, DDEV, struktura) |
| 1-2 | Instalacja pakietów (API Platform, JWT, CORS) |
| 2-3 | Konfiguracja (security.yaml, JWT keys, .env) |
| 3-5 | Encje Doctrine + migracje |
| 5-6 | MembershipFeeValidationService |
| 6-7 | MembershipFeeController (custom endpoints) |
| 7-8 | Fixtures + test manualny |

### Dzień 2: Testy + Frontend
| Godziny | Zadanie |
|---------|---------|
| 0-2 | Unit testy (MembershipFeeValidationServiceTest) |
| 2-3 | Setup frontend (Vite + React) |
| 3-4 | api.js + AuthContext |
| 4-5 | LoginForm |
| 5-7 | MemberList + MemberForm |
| 7-8 | FeeList + styling |

### Dzień 3: CI/CD + Deploy
| Godziny | Zadanie |
|---------|---------|
| 0-2 | GitHub Actions (ci.yml) |
| 2-3 | Docker config produkcyjny |
| 3-5 | Deploy na serwer |
| 5-6 | Testy E2E manualne |
| 6-7 | Bug fixes |
| 7-8 | README + dokumentacja |

---

## Kryteria sukcesu POC

- [ ] `curl -X POST /api/login_check` zwraca JWT token
- [ ] CRUD członków działa przez API i frontend
- [ ] Przycisk "Oznacz zaległe" zmienia status składek
- [ ] `vendor/bin/phpunit` - wszystkie testy zielone
- [ ] GitHub Actions pipeline przechodzi

---

## Czego NIE MA w POC

- Multi-tenant (izolacja jednostek)
- Moduły: odznaczenia, wyposażenie, finanse
- Powiadomienia email
- Raporty i eksporty
- Strona publiczna
- Zaawansowane role i uprawnienia