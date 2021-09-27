<?php

use yii\db\Migration;

/**
 * Class m210927_085312_change_dynamic_fields_length
 */
class m210927_085312_change_dynamic_fields_length extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // To prevent "Specified key was too long; max key length is 1000 bytes" error.
        $this->alterColumn('{{%dynamic_fields}}', 'model', $this->string(150)->notNull()->comment('Model name'));
        $this->alterColumn('{{%dynamic_fields}}', 'field', $this->string(50)->notNull()->comment('Field name'));

        // Change `value` column: string -> text.
        $this->alterColumn('{{%dynamic_fields}}', 'value', $this->text()->null()->comment('Field value'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
