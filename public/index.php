<?php

use pxgamer\GitterStatusBot\Bot;

require __DIR__ . '/../vendor/autoload.php';

$bot = new Bot(__DIR__ . '/..');

try {
    $bot->checkUptime();
    $bot->postToGitter();
} catch (Exception $exception) {
    error_log($exception);
}