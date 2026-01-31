# OSP.plus - Instrukcja wdrożenia

## Wymagania systemowe

### Serwer

- PHP 8.4+
- PostgreSQL 15+
- Composer 2.x
- Node.js 20+ (do budowania frontendu)
- Nginx lub Apache

### Rozszerzenia PHP

- pdo_pgsql
- intl
- mbstring
- openssl
- json

---

## Instalacja

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/your-org/osp-plus.git
cd osp-plus
```

### 2. Konfiguracja backendu

```bash
cd backend

# Instalacja zależności
composer install --no-dev --optimize-autoloader

# Kopiowanie konfiguracji środowiska
cp .env .env.local
```

Edytuj `.env.local`:

```env
APP_ENV=prod
APP_SECRET=<wygeneruj-losowy-klucz-32-znaki>
DATABASE_URL="postgresql://user:password@localhost:5432/osp_plus?serverVersion=15"
```

### 3. Generowanie kluczy JWT

```bash
# Utwórz katalog na klucze
mkdir -p config/jwt

# Wygeneruj klucz prywatny
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

# Wygeneruj klucz publiczny
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# Ustaw uprawnienia
chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

Dodaj do `.env.local`:

```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<hasło-użyte-przy-generowaniu-klucza>
```

### 4. Utworzenie bazy danych

```bash
# Utwórz bazę danych
php bin/console doctrine:database:create

# Wykonaj migracje
php bin/console doctrine:migrations:migrate --no-interaction

# Załaduj słowniki (kategorie, typy odznaczeń, typy wyposażenia)
php bin/console doctrine:fixtures:load --group=dictionary --no-interaction
```

### 5. Utworzenie użytkownika administratora

```bash
php bin/console app:create-user admin@twoja-jednostka.pl haslo123 ROLE_ADMIN
```

### 6. Budowanie frontendu

```bash
cd ../frontend

# Instalacja zależności
npm ci

# Konfiguracja API URL
echo "VITE_API_URL=https://api.twoja-jednostka.osp.plus" > .env.production.local

# Build produkcyjny
npm run build
```

### 7. Konfiguracja serwera WWW

#### Nginx (zalecane)

```nginx
# Backend API
server {
    listen 443 ssl http2;
    server_name api.twoja-jednostka.osp.plus;

    root /var/www/osp-plus/backend/public;

    ssl_certificate /etc/letsencrypt/live/api.twoja-jednostka.osp.plus/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.twoja-jednostka.osp.plus/privkey.pem;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}

# Frontend
server {
    listen 443 ssl http2;
    server_name twoja-jednostka.osp.plus;

    root /var/www/osp-plus/frontend/dist;
    index index.html;

    ssl_certificate /etc/letsencrypt/live/twoja-jednostka.osp.plus/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/twoja-jednostka.osp.plus/privkey.pem;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 8. Uprawnienia katalogów

```bash
# Backend
chown -R www-data:www-data /var/www/osp-plus/backend/var
chmod -R 775 /var/www/osp-plus/backend/var
```

### 9. Czyszczenie cache

```bash
cd /var/www/osp-plus/backend
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## Aktualizacja

### 1. Pobranie nowej wersji

```bash
cd /var/www/osp-plus
git pull origin main
```

### 2. Aktualizacja backendu

```bash
cd backend
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
```

### 3. Aktualizacja frontendu

```bash
cd ../frontend
npm ci
npm run build
```

---

## Backup

### Codzienny backup bazy danych

Dodaj do crontab (`crontab -e`):

```cron
0 2 * * * pg_dump -U osp_user osp_plus | gzip > /backups/osp_plus_$(date +\%Y\%m\%d).sql.gz
```

### Przechowywanie 30 dni

```cron
0 3 * * * find /backups -name "osp_plus_*.sql.gz" -mtime +30 -delete
```

### Restore z backupu

```bash
gunzip -c /backups/osp_plus_20260131.sql.gz | psql -U osp_user osp_plus
```

---

## Monitorowanie

### Logi aplikacji

```bash
# Backend logs
tail -f /var/www/osp-plus/backend/var/log/prod.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

### Sprawdzenie statusu

```bash
# PHP-FPM
systemctl status php8.4-fpm

# Nginx
systemctl status nginx

# PostgreSQL
systemctl status postgresql
```

---

## Rozwiązywanie problemów

### Błąd 500 - Internal Server Error

1. Sprawdź logi: `tail -f backend/var/log/prod.log`
2. Sprawdź uprawnienia: `ls -la backend/var/`
3. Wyczyść cache: `php bin/console cache:clear --env=prod`

### Błąd 401 - Unauthorized

1. Sprawdź czy klucze JWT istnieją: `ls -la backend/config/jwt/`
2. Sprawdź czy JWT_PASSPHRASE jest poprawne w `.env.local`

### Błąd połączenia z bazą danych

1. Sprawdź czy PostgreSQL działa: `systemctl status postgresql`
2. Sprawdź DATABASE_URL w `.env.local`
3. Sprawdź dostęp: `psql -U osp_user -d osp_plus -c "SELECT 1"`

### Frontend nie ładuje się

1. Sprawdź czy pliki są w `frontend/dist/`
2. Sprawdź konfigurację Nginx
3. Sprawdź VITE_API_URL w `.env.production.local`

---

## Kontakt

W przypadku problemów technicznych skontaktuj się z zespołem deweloperskim.
