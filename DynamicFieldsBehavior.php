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
     * @var array
     */
    public $fields = [];

    public $tableName = '{{%dynamic_fields}}';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteModelValues',
        ];
    }

    public function afterFind($event)
    {
        /* Load fields values */
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

    public function afterSave($event)
    {
        /* Save fields values in separate table */
        $this->deleteModelValues();

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

    public function deleteModelValues()
    {
        Yii::$app->db->createCommand()->delete($this->tableName, ['model' => $this->getModelClass(), 'model_id' => $this->getPrimaryKey()])->execute();
    }

    protected function getModelClass()
    {
        return $this->modelName ?: (new \ReflectionClass($this->owner))->getShortName();
    }

    protected function getPrimaryKey()
    {
        return $this->owner->getPrimaryKey();
    }
}
