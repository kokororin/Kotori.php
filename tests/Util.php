<?php
namespace Kotori\Tests;

use Curl\Curl;
use Exception;
use PDO;
use PDOException;

class Util
{
    public static function get($url, $params = [])
    {
        $curl = new Curl();
        $curl->get($url, $params);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . ' Info: ' . $curl->responseHeaders['Kotori-Debug']);
        }

        return $curl->response;
    }

    public static function post($url, $params = [])
    {
        $curl = new Curl();
        $curl->post($url, $params);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . ' Info: ' . $curl->responseHeaders['Kotori-Debug']);
        }

        return $curl->response;
    }

    public static function postJSON($url, $params = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post($url, $params);
        if ($curl->error) {
            throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . ' Info: ' . $curl->responseHeaders['Kotori-Debug']);
        }

        return $curl->response;
    }

    public static function createTestDatabase()
    {
        try {
            $pdo = new PDO('mysql:host=' . getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PWD'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('DROP DATABASE IF EXISTS `' . getenv('MYSQL_DB') . '`;
CREATE DATABASE `' . getenv('MYSQL_DB') . '`;
USE `' . getenv('MYSQL_DB') . '`;

DROP TABLE IF EXISTS `table`;
CREATE TABLE `table` (
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

    protected static function getFileList($dir = __DIR__ . '/../src')
    {
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

    public static function convertArraysToSquareBrackets()
    {
        $fileList = self::getFileList();
        foreach ($fileList as $file) {
            $code = file_get_contents($file);
            $out = '';
            $brackets = [];
            $tokens = token_get_all($code);
            $l = count($tokens);
            for ($i = 0; $i < $l; $i++) {
                $token = $tokens[$i];
                if ($token === '(') {
                    $brackets[] = false;
                } elseif ($token === ')') {
                    $token = array_pop($brackets) ? ']' : ')';
                } elseif (is_array($token) && $token[0] === T_ARRAY) {
                    $a = $i + 1;
                    if (isset($tokens[$a]) && $tokens[$a][0] === T_WHITESPACE) {
                        $a++;
                    }

                    if (isset($tokens[$a]) && $tokens[$a] === '(') {
                        $i = $a;
                        $brackets[] = true;
                        $token = '[';
                    }
                }

                $out .= is_array($token) ? $token[1] : $token;
            }

            echo 'converting ' . $file . PHP_EOL;
            file_put_contents($file, $out);
        }
    }
}
