FROM php:8.3-apache

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql opcache

# System packages: cron + supervisor to run web + cron in one container
RUN apt-get update && apt-get install -y --no-install-recommends \
        cron \
        supervisor \
        libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache: disable conflicting MPMs (php:8.3-apache ships with event+prefork), enable rewrite + headers
RUN a2dismod mpm_event mpm_worker 2>/dev/null; true
RUN a2enmod rewrite headers
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# PHP runtime settings for uploads (designer attaches images/video) and AI calls
RUN { \
        echo 'upload_max_filesize=50M'; \
        echo 'post_max_size=55M'; \
        echo 'memory_limit=256M'; \
        echo 'max_execution_time=60'; \
    } > /usr/local/etc/php/conf.d/creative-ops.ini

WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Crontab — /etc/cron.d format includes the run-as user per line (www-data),
# read directly by cron daemon — no need to register via `crontab` command
COPY docker/crontab /etc/cron.d/creative-ops-cron
RUN chmod 0644 /etc/cron.d/creative-ops-cron

# Supervisor: run Apache (foreground) + cron daemon together in one container
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint: Railway injects $PORT at runtime — Apache must bind to it
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ownership + permissions for storage (uploads/logs need to be writable by www-data)
RUN mkdir -p storage/uploads storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 755 storage

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
