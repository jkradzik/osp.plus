# Product Requirements Document
## osp.plus - Centrum operacyjne dla OSP

| Wersja | Data | Autor |
|--------|------|-------|
| 1.0 | 2025-01 | - |

---

## 1. Podsumowanie wykonawcze

**osp.plus** to kompleksowa platforma SaaS do zarządzania Ochotniczymi Strażami Pożarnymi. System rozwiązuje problem rozproszonych danych administracyjnych jednostek OSP, które obecnie prowadzone są w Excelach, zeszytach lub "w głowach" członków zarządu.

### Wizja produktu

Jedno miejsce, które integruje wszystkie aspekty funkcjonowania jednostki OSP - od ewidencji członków, przez dokumentację akcji ratowniczych, po zarządzanie finansami i wynajmem remizy.

### Model biznesowy

- **SaaS** - dostęp przez przeglądarkę, dane w chmurze
- **Multi-tenant** - każda jednostka ma izolowane dane
- **Subdomeny** - każda jednostka otrzymuje `jednostka.osp.plus`
- **Freemium** - podstawowe funkcje bezpłatnie, rozszerzone w płatnych pakietach

---

## 2. Problem

### Obecna sytuacja

Jednostki OSP zmagają się z rosnącym obciążeniem administracyjnym:

- **Ewidencja członków** - dane osobowe, szkolenia, badania lekarskie, odznaczenia
- **Dokumentacja akcji** - wyjazdy, uczestnicy, raporty, ekwiwalenty
- **Finanse** - składki, budżet, dotacje, rozliczenia z gminą
- **Majątek** - pojazdy, sprzęt, wyposażenie osobiste, przeglądy

Dane te są rozproszone między Excelami, zeszytami i wiedzą kilku osób z zarządu. Brak jednego źródła prawdy prowadzi do:

- Utraty informacji przy zmianach w zarządzie
- Przeterminowanych badań i szkoleń
- Trudności w generowaniu raportów dla gminy
- Nieefektywnego zarządzania majątkiem

### Istniejące rozwiązania

| Produkt | Klienci | Ograniczenia |
|---------|---------|--------------|
| OSPanel | ~650 | Przestarzały UI, brak integracji |
| mOSP | ~700 | Podstawowe funkcje, legacy |
| Total-OSP | ~100 | Desktop only, brak chmury |

**Żadne z nich nie oferuje:**
- Modułu wynajmów remizy (źródło przychodów wielu jednostek)
- Integracji z eRemizą
- Nowoczesnego, responsywnego interfejsu
- Architektury gotowej na mobile

---

## 3. Cele produktu

### Cel główny

Stać się domyślnym narzędziem do zarządzania jednostką OSP w Polsce.

### Cele mierzalne

| Horyzont | Cel | Metryka |
|----------|-----|---------|
| 6 miesięcy | Walidacja produktu | 10 aktywnych jednostek |
| 12 miesięcy | Product-market fit | 100 jednostek, 50% retencji |
| 24 miesiące | Wzrost | 1000 jednostek, MRR 10k PLN |

### Success metrics

- **Aktywacja:** % jednostek z >10 wprowadzonymi członkami
- **Retencja:** % jednostek aktywnych po 3 miesiącach
- **Engagement:** średnia liczba logowań/tydzień
- **NPS:** Net Promoter Score > 50

---

## 4. Grupy użytkowników

### Użytkownicy bezpośredni

| Persona | Rola w OSP | Potrzeby | Częstotliwość użycia |
|---------|------------|----------|---------------------|
| **Prezes** | Zarządza jednostką | Przegląd stanu, raporty | 1-2x/tydzień |
| **Naczelnik** | Dowodzi akcjami | Dokumentacja wyjazdów, gotowość bojowa | 3-5x/tydzień |
| **Skarbnik** | Zarządza finansami | Składki, budżet, rozliczenia | 2-3x/tydzień |
| **Sekretarz** | Dokumentacja | Protokoły, korespondencja | 1-2x/tydzień |
| **Gospodarz** | Zarządza remizą | Wynajem sali, inwentarz | 2-3x/tydzień |
| **Druh** | Członek zwykły | Podgląd swoich danych | 1x/miesiąc |

### Użytkownicy pośredni

- **Gmina** - otrzymuje raporty, rozlicza dotacje
- **PSP/KG PSP** - statystyki, integracja z eRemizą (przyszłość)

---

## 5. Wymagania funkcjonalne

### 5.1 Moduł: Ewidencja członków

**Priorytet:** Krytyczny (MVP)

