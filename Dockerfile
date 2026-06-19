FROM php:8.3-fpm

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql opcache zip

# Nginx
RUN apt-get update && apt-get install -y --no-install-recommends nginx cron supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP config
RUN echo 'upload_max_filesize=50M\npost_max_size=55M\nmemory_limit=256M\nmax_execution_time=60' > /usr/local/etc/php/conf.d/app.ini

WORKDIR /app
COPY . /app

# Nginx config
RUN echo 'server { listen ${PORT:-8080}; root /app; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; } }' > /etc/nginx/sites-enabled/default

# Supervisor config
RUN mkdir -p /etc/supervisor/conf.d
RUN echo '[supervisord]\nnodaemon=true\n\n[program:php-fpm]\ncommand=docker-php-entrypoint php-fpm\nautostart=true\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\n\n[program:cron]\ncommand=cron -f\nautostart=true' > /etc/supervisor/conf.d/supervisord.conf

# Crontab
COPY docker/crontab /etc/cron.d/app
RUN chmod 0644 /etc/cron.d/app

RUN mkdir -p storage && chmod -R 777 storage

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
