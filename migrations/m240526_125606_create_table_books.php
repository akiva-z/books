<?php

use yii\db\Migration;

class m240526_125606_create_table_books extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%books}}',
            [
                'book_id' => $this->primaryKey(),
                'book_ISBN' => $this->string(13),
                'book_title' => $this->string(2000)->notNull(),
                'author_id' => $this->integer()->notNull(),
                'publisher_id' => $this->integer()->notNull(),
                'publication_date' => $this->date()->notNull(),
                'book_active' => $this->tinyInteger()->notNull()->defaultValue('1'),
                'book_ISBN_restore' => $this->string(13),
            ],
            $tableOptions
        );

        $this->createIndex('author_id', '{{%books}}', ['author_id']);
        $this->createIndex('book_ISBN', '{{%books}}', ['book_ISBN'], true);
        $this->createIndex('publisher_id', '{{%books}}', ['publisher_id']);

        $this->addForeignKey(
            'books_ibfk_1',
            '{{%books}}',
            ['author_id'],
            '{{%authors}}',
            ['author_id'],
            'RESTRICT',
            'NO ACTION'
        );
        $this->addForeignKey(
            'books_ibfk_2',
            '{{%books}}',
            ['publisher_id'],
            '{{%publishers}}',
            ['publisher_id'],
            'RESTRICT',
            'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%books}}');
    }
}
