<?php

namespace Riskyworks\WordpressMigrator\Controllers;

use Riskyworks\WordpressMigrator\Helpers\MysqlHelper;

class DatabaseMigrator
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function delete_tables_by_prefix($prefix)
    {
        $tables = $this->find_tables_by_prefix($prefix);

        $command = "DROP TABLE IF EXISTS %s";

        foreach ($tables as $table) {
            if ($this->mysqli->query(sprintf($command, $table)) === true) {
                printf("[x] Table %s deleted\n", $table);
            }
        }
    }

    public function find_tables_by_prefix($prefix)
    {
        $command = "SELECT * FROM information_schema.tables WHERE table_name LIKE '" . $prefix . "%'";
        $query = $this->mysqli->query($command);
        $data = MysqlHelper::exchange($query);

        return array_map(function ($x) {
            return $x['TABLE_NAME'];
        }, $data);
    }

    public function copy_tables($tables, $old_prefix, $new_prefix)
    {
        $create_table_command = "CREATE TABLE %s LIKE %s";
        $insert_command = "INSERT INTO %s SELECT * FROM %s";

        foreach ($tables as $table) {
            $new_table_name = str_replace($old_prefix, $new_prefix, $table);

            if ($this->mysqli->query(sprintf($create_table_command, $new_table_name, $table)) === true) {
                printf("[x] New table %s has been created\n", $new_table_name);
            }

            if ($this->mysqli->query(sprintf($insert_command, $new_table_name, $table)) === true) {
                printf("[x] Data have been copied: %s -> %s\n", $table, $new_table_name);
            }
        }
    }

    public function migrate_domain($old_domain, $new_domain, $prefix)
    {
        $wp_options_command = "UPDATE %soptions SET option_value = replace(option_value, %s, %s) WHERE option_name = 'home' OR option_name = 'siteurl'";
        $wp_posts_command_1 = "UPDATE %sposts SET guid = replace(guid, %s, %s)";
        $wp_posts_command_2 = "UPDATE %sposts SET post_content = replace(post_content, %s, %s)";
        $wp_postmeta_command = "UPDATE %spostmeta SET meta_value = replace(meta_value, %s, %s)";

        foreach ([$wp_options_command, $wp_posts_command_1, $wp_posts_command_2, $wp_postmeta_command] as $key => $command) {
            if ($this->mysqli->query(sprintf($command, $prefix, $old_domain, $new_domain)) === true) {
                printf("[x] Replacement for idx %d successes\n", $key);
            }
        }
    }
}