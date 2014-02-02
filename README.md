# DayzWhitey - Whitelist Administration Tool

![logo](http://i.imgur.com/zAvBpbN.png)

### About
DayZWhitey is a web administration tool to manage your DayZ whitelist.

It uses the database structure of [DayzWhitelisterPro](https://github.com/deadfred666/DayzWhitelisterPro), so it´s easy to replace the
web interface.

### Installation
* Download or clone this repository and place it in your web directory
* Download the [composer.phar](https://getcomposer.org/download)
* Run ```php composer.phar install```
* Make sure ```var/logs``` and ```var/cache``` are writable
* Copy the ```config.sample.ini``` to ```config.ini``` and edit the settings to your needs
* Run the ```res/dbscheme.sql``` to set up the database structure
* Optional: Create a VirtualHost entry and point it to the ```web/``` directory
* You´re done!

### Requirements
* Apache2/nginx
* MySQL 5
* PHP 5.3

