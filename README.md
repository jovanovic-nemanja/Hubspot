# Getting started 
 
## Installation
 
Please check the official laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/6.x/installation#installation)

Clone the repository

    git clone git@gitlab.com:helloteichner/sr-dev.git

Switch to the repo folder

    cd sr-dev

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000

**TL;DR command list**

    git clone git@gitlab.com:helloteichner/sr-dev.git
    cd sr-dev
    composer install
    cp .env.example .env
    touch database/laravel.sqlite (For local development only)
    npm install
    npm run dev

**Make sure you set the correct database connection information before running the migrations** [Environment variables](#environment-variables)

    touch database/laravel.sqlite (For local development only)
    php artisan migrate && php artisan db:seed
    php artisan serve

## Database seeding

**Populate the database with seed data with relationships which includes users, articles, comments, tags, favorites and follows. This can help you to quickly start testing the api or couple a frontend and start using it with ready content.**

**_Note_** : It's recommended to have a clean database before seeding. You can refresh your migrations at any point to clean the database by running the following command

    php artisan migrate:fresh && php artisan db:seed

## Modules Sync

**Sync json files modules to database.**

    php artisan sync:modules
    

## Update "All Modules" Group

    ls *.json | grep -v -E "theme|base|email|fields|all-modules"

    