<?php
/**
 * 数据库配置（新增文件）
 *
 * 说明：原代码包 config/ 目录下缺失 database.php，由 Lumen 框架内置默认配置兜底。
 * 这里补一份显式配置，目的是：
 *   1) 明确 charset = utf8mb4（官方 SQL 使用 utf8mb4_general_ci）
 *   2) 关闭 strict 模式，避免 MySQL 8.0 默认 ONLY_FULL_GROUP_BY 让老代码里的
 *      统计类 GROUP BY 语句直接报错（原产品线跑 MySQL 5.7）
 *   3) prefix 固定为空 —— 官方 SQL 的表名无前缀，如 account_user_base
 */

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'mysql' => [
            'driver'         => 'mysql',
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'kuteshop'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_general_ci',
            'prefix'         => env('DB_PREFIX', ''),
            'prefix_indexes' => true,
            'strict'         => false,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

    ],

    'redis' => [

        'client'  => env('REDIS_CLIENT', 'phpredis'),
        'cluster' => false,

        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

    'migrations' => 'migrations',

];
