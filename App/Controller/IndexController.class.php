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
        $this->view->display();
    }

    public function showNews($id)
    {
        echo 'This is news No.' . $id;
    }

}
