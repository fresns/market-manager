<p align="center"><a href="https://fresns.org" target="_blank"><img src="https://assets.fresns.com/images/logos/fresns.png" width="300"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/PHP-%5E8.0-blueviolet" alt="PHP">
<img src="https://img.shields.io/badge/Laravel-9.0%7C%5E10.0-orange" alt="Laravel">
<img src="https://img.shields.io/badge/License-Apache--2.0-green" alt="License">
</p>

## About Market Manager

`fresns/market-manager` is a Laravel market which created to manage your large Laravel market. Market is like a Laravel blade template, it has some views.

## Install

To install through Composer, by run the following command:

```bash
composer require fresns/market-manager
```

The market will automatically register a service provider and alias.

Optionally, publish the market's configuration file by running:

```bash
php artisan vendor:publish --provider="Fresns\MarketManager\Providers\MarketServiceProvider"
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/fresns/market-manager/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/fresns/market-manager/issues).
3. Contribute new features or update the wiki.

*The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable.*

## License

Fresns Market Manager is open-sourced software licensed under the [Apache-2.0 license](https://github.com/fresns/market-manager/blob/main/LICENSE).
