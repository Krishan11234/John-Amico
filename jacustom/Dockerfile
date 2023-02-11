## @link https://hub.docker.com/r/yousan/php5.6-apache/dockerfile

FROM php:5.6-apache

# install the PHP extensions we need
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libcurl4-openssl-dev && rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
	&& docker-php-ext-install gd mysqli opcache

# @link https://github.com/docker-library/php/issues/331#issuecomment-261016528   Really thanks
RUN docker-php-ext-install curl mysqli opcache pdo pdo_mysql zip

# @link https://hub.docker.com/r/plab/docker-php/~/dockerfile/
RUN apt-get update && apt-get install -y \
	bzip2 \
	libbz2-dev \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libmcrypt-dev \
	libpng-dev \
	&& docker-php-ext-install mcrypt mbstring bz2 zip \
	&& docker-php-ext-configure gd -with-freetype-dir=/usr/include/ -with-jpeg-dir=/usr/include/

# @link https://github.com/docker-library/php/issues/57
RUN apt-get update \
  && apt-get install -y zlib1g-dev libicu-dev g++ \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl


# @link https://github.com/docker-library/php/issues/77#issuecomment-88936146
# RUN pecl install -o -f xdebug && \
  # echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" > /usr/local/etc/php/conf.d/pecl-xdebug.ini

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires vhost_alias

ENV TERM=xterm


# COPY 000-default.conf /etc/apache2/sites-available/
RUN sed -i 's/	AllowOverride None/	AllowOverride All/' /etc/apache2/apache2.conf

VOLUME /var/www/html

COPY entrypoint.sh /usr/local/bin/
# RUN ln -s usr/local/bin/docker-entrypoint.sh /entrypoint.sh # backwards compat

# ENTRYPOINT resets CMD
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]