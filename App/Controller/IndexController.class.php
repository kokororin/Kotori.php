<?php
class IndexController extends Kotori_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $news_list = $this->NewsModel->getNewsList();
        $this->view->assign('title', 'Welcome to Kotori.php')
            ->display();
    }

    public function showNews($id)
    {
        echo 'This is news No.' . $id;
    }

    public function addNews()
    {
        $this->view->display();
    }

    public function insertNews()
    {
        print_r($this->request->input('post.'));
    }

}
