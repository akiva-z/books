<?php

use yii\db\Migration;

class m240526_125604_create_table_authors extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%authors}}',
            [
                'author_id' => $this->primaryKey(),
                'author_name' => $this->string(2000)->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%authors}}');
    }
}
