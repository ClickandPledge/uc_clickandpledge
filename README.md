README file for Ubercart Payment Module


CONTENTS OF THIS FILE
---------------------
* Introduction
* Requirements
* Installation
* Configuration
* How It Works
* Troubleshooting



INTRODUCTION
------------
This module integrates Click&Pledge Payment gateway for online payments into
the Drupal Ubercart  checkout systems.


* For a full description of the module, visit the project page:
  
* To submit bug reports and feature suggestions, or to track changes:
  https://forums.clickandpledge.com/forum/platform-product-forums/3rd-party-integrations/drupal-commerce


REQUIREMENTS
------------
This module requires the following:
* Drupal Installation
* Drupal Ubercart package
  - Ubercart core (https://www.drupal.org/project/ubercart)
* Recommended Modules:
  - Rules (https://www.drupal.org/project/rules)
  - Colorbox (https://www.drupal.org/project/colorbox)
  - Token (https://www.drupal.org/project/token)



INSTALLATION
------------
* Download the module from below link,
* Extract the zip and place the folder in the following location
	\\Project-Root-Folder\modules\ubercart\payment\uc_clickandpledge\
* Now, we need to activate the module from: dashboard > extends > UBERCART - PAYMENT > Click&Pledge


CONFIGURATION
-------------
* Once installation completed, we will redirected to authentication page, follow the necessary steps
* Create a new Click&Pledge payment gateway.
  Administration > Store > Configuration > Payment Methods > Choose > Add payment method
  - Click&Pledge payment gateway enabled 

*  Click & Pledge Payment settings are available:
   - Administration > Store > Configuration > Click & Pledge Payment settings


HOW IT WORKS
------------
* General considerations:  
  - Customers should have a valid credit card.

* Checkout workflow:
  It follows the Drupal Ubercart Credit Card workflow.
  The customer should enter his/her credit card data

TROUBLESHOOTING
---------------
* No troubleshooting pending for now.

