<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "publishers".
 *
 * @property int $publisher_id
 * @property string $publisher_name
 *
 * @property Books[] $books
 */
class Publishers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'publishers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['publisher_name'], 'required'],
            [['publisher_name'], 'string', 'max' => 2000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'publisher_id' => 'Publisher ID',
            'publisher_name' => 'Publisher Name',
        ];
    }

    /**
     * Gets query for [[Books]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Books::class, ['publisher_id' => 'publisher_id']);
    }
}
