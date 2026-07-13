# TestServes Production Notes: AWS EC2 + Nginx

Use this as the deployment checklist for the current single-EC2 Nginx setup. At higher traffic, place the instance behind an AWS Application Load Balancer and move sessions, cache, and queues to a shared Redis service.

## Laravel Environment

Production should use these values in `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://testserves.com
APP_FORCE_HTTPS=true

TESTSERVES_PORTAL_SCHEME=https

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_CONNECTION=default
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.testserves.com
```

Run these after every deploy:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

Adjust the PHP-FPM service name if the EC2 instance uses another PHP version.

## Nginx Server Block

Point Nginx at Laravel's `public` directory only. Do not serve the project root.

```nginx
server {
    listen 80;
    server_name testserves.com www.testserves.com *.testserves.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name testserves.com www.testserves.com *.testserves.com;

    root /var/www/testserves/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    client_max_body_size 20M;

    gzip on;
    gzip_comp_level 5;
    gzip_types text/plain text/css application/json application/javascript application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* \.(?:css|js|jpg|jpeg|gif|png|webp|svg|ico|woff2?)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## PHP-FPM Starting Point

Size PHP-FPM against real EC2 memory, then load test. A safe initial pool for a 2 GB instance is usually modest:

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 4
pm.max_spare_servers = 8
pm.max_requests = 1000
```

For 2,000 sustained requests per second, do not rely on one EC2 instance. Use an ALB, multiple app instances, Redis/ElastiCache, RDS with read replicas if reads dominate, CloudFront for static assets, and queue workers separated from web traffic.

## Queue Workers

Run Redis-backed queues under Supervisor or systemd. Example Supervisor program:

```ini
[program:testserves-worker]
command=php /var/www/testserves/artisan queue:work redis --sleep=1 --tries=3 --timeout=90 --max-jobs=1000
directory=/var/www/testserves
user=www-data
numprocs=2
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/testserves-worker.log
```

Reload workers after deployment:

```bash
php artisan queue:restart
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart testserves-worker:*
```

## Verification

```bash
curl -I https://testserves.com
php artisan about
php artisan queue:failed
composer audit
```

Expected: HTTPS responses include security headers, Laravel reports cached config/routes/views after deploy, the failed jobs table is empty or understood, and Composer reports no advisories.
