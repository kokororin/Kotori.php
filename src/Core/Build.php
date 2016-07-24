<?php
/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2016 Kotori Technology. All rights reserved.
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
 * Build Class
 *
 * This class builds folders for Kotori.php
 *
 * @package     Kotori
 * @subpackage  Core
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Core;

use Kotori\Debug\Hook;

class Build
{
    /**
     * App path
     * @var string
     */
    protected $_appPath;

    /**
     * folders
     * @var array
     */
    protected $_folders = array(
        'controllers',
        'libraries',
        'logs',
        'models',
        'views',
    );
    /**
     * Class constructor
     *
     * Initialize Build Class.
     *
     * @return void
     */
    public function __construct($appPath)
    {
        $this->_appPath = $appPath;
        $this->startBuild();
        Hook::listen('Build');
    }

    /**
     * start to build folders
     *
     * @return void
     */
    protected function startBuild()
    {
        if (is_dir($this->_appPath)) {
            return;
        }
        foreach ($this->_folders as $folder) {
            Common::mkdirs($this->_appPath . '/' . $folder);
        }
        if (!is_file($this->_appPath . '/common.php')) {
            file_put_contents($this->_appPath . '/common.php', '<?php
// common functions');
        }
    }
}
