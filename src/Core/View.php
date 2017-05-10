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
 * View Class
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Exception;
use Kotori\Debug\Hook;
use Kotori\Debug\Trace;

class View
{
    /**
     * Template Direcory
     *
     * @var string
     */
    protected $_tplDir;

    /**
     *
     * Template Path
     *
     * @var string
     */
    protected $_viewPath;

    /**
     * Variable List
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Variable List for TplInclude
     *
     * @var array
     */
    protected $_needData;

    /**
     * __get magic
     *
     * Allows view to access loaded classes using the same
     * syntax as controllers.
     *
     * @param string $key
     */
    public function __get($key)
    {
        if (property_exists(Controller::getSoul(), $key)) {
            return Controller::getSoul()->$key;
        }

        $backTrace = debug_backtrace();
        $className = get_class($backTrace[0]['object']);
        throw new \Exception($className . '::$' . $key . ' is not defined');
    }

    /**
     * __call magic
     *
     * Allows view to access controller methods
     *
     * @param  $name
     * @param  $arguments
     */
    public function __call($name, $arguments)
    {
        $callback = [Controller::getSoul(), $name];
        if (!is_callable($callback)) {
            $backTrace = debug_backtrace();
            $className = get_class($backTrace[0]['object']);
            throw new \Exception($className . '::' . $name . '() is not callable');
        }

        return call_user_func_array($callback, $arguments);
    }

    /**
     * Class constructor
     *
     * @param string $tplDir Template Directory
     * @return void
     */
    public function __construct($tplDir = null)
    {
        if (null == $tplDir) {
            $this->_tplDir = Config::getSoul()->APP_FULL_PATH . '/views/';
        } else {
            $this->_tplDir = $tplDir;
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Set variables for Template
     *
     * @param string $key key
     * @param mixed $value value
     * @return View
     */
    public function assign($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Display Output
     *
     * Processes and sends finalized output data to the browser along
     *
     * @param string $tpl Template Path
     * @return void
     */
    public function display($tpl = '')
    {
        if ('' === $tpl) {
            $tpl = CONTROLLER_NAME . '/' . ACTION_NAME;
        }

        $this->_viewPath = $this->_tplDir . $tpl . '.html';
        if (!Helper::isFile($this->_viewPath)) {
            throw new Exception('Template is not existed.');
        }

        unset($tpl);
        ob_start();
        extract($this->_data, EXTR_OVERWRITE);
        include $this->_viewPath;
        $buffer = ob_get_contents();
        ob_get_clean();
        $output = Helper::comment() . preg_replace('|</body>.*?</html>|is', '', $buffer, -1, $count) . Trace::getSoul()->showTrace();
        if ($count > 0) {
            $output .= '</body></html>';
        }

        echo $output;
    }

    /**
     * Include Template
     *
     * @param string $path Template Path
     * @param array $data Data Array
     * @return void
     */
    public function need($path, $data = [])
    {
        $this->_needData = [
            'path' => Config::getSoul()->APP_FULL_PATH . '/views/' . $path . '.html',
            'data' => $data,
        ];
        unset($path);
        unset($data);
        extract($this->_needData['data']);
        include $this->_needData['path'];
    }

}
