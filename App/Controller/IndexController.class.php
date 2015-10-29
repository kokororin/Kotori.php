<?php
class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = file_get_contents(PUBLIC_DIR . '/HELP.md');
        $content = Markdown::convert($data);
        $this->assign('content', $content);
        $this->display();
    }

    public function showNews($id)
    {
        echo 'This is news No.' . $id;
    }

}
