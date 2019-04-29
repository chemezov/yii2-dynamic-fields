<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dynamic_fields}}`.
 */
class m190429_074815_create_dynamic_fields_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%dynamic_fields}}', [
            'model' => $this->string()->notNull()->comment('Model name'),
            'model_id' => $this->integer()->notNull()->comment('Model primary key'),
            'field' => $this->string()->notNull()->comment('Field name'),
            'value' => $this->string()->comment('Field value'),
        ]);

        $this->addPrimaryKey('dynamic_fields_pk', '{{%dynamic_fields}}', ['model', 'model_id', 'field']);

        $this->createIndex('ix_dynamic_fields_model_id', '{{%dynamic_fields}}', ['model', 'model_id'], false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('ix_dynamic_fields_model_id', '{{%dynamic_fields}}');

        $this->dropTable('{{%dynamic_fields}}');
    }
}
