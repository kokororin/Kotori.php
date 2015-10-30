<?php
class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->display();
    }

    public function showNews($id)
    {
        echo 'This is news No.' . $id;
    }

}