| ID | User Story | Akceptacja |
|----|------------|------------|
| M-1 | Jako prezes chcę widzieć listę wszystkich członków | Lista z filtrowaniem i wyszukiwaniem |
| M-2 | Jako sekretarz chcę dodać nowego członka | Formularz z walidacją PESEL |
| M-3 | Jako druh chcę zobaczyć swoje dane | Widok profilu własnego |
| M-4 | Jako naczelnik chcę widzieć status szkoleń | Kolumna z datą ważności |
| M-5 | Jako sekretarz chcę importować dane z Excel | Upload + mapowanie kolumn |

**Dane członka:**
- Identyfikacja: imię, nazwisko, PESEL
- Kontakt: adres, telefon, email
- Członkostwo: data wstąpienia, status, funkcja w zarządzie
- Daty: urodzenia, śmierci (opcjonalna)

**Statusy członkostwa:**
`active` | `inactive` | `honorary` | `supporting` | `youth` | `removed` | `deceased`

---

### 5.2 Moduł: Składki członkowskie

**Priorytet:** Krytyczny (MVP)

| ID | User Story | Akceptacja |
|----|------------|------------|
| S-1 | Jako skarbnik chcę widzieć kto zapłacił składkę | Lista ze statusami |
| S-2 | Jako skarbnik chcę oznaczyć składkę jako opłaconą | Zmiana statusu + data |
| S-3 | Jako skarbnik chcę oznaczyć zaległe składki | Batch update po terminie |
| S-4 | Jako prezes chcę widzieć podsumowanie składek | Statystyka: opłacone/zaległe |

**Statusy składki:**
`unpaid` | `paid` | `overdue` | `exempt` | `not_applicable`

**Logika biznesowa:**
- Termin płatności: 31 marca danego roku
- Po terminie: `unpaid` → `overdue` (automatycznie lub na żądanie)
- Wyjątki: `exempt` i `not_applicable` nie podlegają walidacji

---

### 5.3 Moduł: Odznaczenia

**Priorytet:** Wysoki (MVP)

| ID | User Story | Akceptacja |
|----|------------|------------|
| O-1 | Jako sekretarz chcę przypisać odznaczenie | Wybór z słownika + data |
| O-2 | Jako prezes chcę widzieć odznaczenia członka | Lista w profilu członka |
| O-3 | Jako sekretarz chcę widzieć historię odznaczeń | Raport chronologiczny |

**Słownik odznaczeń:** predefiniowany (odznaki OSP, medale, odznaczenia państwowe)

---

### 5.4 Moduł: Wyposażenie osobiste

**Priorytet:** Wysoki (MVP)

| ID | User Story | Akceptacja |
|----|------------|------------|
| W-1 | Jako gospodarz chcę przypisać sprzęt do członka | Formularz z typem i rozmiarem |
| W-2 | Jako naczelnik chcę widzieć kto ma jakie wyposażenie | Raport inwentaryzacyjny |
| W-3 | Jako druh chcę widzieć swoje wyposażenie | Lista w profilu własnym |

**Typy wyposażenia:** ubranie specjalne, hełm, buty, rękawice, kominiarka, latarka

---

### 5.5 Moduł: Ewidencja finansowa

**Priorytet:** Wysoki (MVP)

| ID | User Story | Akceptacja |
|----|------------|------------|
| F-1 | Jako skarbnik chcę zarejestrować przychód | Formularz z kategorią |
| F-2 | Jako skarbnik chcę zarejestrować koszt | Formularz z kategorią |
| F-3 | Jako prezes chcę widzieć bilans | Przychody - koszty |
| F-4 | Jako skarbnik chcę widzieć zestawienie miesięczne | Raport z kategorii |

**Kategorie:** dotacje, składki, darowizny, wynajem | paliwo, naprawy, sprzęt, szkolenia, media

---

### 5.6 Moduł: Akcje ratownicze (post-MVP)

**Priorytet:** Średni

| ID | User Story |
|----|------------|
| A-1 | Jako dowódca chcę udokumentować akcję |
| A-2 | Jako naczelnik chcę widzieć statystyki wyjazdów |
| A-3 | Jako skarbnik chcę wygenerować wniosek o ekwiwalent |

---

### 5.7 Moduł: Wynajem remizy (post-MVP)

**Priorytet:** Średni (wyróżnik konkurencyjny)

| ID | User Story |
|----|------------|
| R-1 | Jako gospodarz chcę widzieć kalendarz rezerwacji |
| R-2 | Jako klient chcę sprawdzić dostępność terminu |
| R-3 | Jako gospodarz chcę wygenerować umowę najmu |

