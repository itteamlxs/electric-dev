FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip zip curl cron \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "0 */5 * * * cd /var/www/html && /var/www/html/scripts/cron-update.sh >> /var/www/html/storage/logs/cron.log 2>&1" > /etc/cron.d/electricity-update \
    && chmod 0644 /etc/cron.d/electricity-update \
    && crontab /etc/cron.d/electricity-update

COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
