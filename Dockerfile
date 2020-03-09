FROM adhocore/phpfpm:7.4

MAINTAINER Jitendra Adhikari <jiten.adhikary@gmail.com>

ENV \
  ADMINER_VERSION=4.7.6 \
  ES_HOME=/usr/share/java/elasticsearch \
  PATH=/usr/share/java/elasticsearch/bin:$PATH

RUN \
  # install
  apk add -U --no-cache \
    beanstalkd \
    elasticsearch \
    memcached \
    mysql mysql-client \
    nano \
    nginx \
    postgresql \
    redis \
    supervisor \
  # elastic setup
  && rm -rf $ES_HOME/plugins \
    && mkdir -p $ES_HOME/tmp $ES_HOME/data $ES_HOME/logs $ES_HOME/plugins $ES_HOME/config/scripts \
      && mv /etc/elasticsearch/* $ES_HOME/config/ \
    # elastico user
    && deluser elastico && addgroup -S elastico \
      && adduser -D -S -h /usr/share/java/elasticsearch -s /bin/ash -G elastico elastico \
      && chown elastico:elastico -R $ES_HOME \
  # adminer
  && mkdir -p /var/www/adminer \
    && curl -sSLo /var/www/adminer/index.php \
      "https://github.com/vrana/adminer/releases/download/v$ADMINER_VERSION/adminer-$ADMINER_VERSION-en.php" \
  # cleanup
  && rm -rf /var/cache/apk/* /tmp/* /var/tmp/* /usr/share/doc/* /usr/share/man/*

# nginx config
COPY nginx/nginx.conf /etc/nginx/nginx.conf
COPY nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY nginx/conf/nginx-site.conf /etc/nginx/sites-available/default.conf

# mailcatcher
COPY --from=tophfr/mailcatcher /usr/lib/libruby.so.2.5 /usr/lib/libruby.so.2.5
COPY --from=tophfr/mailcatcher /usr/lib/ruby/ /usr/lib/ruby/
COPY --from=tophfr/mailcatcher /usr/bin/ruby /usr/bin/mailcatcher /usr/bin/



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


# supervisor config
COPY \
  beanstalkd/beanstalkd.ini \
  elastic/elasticsearch.ini \
  mail/mailcatcher.ini \
  memcached/memcached.ini \
  mysql/mysqld.ini \
  nginx/nginx.ini \
  pgsql/postgres.ini \
  php/php-fpm.ini \
  redis/redis-server.ini \
    /etc/supervisor.d/

# entrypoint
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# ports
EXPOSE 11300 11211 9300 9200 9000 6379 5432 3306 88 80

# commands
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["supervisord", "-n", "-j", "/supervisord.pid"]
