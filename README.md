[![GitHub License](https://img.shields.io/github/license/carsso/kantine)](LICENSE)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/carsso/kantine/build.yml)
![GitHub branch check runs](https://img.shields.io/github/check-runs/carsso/kantine/main)
![GitHub last commit](https://img.shields.io/github/last-commit/carsso/kantine)

# Kantine

Web interface displaying Kantine menus.

Written in PHP/Laravel and VueJS.

### Screenshots:

#### Live preview

[Kantine.menu](https://kantine.menu)

#### Menu view

![Menu view](https://user-images.githubusercontent.com/666182/258982451-8f32fcd3-319c-4141-970e-cbe1cc04bcca.png)

## Pre-requisites

- PHP >= 8.3
- NodeJS >= 23
- FontAwesome Pro 6 license, with a [configured .npmrc file](https://docs.fontawesome.com/web/setup/packages#project-specific-using-configuration-files)

## Deployment

#### Clone repository 

```sh
git clone https://github.com/carsso/kantine.git
```

#### Copy default env file

```sh
cp .env.example .env
```

#### Fill the env file

```sh
vim .env
```

#### Install JS dependencies based on lock file

```sh
npm install
```

#### Build assets

```sh
npm run build
```

#### Install PHP dependencies based on lock file

```sh
composer install --no-interaction --prefer-dist --optimize-autoloader
```

#### Init Python virtual env

```sh
python3 -m venv scripts/
scripts/bin/pip3 install -r scripts/requirements.txt
scripts/bin/python3
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

#### Run reverb server

```sh
php artisan reverb:start
```

## Development

#### Clone repository (main branch)

```sh
git clone git@github.com:carsso/kantine.git
```

Install PHP dependencies with Composer

```sh
composer install
```

Install JS dependencies with NPM

```sh
npm install
```

#### Copy default env file

```sh
cp .env.dev.example .env
```

#### Fill the env file

```sh
vim .env
```

#### Create the storage symbolic links

```sh
php artisan storage:link
```

#### Init Python virtual env

```sh
python3 -m venv scripts/
scripts/bin/pip3 install -r scripts/requirements.txt
scripts/bin/python3
```

#### Build js and css files automatically while developing

```sh
npm run dev
```

#### Run queue worker

```sh
php artisan queue:listen
```

#### Run reverb server

```sh
php artisan reverb:start
```

## License

- This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
- The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
