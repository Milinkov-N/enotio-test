FROM php:8.1-fpm

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y cron supervisor

# Configure cron
RUN crontab -l | { cat; echo "* 3 * * * /usr/local/bin/php /var/www/html/fetch.php >> /var/log/cron.log 2>&1"; } | crontab -

# Configure supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf

COPY ./entrypoint.sh /

ENTRYPOINT /entrypoint.sh
