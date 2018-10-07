FROM wordpress:apache

RUN apt-get update && apt-get install -y --no-install-recommends unzip gnupg dirmngr \
	&& rm -rf /var/lib/apt/lists/*

ENV WORDPRESS_CLI_GPG_KEY 63AF7AA15067C05616FDDD88A3A2E8F226F0BC06

ENV WORDPRESS_CLI_VERSION 2.0.1
ENV WORDPRESS_CLI_SHA512 21b9c1d65993f88bf81cc73c0a832532cc424bea8c15563a77af1905d0dc4714f2af679dfadedd3b683f3968902b4b6be4c6cf94285da9f5582b30c1dac5397f

RUN set -ex; \
	\
	curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; \
#	curl -o /usr/local/bin/wp.gpg -fSL "https://github.com/wp-cli/wp-cli/releases/download/v${WORDPRESS_CLI_VERSION}/wp-cli-${WORDPRESS_CLI_VERSION}.phar.gpg"; \
	\
#	export GNUPGHOME="$(mktemp -d)"; \
#	gpg --keyserver ha.pool.sks-keyservers.net --recv-keys "$WORDPRESS_CLI_GPG_KEY"; \
#	gpg --keyserver 192.146.137.98 --recv-keys "$WORDPRESS_CLI_GPG_KEY"; \
#	gpg --batch --decrypt --output /usr/local/bin/wp /usr/local/bin/wp.gpg; \
#	command -v gpgconf && gpgconf --kill all || :; \
#	rm -rf "$GNUPGHOME" /usr/local/bin/wp.gpg; \
	\
#	echo "$WORDPRESS_CLI_SHA512 */usr/local/bin/wp" | sha512sum -c -; \
	chmod +x /usr/local/bin/wp; \
	\
	wp --allow-root --version

ENV GANTRY_VERSION 5.4.26
ENV JETPACK_VERSION  6.5
ENV MAILCHIMP_VERSION 4.2.5
ENV TWOFACTOR_VERSION 1.3.5

RUN echo ${GANTRY_VERSION}; \
        curl -o gantry5.zip -fSL "https://github.com/gantry/gantry5/releases/download/${GANTRY_VERSION}/wordpress-pkg_gantry5_v${GANTRY_VERSION}.zip"; \
	unzip gantry5.zip; \
	mv gantry5 /usr/src/wordpress/wp-content/plugins/; \
	curl -o hydrogen.zip -fSL "https://github.com/gantry/gantry5/releases/download/${GANTRY_VERSION}/wordpress-tpl_g5_hydrogen_v${GANTRY_VERSION}.zip"; \
	unzip hydrogen.zip; \
	mv g5_hydrogen /usr/src/wordpress/wp-content/themes/; \
        curl -o jetpack.zip -fSL "https://downloads.wordpress.org/plugin/jetpack.${JETPACK_VERSION}.zip"; \
        unzip jetpack.zip; \
        mv jetpack /usr/src/wordpress/wp-content/plugins/; \
        curl -o two-factor-authentication.zip -fSL "https://downloads.wordpress.org/plugin/two-factor-authentication.${TWOFACTOR_VERSION}.zip"; \
        unzip two-factor-authentication.zip; \
        mv two-factor-authentication /usr/src/wordpress/wp-content/plugins/; \
        curl -o twitter.zip -fSL "https://downloads.wordpress.org/plugin/twitter.2.0.2.zip"; \
        unzip twitter.zip; \
        mv twitter /usr/src/wordpress/wp-content/plugins/; \
	curl -o mailchimp-for-wp.zip -fSL  https://downloads.wordpress.org/plugin/mailchimp-for-wp.${MAILCHIMP_VERSION}.zip; \
	unzip mailchimp-for-wp.zip; \
	mv mailchimp-for-wp /usr/src/wordpress/wp-content/plugins/;

COPY plugins/indivisible/indivisible.php /usr/src/wordpress/wp-content/plugins/indivisible/indivisible.php
COPY KEYS.js /usr/src/wordpress/wp-content/plugins/indivisible/indv-react/
#COPY plugins/indivisible/indv-react/build/static/js/main.a285be49.js /usr/src/wordpress/wp-content/plugins/indivisible/indv-react.js
#COPY plugins/indivisible/indv-react/build/static/css/main.c17080f1.css /usr/src/wordpress/wp-content/plugins/indivisible/indv-react.css
COPY themes  /usr/src/wordpress/wp-content/themes/

COPY indv-plugin-entry /usr/local/bin/
COPY indv-plugin-cron  /usr/local/bin/

RUN chown -R www-data:www-data /usr/src/wordpress/wp-content

ENTRYPOINT ["indv-plugin-entry"]
CMD ["apache2-foreground"]