---

## 6. Wymagania niefunkcjonalne

### Bezpieczeństwo

| Wymaganie | Implementacja |
|-----------|---------------|
| Autentykacja | JWT z expiration 1h |
| Autoryzacja | RBAC (role-based access control) |
| Szyfrowanie | HTTPS (TLS 1.3), bcrypt dla haseł |
| RODO | Umowa powierzenia, prawo do usunięcia |

### Wydajność

| Metryka | Cel |
|---------|-----|
| Czas odpowiedzi API | < 500ms (p95) |
| Dostępność | 99.5% (bez planowanych przerw) |
| Równoczesni użytkownicy | 100 na jednostkę |

### Skalowalność

| Horyzont | Skala |
|----------|-------|
| MVP | 1 jednostka, 100 członków |
| 12 miesięcy | 100 jednostek, 10k członków |
| 24 miesiące | 1000 jednostek, 100k członków |

---

## 7. Architektura

```
┌─────────────────────────────────────────────┐
│              KLIENCI                        │
│  ┌─────────┐  ┌─────────┐  ┌─────────────┐ │
│  │ React   │  │ React   │  │ React       │ │
│  │ Admin   │  │ Public  │  │ Native      │ │
│  └────┬────┘  └────┬────┘  └──────┬──────┘ │
└───────┼────────────┼───────────────┼────────┘
        │            │               │
        ▼            ▼               ▼
┌─────────────────────────────────────────────┐
│          REST API (JSON + JWT)              │
│          Symfony 7 + API Platform           │
└─────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────┐
│              PostgreSQL 16                  │
│         (multi-tenant: tenant_id)           │
└─────────────────────────────────────────────┘
```

### Kluczowe decyzje

| Decyzja | Uzasadnienie |
|---------|--------------|
| API-first | Jeden backend dla web, mobile, integracji |
| JWT stateless | Skalowalność, brak sesji na serwerze |
| PostgreSQL | JSON, full-text search, enumy |
| Multi-tenant (shared DB) | Prostota, wystarczająca izolacja |

---

## 8. Roadmapa

### Faza 0: POC (3 dni)
- [ ] Auth + JWT
- [ ] CRUD członków
- [ ] Walidacja składek
- [ ] Unit test
- [ ] CI/CD

### Faza 1: MVP (3-4 miesiące)
- [ ] Pełna ewidencja członków
- [ ] Składki członkowskie
- [ ] Odznaczenia
- [ ] Wyposażenie osobiste
- [ ] Ewidencja finansowa
- [ ] Role i uprawnienia
- [ ] Wdrożenie we własnej jednostce

### Faza 2: Growth (6 miesięcy)
- [ ] Multi-tenant
- [ ] Onboarding dla nowych jednostek
- [ ] Moduł akcji ratowniczych
- [ ] Panel gminy
- [ ] Powiadomienia email

### Faza 3: Scale (12 miesięcy)
- [ ] Moduł wynajmów
- [ ] Aplikacja mobilna
- [ ] Raporty zaawansowane
- [ ] Integracje (KSEF, eRemiza jeśli API dostępne)
- [ ] Płatności online

---

## 9. Ryzyka

| Ryzyko | Prawdop. | Wpływ | Mitygacja |
|--------|----------|-------|-----------|
| Abakus przyspiesza rozwój eRemizy | Średnie | Wysoki | Focus na wyróżniki (wynajem, UX) |
| Brak adopcji przez jednostki | Średnie | Krytyczny | Walidacja z beta-testerami, freemium |
| Konkurencja cenowa | Niskie | Średni | Wartość dodana, nie wyścig cenowy |
| RODO / kary | Niskie | Wysoki | Audyt prawny, dobre praktyki |

---

## 10. Otwarte pytania

1. **Termin płatności składek** - czy 31 marca to standard?
2. **Kwota składki** - stała czy zmienna (ulgi)?
3. **Słownik odznaczeń** - pełna lista do potwierdzenia
4. **Słownik wyposażenia** - pełna lista do potwierdzenia
5. **Hosting produkcyjny** - VPS własny czy managed?

---

## Appendix A: Słownik pojęć

| Termin | Definicja |
|--------|-----------|
| OSP | Ochotnicza Straż Pożarna |
| KSRG | Krajowy System Ratowniczo-Gaśniczy |
| MDP | Młodzieżowa Drużyna Pożarnicza |
| Ekwiwalent | Rekompensata za udział w akcji |
| eRemiza | System alarmowania PSP (Abakus) |
| Druh | Członek OSP |
| Remiza | Siedziba jednostki OSP |