<?php
require_once __DIR__ . '/app/app.php';

// if (is_file(__DIR__ . '/public/index.php')) {
//     require __DIR__ . '/public/index.php';

//     return;
// }

// $works = "<p>If you see this, that means it works!</p>\n\n";
// echo PHP_SAPI == 'cli' ? strip_tags($works) : $works;

// $db = new PDO(
//     'mysql:host=127.0.0.1;port=3306;dbname=' . (getenv('MYSQL_DATABASE') ?: 'test'),
//     getenv('MYSQL_USER') ?: 'root',
//     getenv('MYSQL_PASSWORD') ?: '1234567890'
// );

// $pdb = new PDO(
//     'pgsql:host=127.0.0.1;port=5432;dbname=' . (getenv('PGSQL_DATABASE') ?: 'test'),
//     getenv('PGSQL_USER') ?: 'postgres',
//     getenv('PGSQL_PASSWORD') ?: '1234567890'
// );

// if (PHP_SAPI !== 'cli') echo "<pre>\n";

// echo 'MySQL NOW(): ', $db->query('SELECT NOW()')->fetchColumn() . "\n";
// echo 'PgSQL NOW(): ', $pdb->query('SELECT NOW()')->fetchColumn() . "\n";
// echo "\n";
// echo 'PHP: ', phpversion(), "\n\n";
// echo "Extensions: ", count($extensions = get_loaded_extensions()), "\n";

// foreach (array_chunk($extensions, 4) as $exts) {
//     foreach ($exts as $ext) {
//         echo '- ' . str_pad($ext, 18, ' ', STR_PAD_RIGHT);
//     }
//     echo "\n";
// }

// echo PHP_SAPI === 'cli'
//     ? "\nSource code: https://github.com/adhocore/docker-lemp\n\n"
//     : "</pre>\n"
//         . 'Source code: <a href="https://github.com/adhocore/docker-lemp" target="_blank">adhocore/docker-lemp</a>'
//         . ' | Adminer: <a href="/adminer?server=127.0.0.1%3A3306&username=root" target="_blank">mysql</a>, '
//         . ' <a href="/adminer?pgsql=127.0.0.1%3A5432&username=postgres" target="_blank">postgres</a>'
//         . "\n";