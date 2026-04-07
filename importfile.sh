docker compose exec php  php artisan db:seed --class=UsersDummyTableSeeder
docker compose exec php  php artisan db:seed --class=ClientsDummyTableSeeder
docker compose exec php  php artisan db:seed --class=TasksDummyTableSeeder
docker compose exec php  php artisan db:seed --class=LeadsDummyTableSeeder