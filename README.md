# Diglin\Username module for Magento 2 #

Magento module which allows your customers to use a username and not only the email address as identifier

**IMPORTANT**
It's a BETA. Lots of things have not been yet implemented or is a work in progress. For example checkout login doesn't work.

## Features

TODO

- Compatible and tested with Magento version >=1.4.2 until 1.9.x (should work also on 1.3)
- Login with a username and/or email, it can be done from frontend during checkout or getting access to the customer account
- Save a username from frontend (register account or checkout process) or from backend by editing a customer account
- Check that the username doesn't already exists
- The default templates override some customer and checkout views to adapt display for login pages, checkout process and account edition in frontend. If you have a customized template, please check the layout file username.xml and compare with your template to use or adapt to your situation.
- Configurable options to define what kind of username to support: only letters, only digits, both or default (digits, letters and special characters '-_') or even custom regex
- Configurable options to set the maximum and minimum string length
- Display Username of each customer in the Customer Management Grid
- Allow or not the customer to edit the username in My Account in frontend
- Support username when a customer wants to retrieve his forgotten password thanks to the "Forgotten Password" form

## Installation

TODO 

## Uninstall

```
cd path/to/my/magento/project
bin/magento module:uninstall Diglin_Username
```

TEMPORARY SOLUTION from your database UI:
````
DELETE FROM eav_attribute WHERE attribute_code LIKE '%username%';
ALTER TABLE sales_flat_quote DROP COLUMN 'customer_username'; 
ALTER TABLE sales_flat_order DROP COLUMN 'customer_username';
ALTER TABLE customer_grid_flat DROP COLUMN 'username';
```

## Author

* Sylvain Rayé
* http://www.diglin.com/
* [@diglin_](https://twitter.com/diglin_)
* [Follow me on github!](https://github.com/diglin)
