<?php
$server->addThemeClass('RootThemeController', 'sys');
$server->addClass('RootController', '', 'sys');
$server->addClass('JwtController', '', '');
if (file_exists(__DIR__ . '/routebygen.php')) {
    require_once __DIR__ . '/routebygen.php';
}
