<?php
namespace app\controllers;

use Kotori\Core\Controller;

class Test extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route(method = "get", uri = "test/get")
     */
    public function receiveGet()
    {
        $this->response->throwJSON($this->request->get());
    }

    /**
     * @route(method = "post", uri = "test/post")
     */
    public function receivePost()
    {
        $this->response->throwJSON($this->request->post());
    }

}
