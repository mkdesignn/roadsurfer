## roadsurfer Project

To install the dependencies you'll need to run
```
composer install
npm run install | yarn install
```
Note: If an error occure while installing, You can use  --force with npm install

Note: I have used sqlite database, But you can change the driver to any other driver (pgsql, sqlserver, mysql).

Note: You should have sqlite3 driver on your machine if you are willing to use sqlite. 

Before running the migrations you'll need to copy .env.example to .env, Feel free to change the .env file config.


To run the migration, You can use
```
php artisan migrate
```

To generate fakeData you can use,
```
php artisan db:seed
```

To run the server you can run it by writing the following command
```
php artisan serve
```

This way you'll have the server running. The server is running on port 8000 which it's the default port of laravel.

Feel free to change the ports as the way you like.

You can get the timeline for current month by calling the following api.
```
POST /api/timeline HTTP/1.1
Host: {{host}}
Content-Type: application/json
```

You can also run the tests by using
```
vendor/bin/phpunit
```

To see the results in action, you can visit https://roadsurfer.mohammadkaab.com/

You can use the following username and password to login
U: roadsurfer@gmail.com
P: secret 
