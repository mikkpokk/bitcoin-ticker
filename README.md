# Bitcoin Ticker

#### Installing (via Terminal)

    git clone https://github.com/mikkpokk/bitcoin-ticker.git
    cd bitcoin-ticker
    composer install --prefer-source --no-interaction
    sudo chown -R www-data:www-data /var/www
    sudo chmod -R o+w bootstrap/cache/
    sudo chmod -R o+w storage/
    cp .env.example .env

#### Configuration

Open ```/.env``` file and overwrite following parameters:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=homestead
    DB_USERNAME=homestead
    DB_PASSWORD=secret

Re-open terminal and perform database migration

    php artisan migrate

Add following line to your crontab file (use your own absolute paths):

    * * * * * /bin/bash /Users/mikkpokk/Documents/Sites/bitcoin-ticker/updater.sh >> /dev/null 2>&1

Also make sure that ```updater.sh``` file uses correct paths.


# Bitcoin Ticker uses Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](http://lumen.laravel.com/docs).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
