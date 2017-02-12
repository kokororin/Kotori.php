<?php
namespace app\controllers;

use Kotori\Core\Controller;

class Hello extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $news_list = $this->model->News->getNewsList();
        $this->view->assign('news_list', $news_list)
            ->assign('title', 'Welcome to Kotori.php')
            ->assign('logo', 'https://raw.githubusercontent.com/kokororin/Kotori.php/master/src/Kotori.gif')
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
        $this->db->update('news',
            array('hits[+]' => 1),
            array(
                'id' => 1,
            )
        );

    }

    public function pager()
    {
        header('Content-Type: text/html;charset=utf-8');
        $count = $this->db->count("test", "title");
        $Page = new \app\libraries\Page($count, 20);
        $show = $Page->show();
        $data = $this->db->select("test", "title", array(
            "LIMIT" => array($Page->firstRow, $Page->listRows),
        ));
        print_r($data);
        echo '<br/>' . $show;
    }

    public function captcha()
    {
        $captcha = new \app\libraries\Captcha();
        $captcha->getImg();
        $_SESSION['verify'] = md5($vc->getCode());
    }

    public function memcache()
    {
        $this->cache->set('testvalue', 'TESTVALUE');
        var_dump($this->cache->get('testvalue'));
        $this->cache->set('testarray', array(
            'id' => 1,
            'name' => 'abc',
        ));
        var_dump($this->cache->get('testarray'));
    }

    public function cli($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

}
