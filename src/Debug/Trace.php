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
 * Trace Class
 *
 * @package     Kotori
 * @subpackage  Debug
 * @author      Kokororin
 * @link        https://kotori.love
 */
namespace Kotori\Debug;

use Kotori\Core\Container;
use Kotori\Core\Database;
use Kotori\Core\Handle;
use Kotori\Core\Helper;

class Trace
{
    /**
     * traceTab
     *
     * @var array
     */
    protected $traceTabs = [
        'BASE' => 'Basic',
        'CONFIG' => 'Config',
        'SERVER' => 'Server',
        'COOKIE' => 'Cookie',
        'FILE' => 'File',
        'FLOW' => 'Flow',
        'ERROR' => 'Error',
        'SQL' => 'SQL',
        'SUPPORT' => 'Support',
    ];

    /**
     * Class constructor
     *
     * Initialize Trace.
     */
    public function __construct()
    {
        Hook::listen(__CLASS__);
    }

    /**
     * Get Page Trace
     *
     * @return array
     */
    protected function getTrace()
    {
        $files = get_included_files();
        $config = Container::get('config')->getArray();
        $server = $_SERVER;
        $cookie = $_COOKIE;
        $info = [];
        foreach ($files as $key => $file) {
            $info[] = $file . ' ( ' . number_format(filesize($file) / 1024, 2) . ' KB )';
        }

        $hook = Hook::getTags();
        foreach ($hook as $key => $value) {
            $hook[$key] = ' ( ' . $value . ' μs )';
        }

        $error = Handle::$errors;

        $sql = Database::$queries;

        $base = [
            'Request Info' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD'] . ' : ' . $_SERVER['PHP_SELF'],
            'Run Time' => Hook::listen(\Kotori\App::class) . 'μs',
            'TPR' => Hook::listen(\Kotori\App::class) != 0 ? pow(10, 6) / Hook::listen(\Kotori\App::class) . ' req/s' : '+inf',
            'Memory Uses' => number_format((memory_get_usage() - KOTORI_START_MEMORY) / 1024, 2) . ' kb',
            'SQL Queries' => count($sql) . ' queries ',
            'File Loaded' => count(get_included_files()),
            'Session Info' => 'SESSION_ID=' . session_id(),
        ];

        $support = [
            '<a target="_blank" href="https://github.com/kokororin/Kotori.php">GitHub</a>',
            '<a target="_blank" href="https://kotori.love/archives/kotori-php-framework.html">Blog</a>',
        ];

        $trace = [];
        foreach ($this->traceTabs as $name => $title) {
            switch (strtoupper($name)) {
                case 'BASE':
                    $trace[$title] = $base;
                    break;
                case 'CONFIG':
                    $trace[$title] = $config;
                    break;
                case 'SERVER':
                    $trace[$title] = $server;
                    break;
                case 'COOKIE':
                    $trace[$title] = $cookie;
                    break;
                case 'FILE':
                    $trace[$title] = $info;
                    break;
                case 'FLOW':
                    $trace[$title] = $hook;
                    break;
                case 'ERROR':
                    $trace[$title] = $error;
                    break;
                case 'SQL':
                    $trace[$title] = $sql;
                    break;
                case 'SUPPORT':
                    $trace[$title] = $support;
                    break;
            }
        }

        return $trace;
    }

