PostNL Magento 1 Extension

[![Build Status](https://travis-ci.org/tig-nl/postnl-magento1.svg?branch=master)](https://travis-ci.org/tig-nl/postnl-magento1)

### Installation

The link below redirects to the installation manual for the PostNL Magento extension. Within this manual we describe all the steps that are needed to install the extension in your environment.

https://confluence.tig.nl/display/TIGSD/English+PostNL+extension+installation+guide

### User manual

After installing you can configure the PostNL Magento extension to suit your needs. The user manual describes what options can be configured. 
The user manual can be found trough the following link: https://servicedesk.tig.nl/hc/nl/articles/206496008

### Additional information

Release notes:

https://servicedesk.tig.nl/hc/nl/articles/206495908

Knowledge base & FAQ:

https://servicedesk.tig.nl/hc/nl/categories/200427077

### Installation trough Modman (for advanced users)

Make sure that you have enabled symlinks in your Magento installation.
To enable this go to "System > Configuration > Advanced > Developer" and activate "Allow Symlinks".

Login trough SSH and go to the root of the Magento installation. Execute the following command:

````
cd .modman
git clone git@github.com:tig-nl/postnl-magento1.git
modman deploy postnl-magento1
````
