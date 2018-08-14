FROM wordpress:apache

#ENV WORDPRESS_CLI_GPG_KEY 3B9191625F3B1F1BF5DD3B47673A02042F6B6B7F

#ENV WORDPRESS_CLI_VERSION 1.4.1

RUN apt-get update && apt-get install -y --no-install-recommends unzip gnupg dirmngr \
	&& rm -rf /var/lib/apt/lists/*

ENV WORDPRESS_CLI_GPG_KEY 63AF7AA15067C05616FDDD88A3A2E8F226F0BC06

ENV WORDPRESS_CLI_VERSION 2.0.0
ENV WORDPRESS_CLI_SHA512 18c15c792a0747e434f70fb74f8d82dbc6dc504bd6238a4928859430129f12d88d829a1f37203c30813eb5f4d2c69f4e40aa15c74d9c6f01343052f01842463b

RUN set -ex; \
	\
	curl -o /usr/local/bin/wp.gpg -fSL "https://github.com/wp-cli/wp-cli/releases/download/v${WORDPRESS_CLI_VERSION}/wp-cli-${WORDPRESS_CLI_VERSION}.phar.gpg"; \
	\
	export GNUPGHOME="$(mktemp -d)"; \
	gpg --keyserver ha.pool.sks-keyservers.net --recv-keys "$WORDPRESS_CLI_GPG_KEY"; \
	gpg --batch --decrypt --output /usr/local/bin/wp /usr/local/bin/wp.gpg; \
	command -v gpgconf && gpgconf --kill all || :; \
	rm -rf "$GNUPGHOME" /usr/local/bin/wp.gpg; \
	\
	echo "$WORDPRESS_CLI_SHA512 */usr/local/bin/wp" | sha512sum -c -; \
	chmod +x /usr/local/bin/wp; \
	\
	wp --allow-root --version

ENV GANTRY_VERSION 5.4.24
ENV JETPACK_VERSION  6.4.2

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
        curl -o two-factor-authentication.zip -fSL "https://downloads.wordpress.org/plugin/two-factor-authentication.1.3.3.zip"; \
        unzip two-factor-authentication.zip; \
        mv two-factor-authentication /usr/src/wordpress/wp-content/plugins/; \
        curl -o twitter.zip -fSL "https://downloads.wordpress.org/plugin/twitter.2.0.2.zip"; \
        unzip twitter.zip; \
        mv twitter /usr/src/wordpress/wp-content/plugins/; \
	curl -o mailchimp-for-wp.zip -fSL  https://downloads.wordpress.org/plugin/mailchimp-for-wp.4.2.4.zip; \
	unzip mailchimp-for-wp.zip; \
	mv mailchimp-for-wp /usr/src/wordpress/wp-content/plugins/;

COPY plugins /usr/src/wordpress/wp-content/plugins/
COPY themes  /usr/src/wordpress/wp-content/themes/

COPY indv-plugin-entry /usr/local/bin/
COPY indv-plugin-cron  /usr/local/bin/

RUN chown -R www-data:www-data /usr/src/wordpress/wp-content

ENTRYPOINT ["indv-plugin-entry"]
CMD ["apache2-foreground"]

