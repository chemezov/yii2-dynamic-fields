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

/**
 * Behavior to store dynamic fields in separate table.
 *
 * Class DynamicFieldsBehavior
 * @package chemezov\yii2_dynamic_fields
 */
class DynamicFieldsBehavior extends BaseDynamicFieldsBehavior
{
    /**
     * You can set custom model class. Default is short name of owner class.
     *
     * @var string
     */
    public $modelName;

    public $tableName = '{{%dynamic_fields}}';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'loadDynamicFields',
            ActiveRecord::EVENT_AFTER_INSERT => 'saveDynamicFields',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveDynamicFields',
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteDynamicFields',
        ];
    }

    /**
     * Save fields values in separate table.
     *
     * @throws \yii\db\Exception
     */
    public function saveDynamicFields(): void
    {
        $this->deleteDynamicFields();

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

    /**
     * Load fields values from separate table.
     */
    public function loadDynamicFields(): void
    {
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

    public function deleteDynamicFields(): void
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
