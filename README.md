# Diglin\Username module for Magento 2 - BETA #

Magento module which allows your customers to use a username and not only the email address as identifier

**IMPORTANT**
It's a BETA. Lots of things have not been yet implemented or is a work in progress. See the issues https://github.com/diglin/Diglin_Username2/issues/created_by/diglin. For example checkout account creation with username doesn't work.

## Features

- Compatible and tested with Magento version 2.0 & 2.1 
- Login with a username or email, it can be done from frontend during checkout or to get access to the customer account
- Save a username from frontend (register account or checkout process) or from backend by editing a customer account
- Prevent duplicate username
- The default templates override some customer and checkout views to adapt display for login pages, checkout process and account edition in frontend. If you have a customized template, update your template with the content of the one of this module.
- Configurable options to define what kind of username to support: only letters, only digits, both or default (digits, letters and special characters '-_') or even custom regex
- Configurable options to set the maximum and minimum string length
- Display Username of each customer in the Customer Management Grid
- Allow or not the customer to edit the username in My Account in frontend
- Support username when a customer wants to retrieve his forgotten password thanks to the "Forgotten Password" form
- Generate usernames for customer account who don't have one, can be triggered from configuration page. Generated usernames will use current saved configuration (letters, digits, both or custom regex).

## Installation

```
cd path/to/my/magento/project
composer.phar require 'diglin/module-username'
bin/magento module:enable Diglin_Username
bin/magento setup:upgrade
bin/magento setup:di:compile
```

## Uninstall

```
cd path/to/my/magento/project
bin/magento module:uninstall -r Diglin_Username
```

## Author

* Sylvain Rayé
* http://www.diglin.com/
* [@diglin_](https://twitter.com/diglin_)
* [Follow me on github!](https://github.com/diglin)
