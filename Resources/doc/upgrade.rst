# Upgrade

## Fix #68-69

You need to edit the function ""getFIelds" (used to sort lists) in all controller extending the CrudController.

Before:


```
protected function getFields()
{
    return array(
        'id'   => 'ID',
        'name' => 'Name',
        'menu' => 'Menu',
    );
}
```

After:

```
protected function getFields()
{
    return array(
        'id'   => array(
            'label' => 'ID',
        ),
        'name' => array(
            'label' => 'Name',
        ),
        'menu' => array(
            'label' => 'Menu',
            'sort'  => 'm.name',
            'join'  => 'm'
        )
    );
}
```

In this example we are sorting items of a menu, the column "menu" is a relationship with "item".
If you want to sort using "menu" you need to add the key "sort" inside the array of the getFields().

In the function "doIndex" of the CrudController we added this join so you can sort using the menu column.

```
foreach ($this->getFields() as $key => $field) {
    if (isset($field['join'])) {
        $queryBuilder
            ->leftJoin('e.'.$key, $field['join']);
    }
}
```

Where $key (here menu) is the attribute name of the "item" entity and $field['join'] is the alias of the join.