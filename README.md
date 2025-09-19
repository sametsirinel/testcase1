#Not
> **Github action süreçlerini atlıyorum. Hem daha öncesinde bu konuda tecrübem olmamasında hemde test süreçlerinde migren ağrısı başladı. Vakit de kısıtlı olduğu için o süreçleri atlıyorum** 

# Mimari Akış 
Client → API → DB
API → Redis cache
API → Queue worker → DB + Cache Invalidation

# Kurulum Çalıştırma
Vendor Klasörü ve env ayarları için 
```sh 
sh ./install.sh 
```
# Api Örnekleri
[Postman Collection](postman.json)

#### Api Pathleri 
> Tüm Makaleleri getir
- GET /api/articles 
> Tüm İlgili Makaleyi getir
- GET /api/articles/:id
> Tüm İlgili Makalenin Yorumlarını getir 
- GET /api/articles/:id/comments
> Yeni Yorum Oluştur
- POST /api/articles/:id/comments

### .env örneği
```.env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:2pWwRFojHV67FXc4vCv0LcHuX8JYNOmQNe/vtbrdGkw=
APP_DEBUG=true
APP_URL=http://localhost
QUEUE_PORT=9999

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=example_app
DB_USERNAME=sail
DB_PASSWORD=password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=redis
CACHE_TTL=60
# CACHE_PREFIX=

MEMCACHED_HOST=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

### Rate Limit Stratejisi
Kullanıcının belirli sayfalarına rate limit getirerek gereksiz ve sürekli yorum yapmasını engelliyoruz  

### Test ve CLI komutları 
Env Kopyalama komutları

```sh
cp .env.example .env
````

Eğer docker varsa vendor klasörünün oluşmasını sağlayan composer dockeri 

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

```

sail komutunun çalışması için takma isim

```sh
alias sail='./vendor/bin/sail '
```

Serveri ve queue veritabanı containerlarını ayağa kaldırmak için kullanılır

```sh
sail up -d
```

test komutlarını çalıştırmak için 
```sh
sail artisan test 
```

