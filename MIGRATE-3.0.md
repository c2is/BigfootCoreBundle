Migrate from 2.2 to 3.0
=======================

Change the version of all Bigfoot Bundles in your composer.json to '~3.0'

Update your vendors:

```
composer update
```

Update your database:

```
doctrine:schema:update --force
```

or

```
doctrine:migrations:diff
doctrine:migrations:generate
```

Add these following lines to your Bigfoot config

```yml
parameters:
    ....

    bigfoot_migrate:
        - 'Bigfoot\Bundle\NavigationBundle\Entity\Menu\Item'
        - 'Bigfoot\Bundle\MediaBundle\Entity\Metadata'
        - 'Bigfoot\Bundle\MediaBundle\Entity\MediaUsage'
        - 'Bigfoot\Bundle\MediaBundle\Entity\Media'
        - 'Bigfoot\Bundle\UserBundle\Entity\Role'
        - 'Bigfoot\Bundle\ContentBundle\Entity\Page'
        - 'Bigfoot\Bundle\ContentBundle\Entity\Attribute'
        - 'Bigfoot\Bundle\ContentBundle\Entity\Block'
        - 'Bigfoot\Bundle\ContentBundle\Entity\Sidebar'
        - 'Bigfoot\Bundle\CoreBundle\Entity\Tag'
        - 'Bigfoot\Bundle\CoreBundle\Entity\TagCategory'
        - 'Bigfoot\Bundle\CoreBundle\Entity\Widget'
        - 'Bigfoot\Bundle\SeoBundle\Entity\Metadata'
        - 'Bigfoot\Bundle\NavigationBundle\Entity\Menu'
        - 'Bigfoot\Bundle\NavigationBundle\Entity\Menu\Item\Attribute'
```

Migrate the translations from the table ext_translations to translations entities (with option delete=true if you want to delete useless ext_translations lines):

```
app/console bigfoot:migrate:translation --delete=true
```

That's it