    /**
     * Show Page Trace in Output
     *
     * @return string
     */
    public function showTrace()
    {
        if (!Container::get('config')->get('app_debug')) {
            return;
        }

        $trace = $this->getTrace();
        $tpl = '
<!-- Kotori Page Trace (If you want to hide this feature, please set APP_DEBUG to false.)-->
<div id="page_trace" style="position: fixed; bottom: 0; right: 0; font-size: 14px; width: 100%; z-index: 999999; color: #000; text-align: left; font-family: \'Hiragino Sans GB\',\'Microsoft YaHei\',\'WenQuanYi Micro Hei\';">
<div id="page_trace_tab" style="display: none; background: white; margin: 0; height: 250px;">
<div id="page_trace_tab_tit" style="height: 30px; padding: 6px 12px 0; border-bottom: 1px solid #ececec; border-top: 1px solid #ececec; font-size: 16px">';
        foreach ($trace as $key => $value) {
            $tpl .= '<span id="page_trace_tab_tit_' . strtolower($key) . '" style="color: #000; padding-right: 12px; height: 30px; line-height: 30px; display: inline-block; margin-right: 3px; cursor: pointer; font-weight: 700">' . $key . '</span>';
        }

        $tpl .= '</div>
<div id="page_trace_tab_cont" style="overflow: auto; height: 212px; padding: 0; line-height: 24px">';
        foreach ($trace as $key => $info) {
            $tpl .= '<div id="page_trace_tab_cont_' . strtolower($key) . '" style="display: none;">
    <ol style="padding: 0; margin: 0">';
            if (is_array($info)) {
                foreach ($info as $k => $val) {
                    $val = is_array($val) ? print_r($val, true) : (is_bool($val) ? json_encode($val) : $val);
                    $val = (in_array($key, ['Support'])) ? $val : htmlentities($val, ENT_COMPAT, 'utf-8');
                    $tpl .= '<li style="border-bottom: 1px solid #EEE; font-size: 14px; padding: 0 12px">' . (is_numeric($k) ? '' : $k . ' : ') . $val . '</li>';
                }
            }

            $tpl .= '</ol>
    </div>';
        }

        $tpl .= '</div>
</div>
<div id="page_trace_close" style="display: none; text-align: right; height: 15px; position: absolute; top: 10px; right: 12px; cursor: pointer;"><img style="vertical-align: top;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw==" /></div>
</div>
<div id="page_trace_open" style="height: 30px; float: right; text-align: right; overflow: hidden; position: fixed; bottom: 0; right: 0; color: #000; line-height: 30px; cursor: pointer;"><div style="background: #232323; color: #FFF; padding: 0 6px; float: right; line-height: 30px; font-size:14px">';
        $errorCount = count(Handle::$errors);

        if ($errorCount == 0) {
            $tpl .= Hook::listen(\Kotori\App::class) . 'μs';
        } else {
            $tpl .= $errorCount . ' errors';
        }

        $tpl .= '</div><img width="30" style="border-left:2px solid black;border-top:2px solid black;border-bottom:2px solid black;" title="ShowPageTrace" src="' . Helper::logo() . '"></div>';
        $tpl .= '<script type="text/javascript">
(function() {
\'use strict\';
var tab_tit = document.getElementById(\'page_trace_tab_tit\').getElementsByTagName(\'span\'),
    tab_cont = document.getElementById(\'page_trace_tab_cont\').getElementsByTagName(\'div\'),
    open = document.getElementById(\'page_trace_open\'),
    close = document.getElementById(\'page_trace_close\').children[0],
    trace = document.getElementById(\'page_trace_tab\'),
    storage = localStorage.getItem(\'kotori_show_page_trace\'),
    history = (storage !== null && storage.split(\'|\')) ||  [0,0],
    bindClick = function(dom, listener) {
        if (dom.addEventListener) {
            dom.addEventListener(\'click\', listener, false);
        } else {
            dom.attachEvent(\'onclick\', listener);
        }
    };
bindClick(open, function() {
    trace.style.display = \'block\';
    this.style.display = \'none\';
    close.parentNode.style.display = \'block\';
    history[0] = 1;
    localStorage.setItem(\'kotori_show_page_trace\', history.join(\'|\'));
});
bindClick(close, function() {
    trace.style.display = \'none\';
    this.parentNode.style.display = \'none\';
    open.style.display = \'block\';
    history[0] = 0;
    localStorage.setItem(\'kotori_show_page_trace\', history.join(\'|\'));
});
for (var i = 0; i < tab_tit.length; i++) {
    bindClick(tab_tit[i], (function(i) {
        return function() {
            for (var j = 0; j < tab_cont.length; j++) {
                tab_cont[j].style.display = \'none\';
                tab_tit[j].style.color = \'#999\';
            }
            tab_cont[i].style.display = \'block\';
            tab_tit[i].style.color = \'#000\';
            history[1] = i;
            localStorage.setItem(\'kotori_show_page_trace\', history.join(\'|\'));
        };
    })(i));
}
parseInt(history[0]) && open.click();
tab_tit[history[1]].click();
})();
</script>';
        return $tpl;
    }
}
