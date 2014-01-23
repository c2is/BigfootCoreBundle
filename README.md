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


How to create a widget
---------------

Create a class extends `Bigfoot\Bundle\CoreBundle\Model\AbstractWidget`.
Define in your new class the method 'renderContent()'. This method must return html code of your widget.

Add a record in widget_backoffice table with corresponding values :
  name : Fullname of your class
  title : Title display in widget header, this field is translatable

Add 2 records in widget_backoffice_parameter table.
One with these values :
  name: order
  value: Order number you want for your widget
  widget_id: Record ID of your widget in widget_backoffice table
  user_id: (Optionnal) If defined, this parameter will be used only for this user

Another with these values :
  name: width
  value: number of columns use by your widget
  widget_id: Record ID of your widget in widget_backoffice table
  user_id: (Optionnal) If defined, this parameter will be used only for this user
