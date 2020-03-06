# Laravel Make User
## v7.0.0

[![Build Status](https://travis-ci.org/michaeldyrynda/laravel-make-user.svg?branch=master)](https://travis-ci.org/michaeldyrynda/laravel-make-user)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/michaeldyrynda/laravel-make-user/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/michaeldyrynda/laravel-make-user/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/michaeldyrynda/laravel-make-user/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/michaeldyrynda/laravel-make-user/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dyrynda/laravel-make-user/v/stable)](https://packagist.org/packages/dyrynda/laravel-make-user)
[![Total Downloads](https://poser.pugx.org/dyrynda/laravel-make-user/downloads)](https://packagist.org/packages/dyrynda/laravel-make-user)
[![License](https://poser.pugx.org/dyrynda/laravel-make-user/license)](https://packagist.org/packages/dyrynda/laravel-make-user)
[![Dependency Status](https://www.versioneye.com/php/dyrynda:laravel-make-user/dev-master/badge?style=flat-square)](https://www.versioneye.com/php/dyrynda:laravel-make-user/dev-master)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen?style=for-the-badge)](https://offset.earth/treeware?gift-trees)

## Introduction

Out of the box, Laravel makes it really simple to scaffold out with its [authentication quickstart](https://laravel.com/docs/5.8/authentication#authentication-quickstart). Whilst this makes it really easy to register and authenticate users, for many of the applications I find myself building, we usually remove the ability for visitors to register themselves.

I still need a way to get users into those applications, however, and whilst they're in early development this usually involves firing up Laravel Tinker. This can be a tedious process, and one that I repeat many times over.

This package aims to solve the repetition problem by providing a convenient `make:user` Artisan command.

### Version compatibility

Laravel | Package
:-------|:--------
5.4.*   | 1.0.*
5.5.*   | 2.0.*
5.6.*   | 3.0.*
5.7.*   | 4.0.*
5.8.*   | 5.0.*
6.x     | 6.0.*
7.x     | 7.0.*


## Code Samples

This package exposes a `make:user` command, which is accessed via the Artisan command line utility. The package will use the model defined in your `auth.providers.users.model` configuration value.

```
php artisan make:user
```

This package runs on the assumption that you are using Laravel's default `users` table structure. You can specify additional fields when prompted.

## Installation

This package is installed via [Composer](https://getcomposer.org/). To install, run the following command.

```bash
composer require dyrynda/laravel-make-user
```

## Support

If you are having general issues with this package, feel free to contact me on [Twitter](https://twitter.com/michaeldyrynda).

If you believe you have found an issue, please report it using the [GitHub issue tracker](https://github.com/michaeldyrynda/laravel-make-user/issues), or better yet, fork the repository and submit a pull request.

If you're using this package, I'd love to hear your thoughts. Thanks!

## Treeware

You're free to use this package, but if it makes it to your production environment you are required to buy the world a tree.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to plant trees. If you support this package and contribute to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees [here](https://offset.earth/treeware)

Read more about Treeware at [treeware.earth](https://treeware.earth)
