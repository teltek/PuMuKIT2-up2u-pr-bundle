# UP2U-PR Project: Up2uWebTVBundle

Bundle based on [Symfony](http://symfony.com/) to work with the [PuMuKIT2 Video Platform](https://github.com/campusdomar/PuMuKIT2/blob/2.1.x/README.md).

This bundle overrides the [PuMuKIT-2 WebTV Bundle](https://github.com/campusdomar/PuMuKIT2/tree/master/src/Pumukit/WebTVBundle). 
It has been developed as the Web portal for the UP2U Personal Rercorder project.

Installation
------------

Step 1 require you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require teltek/pmk2-up2u-pr-bundle dev-master
```


### Step 2: Install the Bundle

Install the bundle by executing the following line command. This command updates the Kernel to enable the bundle (app/AppKernel.php) and loads the routing (app/config/routing.yml) to add the bundle routes\
.

```bash
$ php app/console pumukit:install:bundle Pumukit/Up2u/WebTVBundle/PumukitUp2uWebTVBundle
```

### Step 3: Update assets

```bash
$ php app/console cache:clear
$ php app/console cache:clear --env=prod
$ php app/console assets:install
```
