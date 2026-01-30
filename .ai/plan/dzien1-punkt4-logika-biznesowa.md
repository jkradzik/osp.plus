# Technical Task Plan – Logika Biznesowa (Dzień 1, Punkt 4)

## 1. Scope Summary

- **Opis:** Implementacja serwisu walidacji składek i custom endpointów API
- **Faza:** POC
- **Zakres:**
  - `MembershipFeeValidationService` - logika oznaczania zaległych składek
  - `MembershipFeeController` - custom endpointy poza API Platform CRUD
- **Wykluczenia:**
  - Unit testy (Dzień 2)

## 2. Related Requirements

### PRD References
- **Sekcja 5.2 - Moduł: Składki członkowskie:**
  - S-3: Jako skarbnik chcę oznaczyć zaległe składki (Batch update po terminie)

### Business Rules (z plan.md)

**Reguła:** Składka jest "zaległa" jeśli:
- status = UNPAID
- aktualna data > 31 marca danego roku

**Wyjątki (nie podlegają walidacji):**
- status = EXEMPT (zwolniony)
- status = NOT_APPLICABLE (nie dotyczy)

## 3. Task Breakdown

### Stage 1 – MembershipFeeValidationService

#### Task 1.1 – Serwis walidacji
**Metody:**
- `isOverdue(MembershipFee $fee, ?\DateTimeInterface $referenceDate = null): bool`
- `markAsOverdueIfApplicable(MembershipFee $fee): bool`
- `validateAndMarkAllOverdue(): int` - zwraca liczbę oznaczonych
- `getOverdueFees(): array`

**Plik:** `backend/src/Service/MembershipFeeValidationService.php`

### Stage 2 – MembershipFeeController

#### Task 2.1 – Custom endpointy
- `POST /api/membership-fees/validate-overdue` - uruchamia walidację
- `GET /api/membership-fees/overdue` - zwraca listę zaległych

**Plik:** `backend/src/Controller/MembershipFeeController.php`

## 4. Implementation Notes

- Deadline składki: 31 marca danego roku
- Serwis powinien być łatwo testowalny (dependency injection dla daty)
- Controller zwraca JSON responses
