FROM dragoono/laravel-imagick:1.0

WORKDIR /app
COPY . /app
CMD php artisan serve --host=0.0.0.0 --port=8000

EXPOSE 8000
