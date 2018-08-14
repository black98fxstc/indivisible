FROM php:apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY legiscan               /var/www/html/legiscan
COPY IndivisiblePlugin.php  /var/www/html/legiscan/
COPY indv-plugin.php        /var/www/html/legiscan/

COPY legiscan-cron    /usr/local/bin/
COPY legiscan-entry   /usr/local/bin/

RUN set -ex; \
	chown -R www-data:www-data /var/www/html/legiscan && \
	chgrp www-data /usr/local/bin/legiscan-cron

CMD ["legiscan-entry"]
