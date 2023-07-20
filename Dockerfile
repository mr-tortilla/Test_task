FROM php:8.2

WORKDIR /app
COPY . /app
CMD php artisan serve --host=0.0.0.0 --port 4030
EXPOSE 4030
