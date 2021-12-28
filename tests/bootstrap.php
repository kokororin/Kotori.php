<?php
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
