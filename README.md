# CryptoCompra by CryptoMarket
[![N|Solid](https://www.cryptocompra.com/img/logo.png)](https://nodesource.com/products/nsolid)

Cryptocompra is the new way for pay with the most used cryptocurrencies in the world, this Prestashop plugin includes.
  - Support for Bitcoin, Stellar and Ethereum Cryptocurrencies.
  - New API v1.1 updates.
  - Payments with ARS(Argentinian Peso), BRL(Real brazilian), CLP(Chilean peso) and EUR(Euro).
  - Configuration panel for access credentials.

## Development
### Setup
 * NodeJS & NPM
 * Grunt
 * Composer

Clone the repo:
```bash
$ git clone https://github.com/cryptomkt/prestashop-plugin
$ cd prestashop-plugin
```
Install the dependencies:
```bash
$ npm install
$ curl -sS https://getcomposer.org/installer | php
$ ./composer.phar install
```
Get the PSR-2 Coding Standard Tool Fixer:
```bash
$ wget https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -O php-cs-fixer
$ sudo chmod a+x php-cs-fixer
```
### Build
```bash
$ ./node_modules/.bin/grunt build
# Outputs plugin at dist/cryptomarket
# Outputs plugin archive at dist/cryptomarket.zip
```
## Support

### CryptoCompra by CryptoMarket support

* Last Version Tested: Prestashop 1.6.+ | 1.7.+
* [GitHub Issues](https://github.com/cryptomkt/prestashop-plugin/issues)
  * Open an issue if you are having issues with this plugin.
* [Support](https://soporte.cryptomkt.com/)
  * Cryptomarket support team

### Prestashop support

The official PrestaShop 1.7 documentation is available online [on its own website][1]
First-time users will be particularly interested in the following guides:
* [Getting Started][2]: How to install PrestaShop, and what you need to know.
* [User Guide][3]: All there is to know to put PrestaShop to good use.
* [Updating Guide][4]: Switching to the newest version is not trivial. Make sure you do it right.
* [Merchant's Guide][5]: Tips and tricks for first-time online sellers.
* The [FAQ][6] and the [Troubleshooting][7] pages should also be of tremendous help to you.

## Troubleshooting

1. Ensure a valid SSL certificate is installed on your server. Also ensure your root CA cert is updated. If your CA cert is not current, you will see curl SSL verification errors.
2. Verify that your web server is not blocking POSTs from servers it may not recognize. Double check this on your firewall as well, if one is being used.
3. Check the version of this plugin against the official plugin repository to ensure you are using the latest version. Your issue might have been addressed in a newer version! See the [Releases](https://github.com/cryptomkt/prestashop-plugin/releases) page for the latest.
4. If all else fails, enable debug logging in the plugin options and send the log along with an email describing your issue **in detail** to support@cryptomarket.com

**TIP**: When contacting support it will help us is you provide:

* WordPress and WooCommerce Version
* Other plugins you have installed
  * Some plugins do not play nice
* Configuration settings for the plugin (Most merchants take screen grabs)
* Any log files that will help
  * Web server error logs
* Screen grabs of error message if applicable.

## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/cryptomkt/prestashop-plugin/issues) and tell us about it.

If you *are* a developer wanting contribute an enhancement, bugfix or other patch to this project, please fork this repository and submit a pull request detailing your changes.  We review all PRs!

This open source project is released under the [MIT license](http://opensource.org/licenses/MIT) which means if you would like to use this project's code in your own project you are free to do so.  Speaking of, if you have used our code in a cool new project we would like to hear about it!  Please send us an email or post a new thread on [CryptoMarket Developers](https://developers.cryptomkt.com).

## License

Please refer to the [LICENSE](https://github.com/cryptomkt/prestashop-plugin/blob/master/LICENSE) file that came with this project.

[1]: http://doc.prestashop.com
[2]: http://doc.prestashop.com/display/PS17/Getting+Started
[3]: http://doc.prestashop.com/display/PS17/User+Guide
[4]: http://doc.prestashop.com/display/PS17/Updating+PrestaShop
[5]: http://doc.prestashop.com/display/PS16/Merchant%27s+Guide
[6]: http://build.prestashop.com/news/prestashop-1-7-faq/
[7]: http://doc.prestashop.com/display/PS16/Troubleshooting