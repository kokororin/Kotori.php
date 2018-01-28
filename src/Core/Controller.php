<?php
/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2017 Kotori Technology. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Application Controller Class
 *
 * This class object is the super class .
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;

class Controller
{
    /**
     * DB selector
     *
     * @param  string   $key
     * @return \Kotori\Core\Database
     */
    protected function db($key = null)
    {
        return Database::getInstance($key);
    }

    /**
     * Class constructor
     *
     * Initialize view and database classes.
     */
    public function __construct()
    {
        $this->view = new View();
        $this->response = Container::get('response');
        $this->request = Container::get('request');
        $this->route = Container::get('route');
        $this->db = $this->db();
        $this->model = Container::get('model/provider');
        $this->config = Container::get('config');
        $this->cache = Container::get('cache');
        $this->logger = Container::get('logger');
        Hook::listen(__CLASS__);
    }
}
