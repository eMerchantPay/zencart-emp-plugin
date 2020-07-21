emerchantpay Gateway Module for Zen Cart
======================================

This is a Payment Module for Zen Cart, that gives you the ability to process payments through emerchantpay's Payment Gateway - Genesis.

Requirements
------------

* Zen Cart 1.5.x
* [GenesisPHP v1.18.4](https://github.com/GenesisGateway/genesis_php/releases/tag/1.18.4) - (Integrated in Module)
* PCI-certified server in order to use ```emerchantpay Direct```

GenesisPHP Requirements
------------

* PHP version 5.3.2 or newer
* PHP Extensions:
    * [BCMath](https://php.net/bcmath)
    * [CURL](https://php.net/curl) (required, only if you use the curl network interface)
    * [Filter](https://php.net/filter)
    * [Hash](https://php.net/hash)
    * [XMLReader](https://php.net/xmlreader)
    * [XMLWriter](https://php.net/xmlwriter)

Installation (Manual)
------------

* Upload the contents of folder (excluding ```README.md``` and ```YOUR_ADMIN```) to the ```<root>``` folder of your Zen Cart installation
* Upload the contents of folder ```YOUR_ADMIN``` to your ```<admin>``` folder of your Zen Cart installation
* Log into ```Zen Cart Administration Area``` with your Administrator account
* Go to ```Modules``` -> ```Payment``` -> Locate ```emerchantpay Checkout``` or ```emerchantpay Direct``` Module and click ```Install```
* Click ```Edit```, enter your credentials and configure the plugin to your needs

_Note_: If you have trouble with your credentials or terminal configuration, get in touch with our [support] team

You're now ready to process payments through our gateway.

[support]: mailto:tech-support@emerchantpay.net
