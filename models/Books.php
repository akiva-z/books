<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "books".
 *
 * @property int $book_id
 * @property string|null $book_ISBN
 * @property string $book_title
 * @property int $author_id
 * @property int $publisher_id
 * @property string $publication_date
 * @property int $book_active
 * @property string|null $book_ISBN_restore
 *
 * @property Authors $author
 * @property Publishers $publisher
 */
class Books extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'books';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['book_title', 'author_id', 'publisher_id', 'publication_date'], 'required'],
            [['author_id', 'publisher_id', 'book_active'], 'integer'],
            [['publication_date'], 'date', 'format' => 'php:Y-m-d'],
            [['book_ISBN', 'book_ISBN_restore'], 'string', 'max' => 13],
            [['book_title'], 'string', 'max' => 2000],
            [['book_ISBN'], 'unique'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Authors::class, 'targetAttribute' => ['author_id' => 'author_id']],
            [['publisher_id'], 'exist', 'skipOnError' => true, 'targetClass' => Publishers::class, 'targetAttribute' => ['publisher_id' => 'publisher_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'book_id' => 'Book ID',
            'book_ISBN' => 'Book Isbn',
            'book_title' => 'Book Title',
            'author_id' => 'Author ID',
            'publisher_id' => 'Publisher ID',
            'publication_date' => 'Publication Date',
            'book_active' => 'Book Active',
            'book_ISBN_restore' => 'Book Isbn Restore',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Authors::class, ['author_id' => 'author_id']);
    }

    /**
     * Gets query for [[Publisher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPublisher()
    {
        return $this->hasOne(Publishers::class, ['publisher_id' => 'publisher_id']);
    }

    public static function cleanISBN($ISBN)
    {
        return preg_replace('/[^0-9.]+/', '', $ISBN);
    }

    public static function findByISBBN($ISBN)
    {
        $ISBN = preg_replace('/[^0-9.]+/', '', $ISBN);

        $book = self::find()
            ->where(['book_active' => true])
            ->andWhere(['book_ISBN' => self::cleanISBN($ISBN)])
            ->one();

        return $book;
    }

    public function formatAPI()
    {
        return [
            'ISBN' => $this->book_ISBN,
            'title' => $this->book_title,
            'author' => $this->author->author_name,
            'publisher' => $this->publisher->publisher_name,
            'publication_date' => $this->publication_date,
        ];
    }
}
