playlab
=======

This project is developing under Ubuntu 13.04

## Install

* sudo apt-get install php-pear php5-dev libevent-dev php5-json
* sudo pecl install event
* git clone https://github.com/kiang/playlab.git
* cd playlab
* rm -Rf vendor/rickysu
* composer.phar install
* php -q server.php
