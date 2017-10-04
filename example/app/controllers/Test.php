<?php
namespace app\controllers;

use Kotori\Core\Controller;
use Kotori\Facade\Request;
use Kotori\Facade\Response;

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
        Response::throwJSON(Request::get());
    }

    /**
     * @route(method = "post", uri = "test/post")
     */
    public function receivePost()
    {
        Response::throwJSON(Request::post());
    }

}
