# Solr Management Console
[![Build Status](https://img.shields.io/travis/fabiorphp/solr-console/master.svg?style=flat-square)](https://travis-ci.org/fabiorphp/solr-console)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fabiorphp/solr-console/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/fabiorphp/solr-console/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/fabiorphp/solr-console/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/fabiorphp/solr-console/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/solr/console.svg?style=flat-square)](https://packagist.org/packages/solr/console)
[![Total Downloads](https://img.shields.io/packagist/dt/solr/console.svg?style=flat-square)](https://packagist.org/packages/solr/console)
[![License](https://img.shields.io/packagist/l/solr/console.svg?style=flat-square)](https://packagist.org/packages/solr/console)

An application that provides a management console for [SolrCloud](http://lucene.apache.org/solr/)

## Dependencies
To use this package, is necessary install the [Zookeeper Pecl Package](https://github.com/andreiz/php-zookeeper).

## Instalation
The package is available on [Packagist](http://packagist.org/packages/dafiti/logger-service-provider).
Autoloading is [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) compatible.
```json
{
    "require": {
        "solr/console": "dev-master"
    }
}
```

## Usage

#### List commands
```sh
# Symfony/Console
$ vendor/bin/solr 
```
For more details about console commands, please run the script above

#### How to integrate the solr console commands with your application?
```php
#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Console\Application;

$httpClient = new HttpClient(['base_url' => 'http://localhost:8983/solr']); // Your Solr host.
$zkClient = new \Zookeeper('localhost:2181'). // Your Zookeeper host.

$application = new Application();
$application->add(new Collection\All($httpClient);
$application->add(new Collection\Reload($httpClient);
$application->add(new Collection\Remove($httpClient);
$application->add(new Collection\Create($httpClient);
$application->add(new Schema\All($zkClient);
$application->add(new Schema\LinkConfig($zkClient);
$application->add(new Schema\Download($zkClient);
$application->add(new Schema\Upload($zkClient);
$application->add(new Schema\Remove($zkClient);
$application->run();
```
Open the command class and see the constructor params.
