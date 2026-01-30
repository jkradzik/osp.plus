# Technical Task Plan – Unit Testy (Dzień 2, Punkt 1)

## 1. Scope Summary

- **Opis:** Unit testy dla MembershipFeeValidationService
- **Faza:** POC
- **Zakres:**
  - Test `isOverdue()` - różne scenariusze
  - Test `markAsOverdueIfApplicable()`
  - Test `validateAndMarkAllOverdue()`

## 2. Test Cases (~12)

### isOverdue()
1. Składka UNPAID przed deadline (31.03) → false
2. Składka UNPAID po deadline → true
3. Składka UNPAID dokładnie w dniu deadline → false
4. Składka PAID → false
5. Składka OVERDUE → false
6. Składka EXEMPT → false (wyjątek)
7. Składka NOT_APPLICABLE → false (wyjątek)

### markAsOverdueIfApplicable()
8. Oznacza UNPAID po deadline → true, status = OVERDUE
9. Nie oznacza PAID → false
10. Nie oznacza EXEMPT → false

### validateAndMarkAllOverdue()
11. Batch update - oznacza tylko kwalifikujące się
12. Zwraca poprawną liczbę oznaczonych

## 3. Implementation

**Plik:** `backend/tests/Unit/Service/MembershipFeeValidationServiceTest.php`
