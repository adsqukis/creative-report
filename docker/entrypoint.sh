#!/bin/sh
set -e

PORT="${PORT:-8080}"

# Fix Apache port — handle both standard and alternative config locations
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf 2>/dev/null || true
fi

# Find and update virtual host config
for conf in /etc/apache2/sites-enabled/*.conf; do
    [ -f "$conf" ] && sed -i "s/:80>/:${PORT}>/g" "$conf" 2>/dev/null || true
done

# Export env vars for cron
: > /etc/environment
printenv 2>/dev/null | grep -E '^(MYSQL|DEEPSEEK|FONNTE|APP_|AI_|DB_)' | while IFS='=' read -r key val; do
    safe_val=$(printf '%s' "$val" | sed 's/"/\\"/g')
    printf 'export %s="%s"\n' "$key" "$safe_val" >> /etc/environment 2>/dev/null || true
done || true

# Fix storage permissions
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true

echo "Starting Creative Ops — Apache on port ${PORT}, cron active"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
