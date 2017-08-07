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

use Kotori\Debug\Hook;
use Kotori\Exception\NotFoundException;
use Kotori\Facade\Config;
use Kotori\Facade\Trace;
use Kotori\Traits\ControllerMethodsTrait;

class View
{
    use ControllerMethodsTrait;
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
     * Class constructor
     *
     * @param  string $tplDir
     * @return void
     */
    public function __construct($tplDir = null)
    {
        if (null == $tplDir) {
            $this->_tplDir = Config::get('APP_FULL_PATH') . '/views/';
        } else {
            $this->_tplDir = $tplDir;
        }

        Hook::listen(__CLASS__);
    }

    /**
     * Set variables for Template
     *
     * @param  string $key
     * @param  mixed  $value
     * @return \Kotori\Core\View
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
     * @param  string $tpl
     * @return void
     */
    public function display($tpl = '')
    {
        if ('' === $tpl) {
            $tpl = CONTROLLER_NAME . '/' . ACTION_NAME;
        }

        $this->_viewPath = $this->_tplDir . $tpl . '.html';
        if (!Helper::isFile($this->_viewPath)) {
            throw new NotFoundException('Template is not existed.');
        }

        unset($tpl);
        ob_start();
        extract($this->_data, EXTR_OVERWRITE);
        include $this->_viewPath;
        $buffer = ob_get_contents();
        ob_get_clean();
        $output = Helper::comment() . preg_replace('|</body>.*?</html>|is', '', $buffer, -1, $count) . Trace::showTrace();
        if ($count > 0) {
            $output .= '</body></html>';
        }

        echo $output;
    }

    /**
     * Include Template
     *
     * @param  string $path
     * @param  array  $data
     * @return void
     */
    public function need($path, $data = [])
    {
        $this->_needData = [
            'path' => Config::get('APP_FULL_PATH') . '/views/' . $path . '.html',
            'data' => $data,
        ];
        unset($path);
        unset($data);
        extract($this->_needData['data']);
        include $this->_needData['path'];
    }

}
