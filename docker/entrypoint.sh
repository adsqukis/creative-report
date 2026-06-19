#!/bin/sh
set -e

# Railway injects PORT at runtime; default to 8080 for local docker run/testing.
PORT="${PORT:-8080}"

# Apache must listen on the port Railway expects, not the hardcoded image default.
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

# cron runs as its own daemon with a near-empty environment, so it never sees
# the env vars Railway injects into this entrypoint process (MYSQLHOST,
# DEEPSEEK_API_KEY, APP_URL, etc). Dump them to /etc/environment so the
# "www-data . /etc/environment" line in docker/crontab can source them
# before running each PHP cron script. Values are quoted to survive spaces
# or special characters (DB passwords commonly contain punctuation).
: > /etc/environment
printenv | grep -E '^(MYSQL|DEEPSEEK|FONNTE|APP_|AI_)' | while IFS='=' read -r key val; do
    safe_val=$(printf '%s' "$val" | sed 's/"/\\"/g')
    printf 'export %s="%s"\n' "$key" "$safe_val" >> /etc/environment
done

echo "Starting Creative Ops — Apache on port ${PORT}, cron active"

# Railway Volumes mount as root-owned by default, which would block www-data
# (the Apache/PHP user) from writing uploaded files. Fix ownership at boot,
# every time, since a fresh volume resets this on each new mount.
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
