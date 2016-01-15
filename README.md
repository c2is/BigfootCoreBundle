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

How to overload Symfony Router
---------------

Add these following lines to your front config:

```yml
bigfoot_core:
    routing:
        replace_symfony_router: true
        routers_by_id:
            router.default :     300
            bigfoot_core.router: 400
```

### BigfootFile annotation :
BigfootFile uses symfony's file upload system with its 2 properties for one file.

**@Bigfoot\Bundle\CoreBundle\Annotation\Bigfoot\File** : apply this annotation on the property that represents the form field. Use its *filePathProperty* option (required) to connect the other property.

Don't forget to create an "updated" field, and to update it before flushing with a PreFlush listener. Otherwise the file won't update if you don't change anything else in the form.

Exemple :

``` php
<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Bigfoot\Bundle\CoreBundle\Annotation\Bigfoot;

/**
 * @ORM\Table(name="items")
 * @ORM\HasLifecycleCallbacks()
 */
class Item
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path

    /**
     * @Assert\File(maxSize="6000000")
     * @Bigfoot\File(filePathProperty="path")
     */
    public $file;

    /**
     * @var string
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\PreFlush()
     */
    public function refreshUpdated() {
        if ($this->file) {
            $this->updated = new \DateTime("now");
        }
    }

}
```

### Use it in your Form :

``` php
class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'bigfoot_file', array(
            'required'         => false,
            'label'            => 'Your file',
            'filePathProperty' => 'path',
            'deleteRoute'      => 'entity_delete_file'
        ));
    }
}
```

If you don't define any deleteRoute, the deleteLink won't appear

### Get the file in front :

The second parameter(default false) defines whether or not the filter returns an absolute path

```php

{{ item|bigfoot_file('path', false) }}

```

### Generate a csv in Crud Index page :

Add the method getCsvFields into your extended CrudController controller:

``` php
protected function getCsvFields()
{
    return array(
        'name' => array(
            'label' => 'Name'
        ),
        'region.name' => array(
            'label'    => 'Region',
        ),
        'services.codeDetail' => array(
            'label'    => 'Services',
            'multiple' => true
        )
    );
}
```

If you want to display a field of your entity, use:

``` php
protected function getCsvFields()
{
    return array(
        'name' => array(
            'label' => 'Name'
        ),
        ....
```

If you want to display a field of an external entity, use:

``` php
protected function getCsvFields()
{
    return array(
        'region.name' => array(
            'label'    => 'Region'
        ),
        ....
    );
```

If you want to display a field of OneToMany relation, use:

``` php
protected function getCsvFields()
{
    return array(
        'services.codeDetail' => array(
            'label'    => 'Services',
            'multiple' => true
        )
        ....
    );
```
