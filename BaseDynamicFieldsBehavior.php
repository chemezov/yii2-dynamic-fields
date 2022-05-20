<?php

namespace chemezov\yii2_dynamic_fields;

use yii\base\Behavior;

abstract class BaseDynamicFieldsBehavior extends Behavior
{
    /**
     * Fields to store and load with your model. Example: ['address', 'is_client'].
     *
     * @var string[]
     */
    public $fields = [];

    /**
     * @var array
     */
    protected $_values = [];

    public function canGetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->fields);
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->fields);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_values)) {
            return $this->_values[$name];
        }
    }

    public function __set($name, $value)
    {
        if ($this->canSetProperty($name)) {
            $this->_values[$name] = $value;
        }
    }

    /**
     * Save dynamic fields from storage.
     */
    abstract public function saveDynamicFields(): void;

    /**
     * Load dynamic fields from storage.
     */
    abstract public function loadDynamicFields(): void;

    /**
     * Delete dynamic fields from storage.
     */
    abstract public function deleteDynamicFields(): void;
}
