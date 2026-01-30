# Technical Task Plan – Encje + Migracje (Dzień 1, Punkt 3)

## 1. Scope Summary

- **Opis:** Utworzenie encji domenowych Member i MembershipFee z API Platform oraz enumów statusów
- **Faza:** POC
- **Zakres:**
  - Enum `MembershipStatus` (statusy członkostwa)
  - Enum `FeeStatus` (statusy składek)
  - Encja `Member` z atrybutem `#[ApiResource]`
  - Encja `MembershipFee` z atrybutem `#[ApiResource]`
  - Migracje Doctrine
- **Wykluczenia:**
  - Logika biznesowa walidacji składek (punkt 4)
  - Fixtures (punkt 5)

## 2. Related Requirements

### PRD References
- **Sekcja 5.1 - Moduł: Ewidencja członków** (M-1 do M-5)
- **Sekcja 5.2 - Moduł: Składki członkowskie** (S-1 do S-4)

### Model danych (z plan.md)

**Member:**
| Pole | Typ | Wymagane |
|------|-----|----------|
| id | int | auto |
| firstName | string(100) | tak |
| lastName | string(100) | tak |
| pesel | string(11) | tak, unique |
| address | text | nie |
| phone | string(20) | nie |
| email | string(255) | nie |
| birthDate | date | tak |
| joinDate | date | tak |
| deathDate | date | nie |
| membershipStatus | enum | tak |
| boardPosition | string(100) | nie |

**MembershipFee:**
| Pole | Typ | Wymagane |
|------|-----|----------|
| id | int | auto |
| member_id | FK | tak |
| year | int | tak |
| amount | decimal(10,2) | tak |
| status | enum | tak |
| paidAt | date | nie |

## 3. Task Breakdown

### Stage 1 – Enumy

#### Task 1.1 – MembershipStatus enum
- **Wartości:** active, inactive, honorary, supporting, youth, removed, deceased
- **Plik:** `backend/src/Enum/MembershipStatus.php`

#### Task 1.2 – FeeStatus enum
- **Wartości:** unpaid, paid, overdue, exempt, not_applicable
- **Plik:** `backend/src/Enum/FeeStatus.php`

### Stage 2 – Encja Member

#### Task 2.1 – Member entity
- PHP 8.4 property hooks
- `#[ApiResource]` z API Platform
- Walidacja (NotBlank, Length, Regex dla PESEL)
- Relacja OneToMany do MembershipFee
- **Plik:** `backend/src/Entity/Member.php`

### Stage 3 – Encja MembershipFee

#### Task 3.1 – MembershipFee entity
- PHP 8.4 property hooks
- `#[ApiResource]` z API Platform
- Relacja ManyToOne do Member
- **Plik:** `backend/src/Entity/MembershipFee.php`

### Stage 4 – Migracje

#### Task 4.1 – Generowanie i wykonanie migracji
- `doctrine:migrations:diff`
- `doctrine:migrations:migrate`

## 4. Implementation Notes

- Użyć PHP 8.4 backed enums ze string values
- Użyć asymmetric visibility `public private(set)` dla id
- Użyć property hooks dla computed properties (np. fullName)
- API Platform 4.x wymaga atrybutu `#[ApiResource]` na encji
