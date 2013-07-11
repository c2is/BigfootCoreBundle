BigfootCoreBundle
=================

This is the core bundle for the Bigfoot administration interface.
Provides core features and helpers to integrate BackOffice features through bundles.

Installation
------------

Use composer :

    php composer.phar require bigfoot/core-bundle

Register the bundle in your app/AppKernel.php file :

    $bundles = array(
        ...
        new Bigfoot\Bundle\CoreBundle\BigfootCoreBundle(),
        ...
    );

Usage
-----

The administration interface is then available at /admin. For now, it does nothing. Add Bigfoot bundles or create your own to really get started !
