Yii2 Dynamic Fields behavoir
============================
Behavior for Yii2 framework to work with custom (dynamic) fields.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist chemezov/yii2-dynamic-fields "*"
```

or add

```
"chemezov/yii2-dynamic-fields": "*"
```

to the require section of your `composer.json` file.

Apply migration

```
yii migrate --migrationPath=@chemezov/yii2-dynamic-fields/migrations
```


Usage
-----

For example you have User model. And you want to store **address** and some other values e.g. **is_client** but don't want to extend your DB table.
So you can use this behavior. Here example of User model:

## Basic usage

```php
use chemezov\yii2_dynamic_fields\DynamicFieldsBehavior;

class User extends \yii\db\ActiveRecord
{
    /* custom fields */
    public $address;
    public $is_client;
    
    public function rules()
    {
        return [
        ...
            [['address'], 'string', 'max' => 255],
            [['is_client'], 'default', 'value' => null],
            [['is_client'], 'boolean'],
        ];
    }
    
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_client' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
            ],
            'dynamicFields' => [
                'class' => DynamicFieldsBehavior::class,
                'fields' => ['address', 'is_client'], // All fields you want to save with your model
            ],
        ];
    }
}
```

## Common usage
```php
use chemezov\yii2_dynamic_fields\DynamicFieldsBehavior;

class User extends \yii\db\ActiveRecord
{
    /* custom fields */
    public $address;
    public $is_client;
    
    public function rules()
    {
        return [
        ...
            [$this->getAdditionalFieldsNamesString(), 'string', 'max' => 255],
            [$this->getAdditionalFieldsNamesBoolean(), 'default', 'value' => null],
            [$this->getAdditionalFieldsNamesBoolean(), 'boolean'],
        ];
    }
    
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_client' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
            ],
            'dynamicFields' => [
                'class' => DynamicFieldsBehavior::class,
                'fields' => $this->getAdditionalFieldsNames(),
            ],
        ];
    }
    
    public function getAdditionalFieldsNamesString()
    {
        return [
            'address',
        ];
    }

    public function getAdditionalFieldsNamesBoolean()
    {
        return [
            'is_client',
        ];
    }

    public function getAdditionalFieldsNames()
    {
        return array_merge($this->getAdditionalFieldsNamesString(), $this->getAdditionalFieldsNamesBoolean());
    }

    public function fields()
    {
        return array_merge(parent::fields(), $this->getAdditionalFieldsNames());
    }
}
```

Now is supported string and boolean variables. By using rules and AttributeTypecastBehavior you can configure any type you want.
