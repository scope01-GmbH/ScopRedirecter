# Shopware 5 redirecter plugin

| Version 	| Requirements               	                          | Availability   |
|---------	|-------------------------------------------------------- |----------------|
| 1.0.6     | Auto-adding "/" at the url end can be configured   	  | Github         |
| 1.0.5     | Min. Shopware 5.5.0    	                              | Github         |
| 1.0.4     | Min. Shopware 5.5.10    	                              | Github         |

# Installation

## Zip Installation package for the Shopware Plugin Manager

* Download the [latest plugin version](https://github.com/scope01-GmbH/ScopRedirecter/releases/latest/) (e.g. `ScopRedirecter-1.0.6.zip`)
* Upload and install plugin using Plugin Manager

## Git Version
* Checkout Plugin in `/custom/plugins/ScopRedirecter`
* Change to Directory and run `composer install` to install the dependencies
* Install the Plugin with the Plugin Manager

## Install with composer
* Change to your root Installation of shopware
* Run command `composer require scop/scop-redirecter` and install and active plugin with Plugin Manager

## Plugin Features:
* Adding the slash at the end of the redirecting url if the configuration of "Dont Add Slash" is set to false
* Doesn't change the url if it is an absolute URL that starts with ("http:" or "www.")
