<?php
    define("MYSQL_HOST", $_ENV['MYSQL_HOST']);
    define("MYSQL_USER", $_ENV['MYSQL_USER']);
    define("MYSQL_DATABASE", $_ENV['MYSQL_DATABASE']);
    define("MYSQL_PASSWORD", $_ENV['MYSQL_PASSWORD']); 
    define("JOURNAL_NAME", $_ENV['JOURNAL_NAME']);
    define("SERVER_NAME", $_ENV['SERVER_NAME'] ?? 'localhost');
    define("APP_ENV", $_ENV['APP_ENV'] ?? 'production');
    define("DOCKER_TAG", $_ENV['DOCKER_TAG'] ?? 'latest');
?>