# Faza MVP - Minimum Viable Product

> **Cel:** Wdrożenie systemu we własnej jednostce OSP
> **Czas realizacji:** ~3-4 miesiące (15-20h/tydzień)
> **Użytkownicy:** ~5 osób (zarząd jednostki)
> **Środowisko:** Produkcyjna subdomena (jednostka.osp.plus)

---

## Definicja sukcesu MVP

1. Zarząd przez 2 miesiące nie używa Excela do ewidencji członków
2. Składki członkowskie rozliczane w systemie za pełny rok
3. Wyposażenie i odznaczenia zewidencjonowane dla wszystkich członków
4. Ewidencja finansowa prowadzona na bieżąco bez rozbieżności
5. System działa stabilnie bez interwencji technicznej

---

## Moduły MVP

### Moduł 1: Ewidencja członków (rozszerzenie POC)

**Encja Member - pełna wersja:**
```
Member
├── id: int
├── firstName: string
├── lastName: string
├── pesel: string (unique)
├── address: text (nullable)
├── phone: string (nullable)
├── email: string (nullable)
├── birthDate: date
├── joinDate: date
├── deathDate: date (nullable)
├── membershipStatus: enum
├── boardPosition: string (nullable)
├── createdAt: datetime
└── updatedAt: datetime
```

**Statusy członkostwa:**
- `active` - aktywny
- `inactive` - nieaktywny
- `honorary` - honorowy
- `supporting` - wspierający
- `youth` - MDP (młodzież)
- `removed` - skreślony
- `deceased` - zmarły

**Funkcjonalności:**
- CRUD członków z walidacją PESEL
- Filtrowanie i wyszukiwanie
- Historia zmian (audit log)
- Import z szablonu CSV/Excel

---

### Moduł 2: Ewidencja składek członkowskich

**Encja MembershipFee:**
```
MembershipFee
├── id: int
├── member: Member (FK)
├── year: int
├── amount: decimal(10,2)
├── status: enum
├── paidAt: date (nullable)
├── notes: text (nullable)
└── createdAt: datetime
```

**Statusy składki:**
- `unpaid` - nieopłacona
- `paid` - opłacona
- `overdue` - zaległa
- `exempt` - zwolniony
- `not_applicable` - nie dotyczy

**Funkcjonalności:**
- Automatyczne tworzenie składek na nowy rok
- Oznaczanie zaległych (logika z POC)
- Podsumowanie: kto zapłacił, kto zalega
- Generowanie listy do wydruku

---

### Moduł 3: Ewidencja odznaczeń

**Encja Decoration:**
```
Decoration
├── id: int
├── member: Member (FK)
├── type: DecorationDictionary (FK)
├── awardedAt: date
├── awardedBy: string (nullable)
├── certificateNumber: string (nullable)
├── notes: text (nullable)
└── createdAt: datetime

DecorationDictionary
├── id: int
├── name: string
├── category: enum (osp|state|other)
├── requiredYears: int (nullable)
└── sortOrder: int
```

**Słownik odznaczeń OSP:**
- Odznaka "Strażak Wzorowy"
- Medal "Za Zasługi dla Pożarnictwa" (brązowy/srebrny/złoty)
- Odznaka "Za wysługę lat" (5/10/15/20/25/30/35/40/45/50)
- Złoty Znak Związku OSP RP
- inne...

**Funkcjonalności:**
- Przypisywanie odznaczeń do członków
- Historia odznaczeń członka
- Sugestie kandydatów (opcjonalnie, post-MVP)

---

### Moduł 4: Ewidencja wyposażenia osobistego

**Encja PersonalEquipment:**
```
PersonalEquipment
├── id: int
├── member: Member (FK)
├── type: EquipmentDictionary (FK)
├── size: string (nullable)
├── serialNumber: string (nullable)
├── issuedAt: date
├── notes: text (nullable)
└── createdAt: datetime

EquipmentDictionary
├── id: int
├── name: string
├── category: enum (clothing|protective|other)
└── hasSizes: boolean
```

**Typy wyposażenia:**
- Ubranie specjalne (kurtka + spodnie)
- Hełm strażacki
- Buty specjalne
- Rękawice
- Kominiarka
- Latarka
- inne...

