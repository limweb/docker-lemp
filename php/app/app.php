<?php
(session_status() == PHP_SESSION_NONE ? session_start() : null);
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
$dotenv = Dotenv\Dotenv::create(__DIR__);  //ไปแก้ config ที่ .env
$dotenv->load();
require_once __DIR__ . '/configs/config.php'; //ห้ามแก้
define('SRVPATH', __DIR__); // ห้ามแก้
$server = new \Servit\Restsrv\RestServer\RestServer($sysconfig, APPMODE); // config = class config and  mode = debug / production see config.php
$server->includeDir(__DIR__ . '/models/'); //ห้ามแก้ ยกเว้น เปลี่ยน folder
$server->includeDir(__DIR__ . '/services/');//ห้ามแก้ ยกเว้น เปลี่ยน folder แต่สามารถเพิ่ม folder ได้
include __DIR__ . '/route/routes.php';
$server->handle();