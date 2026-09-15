FROM php:8.2-cli

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/products /var/www/html/uploads/profile \
    && chmod -R 775 /var/www/html/uploads

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
