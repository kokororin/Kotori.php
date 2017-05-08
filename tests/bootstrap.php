<?php
// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

$autoloader->addPsr4('Kotori\Tests\\', __DIR__);

// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
    getenv('WEB_SERVER_HOST'),
    getenv('WEB_SERVER_PORT'),
    getenv('WEB_SERVER_DOCROOT')
);

echo sprintf('Running command "%s"', $command) . PHP_EOL;

// Execute the command and store the process ID
$output = [];
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
    '%s - Web server started on %s:%d with PID %d',
    date('r'),
    getenv('WEB_SERVER_HOST'),
    getenv('WEB_SERVER_PORT'),
    $pid
) . PHP_EOL;

sleep(1);

// Set env
if (getenv('CI')) {
    putenv('MYSQL_PWD=');
}

// Create test database
\Kotori\Tests\Util::createTestDatabase();

// Kill the web server when the process ends
register_shutdown_function(function () use ($pid) {
    \Kotori\Tests\Util::dropTestDatabase();
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});
