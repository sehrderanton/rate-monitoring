FROM php:8.3-cli

RUN apt-get update && apt-get install -y libpq-dev unzip cron

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --quiet

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

#RUN composer dump-autoload --optimize

# Setup the cron job
COPY app-cron/crontab /etc/cron.d/app-cron
RUN chmod 0644 /etc/cron.d/app-cron
RUN crontab /etc/cron.d/app-cron
RUN touch /var/log/cron.log

# Your script to run the cron job
#RUN echo "*/5 * * * * /usr/local/bin/php /app/bin/console app:send-rate >> /var/log/cron.log 2>&1" >> /etc/cron.d/app-cron
# Ensure the file ends with an empty newline
RUN echo "" >> /etc/cron.d/app-cron

# Run the cron daemon as the main container process
CMD cron -f