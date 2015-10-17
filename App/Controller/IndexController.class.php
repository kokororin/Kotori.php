<?php
class IndexController extends Controller
{
    protected function __construct()
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

}
