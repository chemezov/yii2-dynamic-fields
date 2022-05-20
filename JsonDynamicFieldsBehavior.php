<?php

namespace chemezov\yii2_dynamic_fields;

use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Behavior to store dynamic fields in separate attribute of model.
 *
 * Class JsonDynamicFieldsBehavior
 * @package chemezov\yii2_dynamic_fields
 */
class JsonDynamicFieldsBehavior extends BaseDynamicFieldsBehavior
{
    /**
     * Attribute name with json data.
     *
     * @var string
     */
    public $attributeName = 'additional_data';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'loadDynamicFields',
            ActiveRecord::EVENT_BEFORE_INSERT => 'saveDynamicFields',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'saveDynamicFields',
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteDynamicFields',
        ];
    }

    public function loadDynamicFields(): void
    {
        $data = $this->owner->{$this->attributeName} ?: [];

        if (is_string($data)) {
            $data = Json::decode($data);
        }

        foreach ($data as $field => $value) {
            if (in_array($field, $this->fields)) {
                $this->owner->$field = $value;
            }
        }
    }

    public function saveDynamicFields(): void
    {
        $this->owner->{$this->attributeName} = Json::encode($this->_values);
    }

    public function deleteDynamicFields(): void
    {
        $this->_values = [];
        $this->owner->{$this->attributeName} = null;
    }
}
