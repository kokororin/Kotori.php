<?php
class IndexController extends Controller
{
    public function index()
    {
        $data = file_get_contents(U('Public/HELP.md'));
        $content = Markdown::convert($data);
        $this->assign('content', $content);
        $this->display();
    }

}
