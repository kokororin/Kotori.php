<?php
namespace app\controllers;

use Kotori\Core\Controller;

class Hello extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route(uri = "/")
     */
    public function index()
    {
        $news_list = $this->model->News->getNewsList();
        $this->view->assign('news_list', $news_list)
            ->assign('title', 'Welcome to Kotori.php')
            ->assign('logo', 'https://cdn.rawgit.com/kokororin/Kotori.php/master/src/Kotori.jpg')
            ->display();
    }

    /**
     * @route(uri = "news/([0-9])", regexp = "$1")
     */
    public function showNews($id)
    {
        echo 'This is news No.' . $id;
    }

    /**
     * @route(method = "get", uri = "add")
     */
    public function addNews()
    {
        $this->view->display();
    }

    /**
     * @route(method = "post", uri = "add")
     */
    public function insertNews()
    {
        print_r($this->request->post());
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

    /**
     * @route(uri = "captcha")
     */
    public function captcha()
    {
        $captcha = new \app\libraries\Captcha();
        $captcha->getImg();
        $this->request->session('verify', md5($vc->getCode()));
    }

    /**
     * @route(uri = "memcache")
     */
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

    /**
     * @route(method="cli", uri = "cliTest/(.*)", regexp = "$1")
     */
    public function cli($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

}
