FROM index.alauda.cn/library/php:7.0-fpm
RUN apt-get update && apt-get install -y \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libmcrypt-dev \
	libpng12-dev \
	&& docker-php-ext-install -j$(nproc) iconv mcrypt mysqli zip json opcache bcmath \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install -j$(nproc) pdo_mysql

RUN curl -fsSL 'https://xdebug.org/files/xdebug-2.5.4.tgz' -o xdebug.tar.gz \
	&& mkdir -p xdebug \
	&& tar -xf xdebug.tar.gz -C xdebug --strip-components=1 \
	&& rm xdebug.tar.gz \
	&& ( \
			cd xdebug \
			&& phpize \
			&& ./configure --enable-xdebug \
			&& make -j$(nproc) \
			&& make install \
	   ) \
	&& rm -r xdebug \
	&& docker-php-ext-enable xdebug

RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai  /etc/localtime

# 安装在 webgrind 中生成图片的工具
RUN apt-get update  &&  apt-get install -y python graphviz

# Possible values for ext-name:
# bcmath bz2 calendar ctype curl dba dom enchant exif fileinfo filter ftp gd gettext gmp hash iconv imap interbase intl json ldap mbstring mcrypt mysqli oci8 odbc opcache pcntl pdo pdo_dblib pdo_firebird pdo_mysql pdo_oci pdo_odbc pdo_pgsql pdo_sqlite pgsql phar posix pspell readline recode reflection session shmop simplexml snmp soap sockets spl standard sysvmsg sysvsem sysvshm tidy tokenizer wddx xml xmlreader xmlrpc xmlwriter xsl zip
