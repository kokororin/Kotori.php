<?php

/**
 * Kotori.php
 *
 * A Tiny Model-View-Controller PHP Framework
 *
 * This content is released under the Apache 2 License
 *
 * Copyright (c) 2015-2022 kokororin. All rights reserved.
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

use Kotori\Tests\Util;

// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

// Start build-in server
$pid = Util::startServer();

if (!$pid) {
    throw new RuntimeException('Could not start the web server');
}

$start = microtime(true);
$connected = false;

// Try to connect until the time spent exceeds the timeout specified in the configuration
while (microtime(true) - $start <= (int) getenv('WEB_SERVER_TIMEOUT')) {
    if (Util::canConnectToServer()) {
        $connected = true;
        break;
    }
}

if (!$connected) {
    Util::killProcess($pid);
    throw new RuntimeException(
        sprintf(
            'Could not connect to the web server within the given timeframe (%d second(s))',
            getenv('WEB_SERVER_TIMEOUT')
        )
    );
}

// Create test database
Util::createTestDatabase();

// Kill the web server when the process ends
register_shutdown_function(function () use ($pid) {
    Util::dropTestDatabase();
    Util::killProcess($pid);
});
