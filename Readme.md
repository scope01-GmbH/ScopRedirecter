# Shopware 5 redirecter plugin

| Version 	| Requirements               	                                                            | Availability   |
|---------	|-------------------------------------------------------------------------------------------|----------------|
| 1.0.9     | Fixed Shopware 5.5 compatibility                                                          | Github         |
| 1.0.9     | Fixed smarty security error in combination with plugin digital publishing in backend       | Github         |
| 1.0.8     | Fixed redirect loop on activated option "Disable appending slash at the end of the URL"   | Github         |
| 1.0.7     | Fixed errors in log file on doing redirects       	                                    | Github         |
| 1.0.6     | Auto-adding "/" at the URL can be configured      	                                    | Github         |
| 1.0.6     | Improved translations and usability in backend     	                                    | Github         |
| 1.0.6     | Improved error handling and validation   	                                                | Github         |
| 1.0.5     | Min. Shopware 5.5.0    	                                                                | Github         |
| 1.0.4     | Min. Shopware 5.5.10    	                                                                | Github         |



# Installation

## Zip Installation package for the Shopware Plugin Manager

* Download the [latest plugin version](https://github.com/scope01-GmbH/ScopRedirecter/releases/latest/) (e.g. `ScopRedirecter-1.0.9.zip`)
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