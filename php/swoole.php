<?php
$http = new swoole_http_server("0.0.0.0", 80);

$http->on("start", function ($server) {
    echo "Swoole http server is started at http://0.0.0.0:80\n";
});

$http->on("request", function ($request, $response) {
    $response->header("Content-Type", "text/plain");
    $response->end("Hello Swoole\n");
});

$http->start();
