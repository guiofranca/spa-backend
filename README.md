<p align="center">

<img src="https://github.com/guiofranca/spa-frontend/raw/master/static/icon.png" alt="Continhas Logo" />

</p>

# Continhas

Continhas is a very special Bill Splitting solution!

Share your home, event or bills of any other kind of activity and then settle the differences once and for all with everybody!

## Features
* A user can create Groups
* The Group owner can invite another users to the Group
* The Group owner can set the split proportion to each member
* Every member can register the expenses as Bills
* Only the Bill owner can edit or delete a Bill
* The Group owner can Settle the Bills
* All Bills from Bills page will be marked as settled and will be removed from the page
* Only Group owner can finish, edit or delete the Settle

## Purpose
This project is made to practice my developer skills using [PHP](https://github.com/php)/[Laravel](https://github.com/laravel) for the API and [Vue](https://github.com/vuejs)/[Nuxt](https://github.com/nuxt) for the Web Pages and Single Page Application feel.

Also to make my home's bill splitting easier. It was formerly done by registering the expenses on a Google form, and the splitting calculated on a Spreadsheet.

This is my very first project that has Tests! Hooray!

## Related project
The Frontend project is available on [Continhas](https://github.com/guiofranca/spa-frontend).

## Build Setup
Copy the `.env.example` to `.env` and fill it to map your environment. Then:

```bash
# install dependencies
$ composer install

# generate a new application key
$ php artisan key:generate
$ php artisan jwt:secret

# run migrations
$ php artisan migrate

# optimize for production (never for development)
$ php artisan optimize
```

