A generic Silex service provider for managed services
=====================================================

Managed services is a Silex service provider that allows to manage a given service for
multiple configurations.

If you find yourself writing new service providers just to wrap another service class with
multiple different configurations in a single application, then this might be for you.


## Features

Managed services works similar to the `DoctrineServiceProvider`. It allows to configure
multiple instances of a service with different options.

Instead of writing a new custom service provider for each service (even 3rd party) just
wrap them in an instance of `ManagedServiceProvider`.


## Installation

The recommended way to install managed-services is [through
composer](http://getcomposer.org). Just create a `composer.json` file and
run the `php composer.phar install` command to install it:

    {
        "require": {
            "radebatz/managed-service": "1.0.*@dev"
        }
    }

Alternatively, you can download the [`managed-service.zip`][2] file and extract it.



## Tests

Managed service comes with a (comprehensive) set of unit tests.

To run the test suite, you will need [PHPUnit](http://phpunit.de/manual/current/en/).



## License

Managed service is licensed under the MIT license.



[1]: https://github.com/DerManoMann/managed-service/archive/master.zip