**Funkcjonalności:**
- Przypisanie wyposażenia do członka
- Podgląd kto ma co (inwentaryzacja)
- Stan obecny (bez historii cyklu życia)

---

### Moduł 5: Ewidencja finansowa

**Encja FinancialRecord:**
```
FinancialRecord
├── id: int
├── type: enum (income|expense)
├── category: FinancialCategory (FK)
├── amount: decimal(10,2)
├── description: text
├── documentNumber: string (nullable)
├── recordedAt: date
├── createdBy: User (FK)
└── createdAt: datetime

FinancialCategory
├── id: int
├── name: string
├── type: enum (income|expense)
└── sortOrder: int
```

**Kategorie przychodów:**
- Dotacja z gminy
- Składki członkowskie
- Darowizny
- Wynajem remizy
- Inne przychody

**Kategorie kosztów:**
- Paliwo
- Przeglądy i naprawy pojazdów
- Zakup sprzętu
- Szkolenia
- Ubezpieczenia
- Media i utrzymanie remizy
- Inne koszty

**Funkcjonalności:**
- Rejestrowanie przychodów i kosztów
- Podsumowanie miesięczne/roczne
- Bilans (przychody - koszty)

---

## Role i uprawnienia MVP

| Rola | Członkowie | Składki | Odznaczenia | Wyposażenie | Finanse |
|------|------------|---------|-------------|-------------|---------|
| Admin | CRUD | CRUD | CRUD | CRUD | CRUD |
| Prezes | Read + Edit | Read | Read + Edit | Read | Read |
| Skarbnik | Read | CRUD | Read | Read | CRUD |
| Naczelnik | Read + Edit | Read | Read | CRUD | Read |
| Druh | Read (own) | Read (own) | Read (own) | Read (own) | - |

---

## Wymagania niefunkcjonalne

### Bezpieczeństwo
- JWT auth z expiration (1h)
- Hashowanie haseł (bcrypt/argon2)
- HTTPS only
- Walidacja danych wejściowych
- Prepared statements (Doctrine)

### Wydajność
- Czas odpowiedzi API < 500ms
- Paginacja list (20 elementów/strona)
- Indeksy na często wyszukiwanych polach

### Backup
- Codzienny dump bazy danych
- Przechowywanie 30 dni wstecz
- Testowany restore raz na kwartał

### Audit log
- Kto zmienił (user_id)
- Kiedy zmienił (timestamp)
- Co zmienił (before/after)
- Przechowywanie przez 2 lata

---

## Harmonogram MVP

### Miesiąc 1: Fundament
- [ ] Rozszerzenie modułu członków (pełne dane)
- [ ] Moduł składek (pełna funkcjonalność)
- [ ] Import danych z Excel (członkowie + składki)
- [ ] Podstawowy audit log

### Miesiąc 2: Ewidencje
- [ ] Moduł odznaczeń
- [ ] Moduł wyposażenia osobistego
- [ ] Słowniki (odznaczenia, wyposażenie, kategorie)

### Miesiąc 3: Finanse + Role
- [ ] Moduł ewidencji finansowej
- [ ] System ról i uprawnień
- [ ] Widoki dla zwykłego druha

### Miesiąc 4: Polish + Wdrożenie
- [ ] Testy z zarządem jednostki
- [ ] Bug fixes i poprawki UX
- [ ] Dokumentacja użytkownika
- [ ] Oficjalne wdrożenie

---

## Czego NIE MA w MVP

- Multi-tenant (wiele jednostek)
- Moduł akcji ratowniczych
- Moduł wynajmów remizy
- Powiadomienia SMS
- Aplikacja mobilna
- Strona publiczna jednostki
- Integracje zewnętrzne (eRemiza, KSEF)
- Raporty zaawansowane
- Płatności online za składki

---

## Metryki sukcesu (do zbierania)

| Metryka | Cel |
|---------|-----|
| Aktywni użytkownicy tygodniowo | 5 (cały zarząd) |
| Członkowie wprowadzeni | 100% stanu jednostki |
| Składki rozliczone | 100% za bieżący rok |
| Czas wprowadzenia danych | < 30 min/tydzień |
| Zgłoszone błędy | < 5/miesiąc |