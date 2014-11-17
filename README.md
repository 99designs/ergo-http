Ergo
==========

A light-weight HTTP library for PHP5.3. Provides a client and some URL utils.

Extracted from [99designs/ergo][1].

Install
-------

`composer require 99designs/ergo-http`.


Basic Usage
-----------

```php

$client = new \Ergo\Http\Client(new \Ergo\Http\Url($host));
$response = $client->get($path);

echo $response->getBody();

```

How to develop
-----------------

To install dependancies via [Composer][2]:

`$ composer install --dev`

Run the test suite:

```
$ phpunit
PHPUnit 3.7.38 by Sebastian Bergmann.

Configuration read from /home/vagrant/ergo-http/phpunit.xml.dist

...................................................

Time: 149 ms, Memory: 5.00Mb

OK (51 tests, 163 assertions)

```


Status
-------

Used in several high-volume production websites, including 99designs.com, flippa.com, learnable.com and sitepoint.com.

[1]: https://github.com/99designs/ergo
[2]: https://github.com/composer/composer
