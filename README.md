[![License](https://img.shields.io/badge/License-MIT-blue.svg)](http://opensource.org/licenses/MIT)

# Kantine

Web interface displaying Kantine menus.

Written in PHP/Laravel and VueJS.

### Screenshots:

#### Menu view:
![Menu view](https://user-images.githubusercontent.com/666182/258982451-8f32fcd3-319c-4141-970e-cbe1cc04bcca.png)

## Deployment

#### Clone repository : 
```sh
git clone https://github.com/carsso/kantine.git
```

#### Switch to deploy branch :
```sh
git fetch origin deploy
```

#### Copy default env file :
```sh
cp .env.example .env
```

#### Fill the env file :
```sh
vim .env
```

#### Install dependencies based on lock file
```sh
composer install --no-interaction --prefer-dist --optimize-autoloader
```

#### Clear cache
```sh
php artisan optimize
```

#### Create the storage symbolic links
```sh
php artisan storage:link
```

#### Run queue worker
```sh
php artisan queue:listen
```

## Development

#### Pre-requisites
- PHP >= 8.1
- NodeJS >= 18

#### Clone repository (main branch) : 
```sh
git clone git@github.com:carsso/kantine.git
```

Install PHP dependencies with Composer :
```sh
composer install
```

Install JS dependencies with NPM :
```sh
npm install
```

#### Copy default env file :
```sh
cp .env.dev.example .env
```

#### Fill the env file :
```sh
vim .env
```

#### Create the storage symbolic links
```sh
php artisan storage:link
```

#### Build js and css files automatically while developing :
```sh
npm run dev
```

#### Run queue worker
```sh
php artisan queue:listen
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).