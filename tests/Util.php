<?php
namespace Kotori\Tests;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use PDO;
use PDOException;

class Util
{
    public static function get($url, $params = [])
    {
        $client = new HttpClient();
        try {
            $response = $client->request('GET', $url, [
                'query' => $params,
            ]);
        } catch (RequestException $e) {
            self::parseErrorResponse($e);
        }

        return (string) $response->getBody();
    }

    public static function post($url, $params = [])
    {
        $client = new HttpClient();
        try {
            $response = $client->request('POST', $url, [
                'form_params' => $params,
            ]);
        } catch (RequestException $e) {
            self::parseErrorResponse($e);
        }

        return (string) $response->getBody();
    }

    public static function postJSON($url, $params = [])
    {
        $client = new HttpClient();
        try {
            $response = $client->request('POST', $url, [
                'body' => json_encode($params),
            ]);
        } catch (RequestException $e) {
            self::parseErrorResponse($e);
        }

        return (string) $response->getBody();
    }

    private static function parseErrorResponse($exception)
    {
        $response = $exception->getResponse();
        throw new Exception('Error: ' . $response->getStatusCode() . ' Info: ' . $response->getHeaderLine('Kotori-Debug'));
    }

    public static function startServer()
    {
        // Command that starts the built-in web server
        exec('pid=$(lsof -i:' . getenv('WEB_SERVER_PORT') . ' -t); kill -TERM $pid || kill -KILL $pid 2> /dev/null');
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
        return (int) $output[0];
    }

    public static function canConnectToServer()
    {
        // Disable error handler for now
        set_error_handler(function () {
            return true;
        });
        // Try to open a connection
        $sp = fsockopen(getenv('WEB_SERVER_HOST'), getenv('WEB_SERVER_PORT'));
        // Restore the handler
        restore_error_handler();
        if ($sp === false) {
            return false;
        }

        fclose($sp);
        return true;
    }

    public static function killProcess($pid)
    {
        echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
        exec('kill ' . (int) $pid);
    }

    public static function createTestDatabase()
    {
        try {
            $pdo = new PDO('mysql:host=' . getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PWD'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('DROP DATABASE IF EXISTS `' . getenv('MYSQL_DB') . '`;
CREATE DATABASE `' . getenv('MYSQL_DB') . '`;
USE `' . getenv('MYSQL_DB') . '`;

DROP TABLE IF EXISTS `' . getenv('MYSQL_TABLE') . '`;
CREATE TABLE `' . getenv('MYSQL_TABLE') . '` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
            echo 'Create test db successfully' . PHP_EOL;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function dropTestDatabase()
    {
        try {
            $pdo = new PDO('mysql:host=' . getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PWD'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('DROP DATABASE IF EXISTS `' . getenv('MYSQL_DB') . '`;');
            echo 'Drop test db successfully' . PHP_EOL;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    protected static function getFileList($dir = null)
    {
        if ($dir == null) {
            $dir = __DIR__ . '/../src';
        }

        $result = [];
        $items = glob($dir . '/*.php', GLOB_BRACE);
        foreach ($items as $item) {
            if (is_file($item)) {
                array_push($result, $item);
            }
        }

        $items = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($items as $item) {
            if (is_dir($item)) {
                $result = array_merge($result, self::getFileList($item));
            }
        }

        return $result;
    }
}
