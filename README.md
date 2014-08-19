# Fuel DBAL

[![Build Status](https://travis-ci.org/indigophp/fuel-dbal.svg?branch=develop)](https://travis-ci.org/indigophp/fuel-dbal)
[![Latest Stable Version](https://poser.pugx.org/indigophp/fuel-dbal/v/stable.png)](https://packagist.org/packages/indigophp/fuel-dbal)
[![Total Downloads](https://poser.pugx.org/indigophp/fuel-dbal/downloads.png)](https://packagist.org/packages/indigophp/fuel-dbal)
[![License](https://poser.pugx.org/indigophp/fuel-dbal/license.png)](https://packagist.org/packages/indigophp/fuel-dbal)
[![Dependency Status](http://www.versioneye.com/user/projects/53f01c7313bb06c58c000412/badge.svg?style=flat)](http://www.versioneye.com/user/projects/53f01c7313bb06c58c000412)

**This package is a wrapper around [doctrine/dbal](https://github.com/doctrine/dbal) package.**


## Install

Via Composer

``` json
{
    "require": {
        "indigophp/fuel-dbal": "@stable"
    }
}
```


## Usage

You can use the fuel `db` configuration or you can place your configuration in `dbal`. `dbal` is checked first and it must be in DBAL compatible format, only the legacy `db` configurations are converted.

``` php
// Returns a DBAL Connection object
$conn \Dbal::forge('default');
```

`Dbal` class extends `Facade` and uses `Facade\Instance` from [indigophp/fuel-core](https://github.com/indigophp/fuel-core).


### Configuration

``` php
	'dbname'       => 'database'
	'host'         => 'localhost'
	'port'         => 1234
	'user'         => 'user',
	'password'     => 'secret',
	'driver'       => 'pdo_mysql',
	'driver_class' => 'MyNamespace\\MyDriverImpl', // the DBAL driverClass option
	'options'      => array( // the DBAL driverOptions option
	    'foo' => 'bar',
	),
	'path'             => '',
	'wrapper_class'    => 'MyDoctrineDbalConnectionWrapper', // the DBAL wrapperClass option
	'charset'          => 'UTF8',
	'profiling'        => true,
	'mapping_types'    => array(
	    'enum' => 'string',
	),
	'types' => array(
	    'custom' => 'MyCustomType',
	),
```


### Profiling

No configuration is required beyond enabling profiling for your connection. Queries sent through DBAL will automatically appear in the Fuel profiler.


**Note:** You can use the package even without loading it since composer handles autoloading.


## Testing

``` bash
$ codecept run
```


## Contributing

Please see [CONTRIBUTING](https://github.com/indigophp/fuel-dbal/blob/develop/CONTRIBUTING.md) for details.


## Credits

- [Márk Sági-Kazár](https://github.com/sagikazarmark)
- [aspendigital](https://github.com/aspendigital/fuel-doctrine2)
- [All Contributors](https://github.com/indigophp/fuel-dbal/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/indigophp/fuel-dbal/blob/develop/LICENSE) for more information.
