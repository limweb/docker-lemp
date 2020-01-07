FROM adhocore/phpfpm:7.4

MAINTAINER Jitendra Adhikari <jiten.adhikary@gmail.com>

ENV ADMINER_VERSION=4.7.5

# nano
RUN apk add -U nano

# phpfpm
COPY php/php-fpm.ini /etc/supervisor.d/

# mysql
RUN apk add mysql mysql-client
COPY mysql/mysqld.ini /etc/supervisor.d/

# pgsql
RUN apk add postgresql
COPY pgsql/postgres.ini /etc/supervisor.d/

# redis
RUN apk add redis
COPY redis/redis-server.ini /etc/supervisor.d/

# beankstalkd
RUN apk add beanstalkd
COPY beanstalkd/beanstalkd.ini /etc/supervisor.d/

# nginx
RUN \
  addgroup -S nginx \
    && adduser -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx \
    && apk add nginx
COPY nginx/nginx.ini /etc/supervisor.d/
COPY nginx/nginx.conf /etc/nginx/nginx.conf
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY nginx/conf/nginx-site.conf /etc/nginx/sites-available/default.conf

# mailcatcher
COPY --from=tophfr/mailcatcher /usr/lib/libruby.so.2.5 /usr/lib/libruby.so.2.5
COPY --from=tophfr/mailcatcher /usr/lib/ruby/ /usr/lib/ruby/
COPY --from=tophfr/mailcatcher /usr/bin/ruby /usr/bin/mailcatcher /usr/bin/
COPY mail/mailcatcher.ini /etc/supervisor.d/

# supervisor
RUN apk add supervisor

# adminer
RUN \
  mkdir -p /var/www/adminer \
  && curl -sSLo /var/www/adminer/index.php "https://github.com/vrana/adminer/releases/download/v$ADMINER_VERSION/adminer-$ADMINER_VERSION-en.php"



# Add Scripts
ADD scripts/start.sh /start.sh
ADD scripts/pull /usr/bin/pull
ADD scripts/push /usr/bin/push
ADD scripts/letsencrypt-setup /usr/bin/letsencrypt-setup
ADD scripts/letsencrypt-renew /usr/bin/letsencrypt-renew
RUN chmod 755 /usr/bin/pull && chmod 755 /usr/bin/push && chmod 755 /usr/bin/letsencrypt-setup && chmod 755 /usr/bin/letsencrypt-renew && chmod 755 /start.sh

# resource
COPY php/index.php /var/www/html/index.php
COPY php/index1.php /var/www/html/index1.php
COPY php/ntunnel_mysql.php /var/www/html/ntunnel_mysql.php
COPY php/ntunnel_pgsql.php /var/www/html/ntunnel_pgsql.php
COPY php/ntunnel_sqlite.php /var/www/html/ntunnel_sqlite.php
COPY php/swoole.php /var/www/html/swoole.php
COPY php/app  /var/www/html/app
COPY php/vendor  /var/www/html/vendor
COPY php/composer.lock /var/www/html/composer.lock
COPY php/composer.json /var/www/html/composer.json


# entrypoint
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# cleanup
RUN \
  rm -rf /var/cache/apk/* /tmp/* /var/tmp/* /usr/share/doc/* /usr/share/man/*

EXPOSE 11300 9000 6379 5432 3306 88 80

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["supervisord", "-n", "-j", "/supervisord.pid"]
