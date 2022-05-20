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

Apply migrations:

```
yii migrate --migrationPath=@vendor/chemezov/yii2-dynamic-fields/migrations
```


Usage
-----

```php
use chemezov\yii2_dynamic_fields\DynamicFieldsBehavior;

class User extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'dynamicFields' => [
                'class' => DynamicFieldsBehavior::class,
                'fields' => ['my_custom_string_1', 'my_custom_string_2'],
            ],
        ];
    }
        
    public function rules() {
        return [
            [['my_custom_string_1', 'my_custom_string_2'], 'string', 'max' => 255],
        ];
    }
        
    public function attributeLabels()
    {
        return [
            'my_custom_string_1' => 'My string 1',
            'my_custom_string_2' => 'My string 2',
        ];
    }
}
```

Now you can use your custom attributes:

```php
$model = new User();
$value = $model->my_custom_string_1; // get value
$model->my_custom_string_1 = 'some value'; // set value
$model->save();
```

Other types
-----------

If you want to use other types than string you can use ```AttributeTypecastBehavior```. Add ```AttributeTypecastBehavior``` after ```DynamicFieldsBehavior```. **It is important!**

```php
use chemezov\yii2_dynamic_fields\DynamicFieldsBehavior;
use yii\behaviors\AttributeTypecastBehavior;

class User extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'dynamicFields' => [
                'class' => DynamicFieldsBehavior::class,
                'fields' => ['my_boolean_attribute'],
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'my_boolean_attribute' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }
        
    public function rules() {
        return [
            [['my_boolean_attribute'], 'default', 'value' => null],
            [['my_boolean_attribute'], 'boolean'],
        ];
    }
        
    public function attributeLabels()
    {
        return [
            'my_boolean_attribute' => 'My boolean attribute',
        ];
    }
}
```

Example
-------

For example you have User model. And you want to store **address** and some other values e.g. **is_client** but don't want to extend your DB table.
So you can use this behavior. Here example of User model:

```php
use chemezov\yii2_dynamic_fields\DynamicFieldsBehavior;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class User
 *
 * @property string $address
 * @property boolean $is_client
 */
class User extends \yii\db\ActiveRecord
{
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
            'dynamicFields' => [
                'class' => DynamicFieldsBehavior::class,
                'fields' => $this->getAdditionalFieldsNames(),
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_client' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
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

Usage for JsonDynamicFieldsBehavior
-----------------------------------

Using this behavior is very similar to using it with the `DynamicFieldsBehavior`. This behavior store dynamic fields in separate column of model table in json.

You should create a column in your model table, e.g. `additional_data`, with type `text`, and allow `null` value. Example for migration:

```php
$this->addColumn('your_table', 'additional_data', $this->text()->null());
```

```php
use chemezov\yii2_dynamic_fields\JsonDynamicFieldsBehavior;
use yii\behaviors\AttributeTypecastBehavior;

class User extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'dynamicFields' => [
                'class' => JsonDynamicFieldsBehavior::class,
                'fields' => ['my_boolean_attribute'],
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'my_boolean_attribute' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }
        
    public function rules() {
        return [
            [['my_boolean_attribute'], 'default', 'value' => null],
            [['my_boolean_attribute'], 'boolean'],
        ];
    }
        
    public function attributeLabels()
    {
        return [
            'my_boolean_attribute' => 'My boolean attribute',
        ];
    }
}
```
