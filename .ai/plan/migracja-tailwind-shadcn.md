# Technical Task Plan – Migracja Frontend na Tailwind + shadcn/ui

## 1. Scope Summary

- **Opis:** Pełna migracja frontendu z custom CSS (App.css) na Tailwind CSS utility classes + komponenty shadcn/ui
- **Faza:** MVP (Polish)
- **Explicit inclusions:**
  - Instalacja komponentów shadcn/ui (Button, Card, Table, Input, Select, Badge, Alert, Form)
  - Migracja 8 komponentów React na Tailwind + shadcn/ui
  - Usunięcie App.css po zakończeniu migracji
  - Zachowanie kolorystyki OSP (czerwień #c41e3a jako primary)
- **Explicit exclusions:**
  - Zmiana funkcjonalności komponentów
  - Zmiana logiki biznesowej
  - Nowe funkcje

## 2. Current State Analysis

### Już skonfigurowane:
- ✅ Tailwind CSS 4.1.18 + @tailwindcss/vite
- ✅ shadcn/ui components.json (style: "new-york")
- ✅ lib/utils.ts z funkcją cn()
- ✅ clsx + tailwind-merge + class-variance-authority
- ✅ lucide-react (ikony)
- ✅ Zmienne CSS w index.css (oklch colors, radius)

### Do migracji:
- App.css: 635 linii, 101 klas CSS
- 8 komponentów React używających custom CSS

## 3. Task Breakdown

### Stage 1 – Przygotowanie infrastruktury shadcn/ui

#### Task 1.1 – Dostosowanie kolorystyki OSP w index.css
- **Opis:** Zmiana --primary na kolor OSP (#c41e3a)
- **Pliki:** `frontend/src/index.css`
- **Detale:** Konwersja #c41e3a na oklch format dla zmiennej --primary

#### Task 1.2 – Instalacja komponentów shadcn/ui
- **Opis:** Dodanie wymaganych komponentów
- **Komponenty do instalacji:**
  - `button` - przyciski (btn, btn-primary, btn-danger, btn-warning, btn-small)
  - `card` - karty (dashboard-card, summary-card, member-form)
  - `table` - tabele (table, th, td)
  - `input` - pola formularzy
  - `select` - dropdowny (filtry)
  - `badge` - statusy (status-badge)
  - `alert` - komunikaty (error-message, success-message, overdue-summary)
  - `label` - etykiety formularzy
  - `textarea` - pola tekstowe
- **Komenda:** `npx shadcn@latest add button card table input select badge alert label textarea`

---

### Stage 2 – Migracja komponentów podstawowych

#### Task 2.1 – Migracja Layout.jsx
- **Obecne klasy:** app-layout, app-header, header-brand, header-nav, header-user, app-main, app-footer
- **Shadcn/ui:** Tailwind utilities dla layout
- **Pliki:** `frontend/src/components/Layout.jsx`

#### Task 2.2 – Migracja LoginForm.jsx
- **Obecne klasy:** login-container, login-form, demo-credentials, demo-buttons, demo-btn.*
- **Shadcn/ui:** Card, Input, Button, Label
- **Pliki:** `frontend/src/components/LoginForm.jsx`

#### Task 2.3 – Migracja Dashboard (w App.jsx)
- **Obecne klasy:** dashboard, dashboard-links, dashboard-card
- **Shadcn/ui:** Card
- **Pliki:** `frontend/src/App.jsx`

---

### Stage 3 – Migracja list CRUD

#### Task 3.1 – Migracja MemberList.jsx
- **Obecne klasy:** list-header, filters, filter-group, search-input, actions, status-badge, pagination
- **Shadcn/ui:** Table, Input, Select, Button, Badge
- **Pliki:** `frontend/src/components/MemberList.jsx`

#### Task 3.2 – Migracja MemberForm.jsx
- **Obecne klasy:** member-form, form-row, form-group, form-actions
- **Shadcn/ui:** Card, Input, Select, Button, Label, Textarea
- **Pliki:** `frontend/src/components/MemberForm.jsx`

#### Task 3.3 – Migracja FeeList.jsx
- **Obecne klasy:** fee-list, overdue-summary, status-badge
- **Shadcn/ui:** Table, Select, Button, Badge, Alert
- **Pliki:** `frontend/src/components/FeeList.jsx`

---

### Stage 4 – Migracja pozostałych komponentów

#### Task 4.1 – Migracja DecorationList.jsx
- **Shadcn/ui:** Table, Select, Button, Badge, Card (formularz)
- **Pliki:** `frontend/src/components/DecorationList.jsx`

#### Task 4.2 – Migracja EquipmentList.jsx
- **Shadcn/ui:** Table, Select, Button, Badge, Card (formularz)
- **Pliki:** `frontend/src/components/EquipmentList.jsx`

#### Task 4.3 – Migracja FinancialList.jsx
- **Obecne klasy:** financial-summary, summary-cards, summary-card (income/expense/balance)
- **Shadcn/ui:** Table, Select, Button, Card (formularz + summary cards), Badge
- **Pliki:** `frontend/src/components/FinancialList.jsx`

---

### Stage 5 – Finalizacja

#### Task 5.1 – Usunięcie App.css
- **Opis:** Po zakończeniu migracji wszystkich komponentów, usunięcie pliku
- **Pliki:** `frontend/src/App.css`
- **Weryfikacja:** Import App.css usunięty z App.jsx

#### Task 5.2 – Testy wizualne i build
- **Opis:** Weryfikacja wszystkich widoków, responsywności, build produkcyjny
- **Komendy:** `npm run build`, `npm run dev`

---

## 4. Mapowanie klas CSS → Tailwind/shadcn

| Obecna klasa | Zamiennik |
|--------------|-----------|
| `.btn` | `<Button variant="outline">` |
| `.btn-primary` | `<Button>` (default primary) |
| `.btn-danger` | `<Button variant="destructive">` |
| `.btn-warning` | `<Button variant="warning">` (custom) |
| `.btn-small` | `<Button size="sm">` |
| `.status-badge` | `<Badge variant="...">` |
| `.error-message` | `<Alert variant="destructive">` |
| `.success-message` | `<Alert>` (default) |
| `.dashboard-card` | `<Card>` z hover effects |
| `.summary-card` | `<Card>` z border-left color |
| `table, th, td` | `<Table>, <TableHeader>, <TableBody>, <TableRow>, <TableCell>` |
| `.form-group` | `<div className="space-y-2">` + `<Label>` + `<Input>` |
| `.login-container` | `<Card className="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">` |

## 5. Progress Tracking

| Task | Status | Data |
|------|--------|------|
| 1.1 Kolorystyka OSP | ✅ Zrobione | 2026-01-31 |
| 1.2 Instalacja shadcn/ui | ✅ Zrobione | 2026-01-31 |
| 2.1 Layout.jsx | ✅ Zrobione | 2026-01-31 |
| 2.2 LoginForm.jsx | ✅ Zrobione | 2026-01-31 |
| 2.3 Dashboard | ✅ Zrobione | 2026-01-31 |
| 3.1 MemberList.jsx | ✅ Zrobione | 2026-01-31 |
| 3.2 MemberForm.jsx | ✅ Zrobione | 2026-01-31 |
| 3.3 FeeList.jsx | ✅ Zrobione | 2026-01-31 |
| 4.1 DecorationList.jsx | ✅ Zrobione | 2026-01-31 |
| 4.2 EquipmentList.jsx | ✅ Zrobione | 2026-01-31 |
| 4.3 FinancialList.jsx | ✅ Zrobione | 2026-01-31 |
| 5.1 Usunięcie App.css | ✅ Zrobione | 2026-01-31 |
| 5.2 Testy i build | ✅ Zrobione | 2026-01-31 |

## 6. Kolejność implementacji (3x3)

### Runda 1 (3 kroki):
1. Task 1.1 – Dostosowanie kolorystyki OSP
2. Task 1.2 – Instalacja komponentów shadcn/ui
3. Task 2.1 – Migracja Layout.jsx

### Runda 2 (3 kroki):
4. Task 2.2 – Migracja LoginForm.jsx
5. Task 2.3 – Migracja Dashboard
6. Task 3.1 – Migracja MemberList.jsx

### Runda 3 (3 kroki):
7. Task 3.2 – Migracja MemberForm.jsx
8. Task 3.3 – Migracja FeeList.jsx
9. Task 4.1 – Migracja DecorationList.jsx

### Runda 4 (3 kroki):
10. Task 4.2 – Migracja EquipmentList.jsx
11. Task 4.3 – Migracja FinancialList.jsx
12. Task 5.1 – Usunięcie App.css

### Runda 5 (1 krok):
13. Task 5.2 – Testy i build

## 7. Weryfikacja

Po każdej rundzie:
1. `npm run build` - sprawdzenie czy buduje się bez błędów
2. `npm run dev` - wizualna weryfikacja zmigrowanych komponentów
3. Test responsywności (mobile view)
4. Test funkcjonalności (klikanie przycisków, formularze, filtry)

## 8. Assumptions & Risks

### Assumptions:
- shadcn/ui działa z React 19
- Tailwind CSS 4.1 jest kompatybilny z shadcn/ui
- Komponenty shadcn/ui będą generowane w `src/components/ui/`

### Risks:
- Potencjalne konflikty stylów podczas migracji (rozwiązanie: migrować komponent po komponencie)
- Różnice w wyglądzie (rozwiązanie: dostosować warianty shadcn/ui)