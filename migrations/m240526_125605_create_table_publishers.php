<?php

use yii\db\Migration;

class m240526_125605_create_table_publishers extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%publishers}}',
            [
                'publisher_id' => $this->primaryKey(),
                'publisher_name' => $this->string(2000)->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%publishers}}');
    }
}
