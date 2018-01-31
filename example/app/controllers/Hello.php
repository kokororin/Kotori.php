<?php
namespace app\controllers;

use app\libraries\Captcha;
use Kotori\Core\Controller;
use Kotori\Facade\Cache;
use Kotori\Facade\Request as FacadeRequest;
use Kotori\Http\Request;

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
     * @route(uri = "news/([0-9])", params = "$1")
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
    public function insertNews(Request $request)
    {
        print_r(FacadeRequest::post());
        print_r($request->post());
    }

    /**
     * @route(uri = "captcha")
     */
    public function captcha()
    {
        $captcha = new Captcha();
        $captcha->getImg();
        $this->request->session('verify', md5($vc->getCode()));
    }

    /**
     * @route(uri = "memcache")
     */
    public function memcache()
    {
        Cache::set('testvalue', 'TESTVALUE');
        var_dump(Cache::get('testvalue'));
        Cache::set('testarray', array(
            'id' => 1,
            'name' => 'abc',
        ));
        var_dump(Cache::get('testarray'));
    }

    /**
     * @route(uri = "json")
     */
    public function json()
    {
        return [
            'hello' => 'world',
        ];
    }

    /**
     * @route(method = "cli", uri = "cliTest/(.*)", params = "$1")
     */
    public function cli($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

}
