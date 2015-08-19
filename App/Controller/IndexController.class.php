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

    public function pager()
    {
        $count = M()->count("SELECT coid,text FROM typecho_comments");
        $Pager = new Page($count, 5);
        $show = $Pager->show();
        $list = M()->query("SELECT coid,text FROM typecho_comments LIMIT {$Pager->firstRow},{$Pager->listRows}");
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function test($id = 1, $type = 'view')
    {
        echo 'Type is ' . $type . ' and id is ' . $id;
        echo '<br>' . U('Index/test', array('id' => 2, 'type' => 'edit'));
    }

}
