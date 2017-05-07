<?php
namespace app\controllers;

use Kotori\Core\Controller;

class Test extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function receiveGet()
    {
        $this->response->throwJSON($this->request->get());
    }

    public function receivePost()
    {
        $this->response->throwJSON($this->request->post());
    }
}
