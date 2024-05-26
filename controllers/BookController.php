<?php

namespace app\controllers;

use app\models\Authors;
use app\models\Books;
use app\models\Publishers;
use Yii;
use yii\data\Pagination;
use yii\rest\Controller;
use yii\web\Response;

class BookController extends Controller
{
    private $post;

    public function beforeAction($event)
    {
        $this->post = Yii::$app->request->post();

        return parent::beforeAction($event);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionFind()
    {
        $page_size = $params['page_size'] ?? 10;
        $page = ($params['page'] ?? 1) - 1;

        $result_data = [
            'books' => [],
            'page_size' => $page_size,
            'pages_count' => 0,
        ];

        if(isset($this->post['author'])) {
            $author = Authors::find()
                ->where(['author_name' => trim($this->post['author'])])
                ->one();

            if(!$author) {
                $this->success($result_data);
                return;
            }
        }

        $books = Books::find()
            ->where(['book_active' => true]);
        if(isset($author)) $books = $books->andWhere(['author_id' => $author->author_id]);

        $pages = new Pagination(['totalCount' => $books->count()]);
        $pages->setPageSize($page_size);
        $pages->setPage($page);
        $pages_count = $pages->pageCount;

        $result_data['pages_count'] = $pages_count;

        $books = $books
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        foreach($books as $book) {
            $result_data['books'][] = $book->formatAPI();
        }

        $this->success($result_data);
    }

    public function actionAdd()
    {
        $required = [
            'ISBN',
            'title',
            'author',
            'publisher',
            'publication_date',
        ];

        if(!$this->checkParams($required)) {
            return;
        }

        $book = Books::findByISBBN($this->post['ISBN']);

        if($book) {
            $this->fail('Book with ISBN '.$this->post['ISBN'].' already exists');
            return;
        }

        $author = Authors::find()
            ->where(['author_name' => trim($this->post['author'])])
            ->one();

        if(!$author) {
            $author = new Authors([
                'author_name' => trim($this->post['author'])
            ]);

            if(!$author->save()) {
                $this->fail(json_encode($author->getErrors()));
                return;
            }
        }

        $publisher = Publishers::find()
            ->where(['publisher_name' => trim($this->post['publisher'])])
            ->one();

        if(!$publisher) {
            $publisher = new Publishers([
                'publisher_name' => trim($this->post['publisher'])
            ]);

            if(!$publisher->save()) {
                $this->fail(json_encode($publisher->getErrors()));
                return;
            }
        }

        $book = new Books([
            'book_ISBN' => Books::cleanISBN($this->post['ISBN']),
            'book_title' => trim($this->post['title']),
            'author_id' => $author->author_id,
            'publisher_id' => $publisher->publisher_id,
            'publication_date' => $this->post['publication_date'],
        ]);

        if(!$book->save()) {
            $this->fail(json_encode($book->getErrors()));
            return;
        }

        $result_data['book'] = $book->formatAPI();

        $this->success($result_data);
    }

    public function actionGet()
    {
        $required = [
            'ISBN',
        ];

        if(!$this->checkParams($required)) {
            return;
        }

        $book = Books::findByISBBN($this->post['ISBN']);

        if(!$book) {
            $this->fail('Book with ISBN '.$this->post['ISBN'].' not found');
            return;
        }

        $result_data['book'] = $book->formatAPI();

        $this->success($result_data);
    }

    public function actionDelete()
    {
        $required = [
            'ISBN',
        ];

        if(!$this->checkParams($required)) {
            return;
        }

        $book = Books::findByISBBN($this->post['ISBN']);

        if(!$book) {
            $this->fail('Book with ISBN '.$this->post['ISBN'].' not found');
            return;
        }

        $book->book_ISBN_restore = $book->book_ISBN;
        $book->book_ISBN = null;
        $book->book_active = 0;
        $book->save();

        $this->success();
    }

    public function actionUpdate()
    {
        $required = [
            'ISBN',
        ];

        if(!$this->checkParams($required)) {
            return;
        }

        $book = Books::findByISBBN($this->post['ISBN']);

        if(!$book) {
            $this->fail('Book with ISBN '.$this->post['ISBN'].' not found');
            return;
        }

        if(isset($this->post['author']) && trim($this->post['author'])!='')
        {
            $author = Authors::find()
                ->where(['author_name' => trim($this->post['author'])])
                ->one();

            if(!$author) {
                $author = new Authors([
                    'author_name' => trim($this->post['author'])
                ]);

                if(!$author->save()) {
                    $this->fail(json_encode($author->getErrors()));
                    return;
                }
            }

            $book->author_id = $author->author_id;
        }

        if(isset($this->post['publisher']) && trim($this->post['publisher'])!='')
        {
            $publisher = Publishers::find()
                ->where(['publisher_name' => trim($this->post['publisher'])])
                ->one();

            if(!$publisher) {
                $publisher = new Publishers([
                    'publisher_name' => trim($this->post['publisher'])
                ]);

                if(!$publisher->save()) {
                    $this->fail(json_encode($publisher->getErrors()));
                    return;
                }
            }

            $book->publisher_id = $publisher->publisher_id;
        }

        if(isset($this->post['title']) && trim($this->post['title'])!='')
        {
            $book->book_title = trim($this->post['title']);
        }

        if(isset($this->post['publication_date']) && trim($this->post['publication_date'])!='')
        {
            $book->publication_date = $this->post['publication_date'];
        }

        if(!$book->save()) {
            $this->fail(json_encode($book->getErrors()));
            return;
        }

        $result_data['book'] = $book->formatAPI();

        $this->success($result_data);
    }

    private function checkParams($required) 
    {
        $check = true;

        foreach($required as $param) {
            if(!isset($this->post[$param])) {
                $this->fail('Missed param: '.$param);
                $check = false;
                break;
            }
        }

        return $check;
    }

    private function success($data = null)
    {
        $return = [
            'status' => true,
            'error_message' => null,
            'data' => $data
        ];

        $this->asJson($return);
    }

    private function fail($error_message = null)
    {
        $return = [
            'status' => false,
            'error_message' => $error_message,
            'data' => null
        ];

        $this->asJson($return);
    }
}
