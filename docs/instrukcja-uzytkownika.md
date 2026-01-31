# OSP.plus - Instrukcja użytkownika

## Spis treści

1. [Logowanie](#logowanie)
2. [Role i uprawnienia](#role-i-uprawnienia)
3. [Moduły systemu](#moduły-systemu)
4. [Ewidencja członków](#ewidencja-członków)
5. [Składki członkowskie](#składki-członkowskie)
6. [Odznaczenia](#odznaczenia)
7. [Wyposażenie osobiste](#wyposażenie-osobiste)
8. [Ewidencja finansowa](#ewidencja-finansowa)

---

## Logowanie

1. Wejdź na stronę systemu OSP.plus
2. Wprowadź swój adres email i hasło
3. Kliknij przycisk "Zaloguj"

Po zalogowaniu zobaczysz panel główny z dostępnymi modułami.

### Konta demo (tylko środowisko testowe)

Na stronie logowania dostępne są przyciski szybkiego logowania dla kont demo:
- **Administrator** - pełny dostęp do wszystkich funkcji
- **Prezes** - zarządzanie członkami i odznaczeniami
- **Skarbnik** - zarządzanie składkami i finansami
- **Naczelnik** - zarządzanie członkami i wyposażeniem
- **Druh** - tylko podgląd swoich danych

---

## Role i uprawnienia

System rozróżnia 5 ról użytkowników:

| Rola | Opis |
|------|------|
| Administrator | Pełny dostęp do wszystkich funkcji systemu |
| Prezes | Zarządzanie członkami (edycja), odznaczeniami, podgląd finansów |
| Skarbnik | Zarządzanie składkami i ewidencją finansową |
| Naczelnik | Zarządzanie członkami (edycja) i wyposażeniem osobistym |
| Druh | Podgląd własnych danych (członkostwo, składki, odznaczenia, wyposażenie) |

### Szczegółowa tabela uprawnień

| Moduł | Administrator | Prezes | Skarbnik | Naczelnik | Druh |
|-------|---------------|--------|----------|-----------|------|
| Członkowie | Pełny CRUD | Odczyt + Edycja | Odczyt | Odczyt + Edycja | Odczyt (własne) |
| Składki | Pełny CRUD | Odczyt | Pełny CRUD | Odczyt | Odczyt (własne) |
| Odznaczenia | Pełny CRUD | Odczyt + Edycja | Odczyt | Odczyt | Odczyt (własne) |
| Wyposażenie | Pełny CRUD | Odczyt | Odczyt | Pełny CRUD | Odczyt (własne) |
| Finanse | Pełny CRUD | Odczyt | Pełny CRUD | Odczyt | Brak dostępu |

---

## Moduły systemu

Po zalogowaniu dostępny jest panel główny z kafelkami prowadzącymi do poszczególnych modułów:

- **Członkowie** - ewidencja członków jednostki OSP
- **Składki** - zarządzanie składkami członkowskimi
- **Odznaczenia** - ewidencja odznaczeń przyznanych członkom
- **Wyposażenie** - wyposażenie osobiste przydzielone członkom
- **Finanse** - ewidencja przychodów i kosztów (tylko dla uprawnionych)

---

## Ewidencja członków

### Przeglądanie listy członków

1. Kliknij "Członkowie" w menu lub na panelu głównym
2. Lista pokazuje wszystkich członków z podstawowymi danymi
3. Użyj wyszukiwarki aby znaleźć członka po nazwisku
4. Użyj filtra statusu aby wyświetlić tylko członków o określonym statusie

### Statusy członkostwa

- **Aktywny** - czynny członek jednostki
- **Nieaktywny** - członek nieaktywny
- **Honorowy** - członek honorowy
- **Wspierający** - członek wspierający
- **MDP** - członek Młodzieżowej Drużyny Pożarniczej
- **Usunięty** - członek skreślony z listy
- **Zmarły** - członek zmarły

### Dodawanie nowego członka (tylko Administrator)

1. Kliknij przycisk "+ Dodaj członka"
2. Wypełnij formularz:
   - Imię i nazwisko (wymagane)
   - PESEL (wymagane, unikalny)
   - Data urodzenia (wymagane)
   - Data wstąpienia (wymagane)
   - Status członkostwa
   - Dane kontaktowe (opcjonalne)
   - Funkcja w zarządzie (opcjonalne)
3. Kliknij "Zapisz"

### Edycja członka (Administrator, Prezes, Naczelnik)

1. Znajdź członka na liście
2. Kliknij przycisk "Edytuj"
3. Zmień potrzebne dane
4. Kliknij "Zapisz"

---

## Składki członkowskie

### Przeglądanie składek

1. Kliknij "Składki" w menu
2. Lista pokazuje wszystkie składki z informacją o członku, roku i statusie
3. Użyj filtrów aby wyświetlić składki za konkretny rok lub o określonym statusie

### Statusy składek

- **Nieopłacona** - składka oczekuje na opłacenie
- **Opłacona** - składka została opłacona
- **Zaległa** - składka nieopłacona po terminie (po 31 marca)
- **Zwolniony** - członek zwolniony ze składki
- **Nie dotyczy** - składka nie dotyczy (np. członek honorowy)

### Oznaczanie zaległych składek (Administrator, Skarbnik)

1. Kliknij przycisk "Oznacz zaległe składki"
2. System automatycznie oznaczy jako zaległe wszystkie nieopłacone składki po terminie
3. Wyświetli się komunikat z liczbą oznaczonych składek

### Podsumowanie zaległości

Na górze strony wyświetla się podsumowanie zaległych składek z listą członków zalegających z opłatami.

---

## Odznaczenia

### Przeglądanie odznaczeń

1. Kliknij "Odznaczenia" w menu
2. Lista pokazuje wszystkie przyznane odznaczenia
3. Użyj filtra aby wyświetlić odznaczenia konkretnego członka

### Rodzaje odznaczeń

**Odznaczenia OSP:**
- Odznaka "Strażak Wzorowy"
- Medal "Za Zasługi dla Pożarnictwa" (brązowy, srebrny, złoty)
- Odznaka "Za wysługę lat" (5, 10, 15, 20, 25, 30, 35, 40, 45, 50 lat)
- Złoty Znak Związku OSP RP

**Odznaczenia państwowe:**
- Medal za Długoletnią Służbę
- Krzyż Zasługi

### Dodawanie odznaczenia (Administrator, Prezes)

1. Kliknij przycisk "Dodaj odznaczenie"
2. Wybierz członka
3. Wybierz rodzaj odznaczenia
4. Podaj datę przyznania
5. Opcjonalnie: nadający, numer legitymacji, uwagi
6. Kliknij "Zapisz"

---

## Wyposażenie osobiste

### Przeglądanie wyposażenia

1. Kliknij "Wyposażenie" w menu
2. Lista pokazuje całe przydzielone wyposażenie
3. Użyj filtra aby wyświetlić wyposażenie konkretnego członka

### Rodzaje wyposażenia

**Odzież:**
- Ubranie specjalne (kurtka)
- Ubranie specjalne (spodnie)
- Buty specjalne
- Rękawice

**Ochronne:**
- Hełm strażacki
- Kominiarka
- Pas strażacki

**Inne:**
- Latarka

### Przypisywanie wyposażenia (Administrator, Naczelnik)

1. Kliknij przycisk "Przypisz wyposażenie"
2. Wybierz członka
3. Wybierz rodzaj wyposażenia
4. Podaj datę wydania
5. Opcjonalnie: rozmiar, numer seryjny, uwagi
6. Kliknij "Zapisz"

---

## Ewidencja finansowa

> **Uwaga:** Moduł finansowy jest dostępny tylko dla Administratora, Prezesa, Skarbnika i Naczelnika.

### Podsumowanie finansowe

Na górze strony wyświetlane są trzy karty:
- **Przychody** - suma wszystkich przychodów
- **Koszty** - suma wszystkich kosztów
- **Bilans** - różnica (przychody - koszty)

### Przeglądanie operacji

1. Kliknij "Finanse" w menu
2. Lista pokazuje wszystkie operacje finansowe
3. Użyj filtrów:
   - Typ: Przychody / Koszty / Wszystkie
   - Rok: wybierz rok do wyświetlenia

### Kategorie przychodów

- Dotacja z gminy
- Składki członkowskie
- Darowizny
- Wynajem remizy
- Inne przychody

### Kategorie kosztów

- Paliwo
- Przeglądy i naprawy pojazdów
- Zakup sprzętu
- Szkolenia
- Ubezpieczenia
- Media i utrzymanie remizy
- Inne koszty

### Dodawanie operacji finansowej (Administrator, Skarbnik)

1. Kliknij przycisk "Dodaj operację"
2. Wybierz typ: Przychód lub Koszt
3. Wybierz kategorię (lista zmienia się w zależności od typu)
4. Podaj kwotę (w PLN)
5. Podaj datę operacji
6. Podaj opis operacji
7. Opcjonalnie: numer dokumentu (np. FV/2024/0123)
8. Kliknij "Zapisz"

---

## Nawigacja

### Menu główne

Menu w górnej części ekranu zawiera linki do wszystkich modułów. Widoczność linków zależy od uprawnień użytkownika.

### Informacje o użytkowniku

W prawym górnym rogu wyświetlane są:
- Adres email zalogowanego użytkownika
- Nazwa roli (np. "Administrator", "Skarbnik")
- Przycisk "Wyloguj"

### Wylogowanie

Kliknij przycisk "Wyloguj" w prawym górnym rogu aby zakończyć sesję.

---

## Wsparcie techniczne

W przypadku problemów z systemem skontaktuj się z administratorem jednostki.
