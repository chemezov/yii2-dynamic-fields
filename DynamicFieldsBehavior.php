<?php

/**
 * Behavior for adding dynamic fields to your ActiveRecord model.
 */

namespace chemezov\yii2_dynamic_fields;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class DynamicFieldsBehavior extends Behavior
{
    /**
     * You can set custom model class. Default is short name of owner class.
     *
     * @var string
     */
    public $modelName;

    /**
     * Fields to store and load with your model. Example: ['address', 'is_client'].
     *
     * @var string[]
     */
    public $fields = [];

    public $tableName = '{{%dynamic_fields}}';

    /**
     * @var array
     */
    private $_values = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'loadDynamicFieldsValues',
            ActiveRecord::EVENT_AFTER_INSERT => 'saveDynamicFieldsValues',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveDynamicFieldsValues',
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteDynamicFieldsValues',
        ];
    }

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

    public function saveDynamicFieldsValues()
    {
        /* Save fields values in separate table */
        $this->deleteDynamicFieldsValues();

        if (!empty($this->fields)) {
            $data = [];

            foreach ($this->fields as $field) {
                $data[] = [
                    $this->getModelClass(),
                    $this->getPrimaryKey(),
                    $field,
                    $this->owner->$field,
                ];
            }

            Yii::$app->db->createCommand()->batchInsert($this->tableName, ['model', 'model_id', 'field', 'value'], $data)->execute();
        }
    }

    public function loadDynamicFieldsValues()
    {
        /* Load fields values */
        if (!empty($this->fields)) {
            $query = (new Query())
                ->select(['field', 'value'])
                ->from($this->tableName)
                ->where(['model' => $this->getModelClass(), 'model_id' => $this->getPrimaryKey()]);

            foreach (ArrayHelper::map($query->all(), 'field', 'value') as $field => $value) {
                if (in_array($field, $this->fields)) {
                    $this->owner->$field = $value;
                }
            }
        }
    }

    public function deleteDynamicFieldsValues()
    {
        Yii::$app->db->createCommand()->delete($this->tableName, ['model' => $this->getModelClass(), 'model_id' => $this->getPrimaryKey()])->execute();
    }

    protected function getModelClass()
    {
        return $this->modelName ?: StringHelper::basename(get_class($this->owner));
    }

    protected function getPrimaryKey()
    {
        return $this->owner->getPrimaryKey();
    }
}
