# N1CTF_backend
backend of n1ctf platform
API可见api目录,可根据需求定制前端
```
composer install --ignore-platform-reqs
composer update
php artisan jwt:secret
php artisan key:generate
php artisan migrate
php artisan queue:work --queue=default,update
```
