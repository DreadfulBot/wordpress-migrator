<?php
require './vendor/autoload.php';

use Riskyworks\WordpressMigrator\Controllers\DatabaseMigrator;
use Riskyworks\WordpressMigrator\Helpers\DebugHelper;

$mysql = null;
try {
    DebugHelper::beginOutput();

    // loading dotenv
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

    // establishing database connection
    $mysqli = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['DB_NAME']
    );

    // Check connection
    if ($mysqli->connect_errno) {
        printf("Failed to connect to MySQL: %s\n", $mysqli->connect_error);
    } else {
        printf("Connected!\n");
    }

    $migrator = new DatabaseMigrator($mysqli);

    // magic happens here
    $prefix_new = $_ENV['PREFIX_NEW'];
    $prefix_old = $_ENV['PREFIX_OLD'];
    $domain_old = $_ENV['DOMAIN_OLD'];
    $domain_new = $_ENV['DOMAIN_NEW'];

    $copy_candidates = $migrator->find_tables_by_prefix($prefix_new);
    $migrator->delete_tables_by_prefix($prefix_new);

    $migrator->copy_tables($copy_candidates, $prefix_old, $prefix_new);
    $migrator->migrate_domain($domain_old, $domain_new, $prefix_new);

} catch (Exception $error) {
    printf("Exception: %s\n", $error->getMessage());
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
        printf("Disconnected\n");
    }
    DebugHelper::endOutput();
}
