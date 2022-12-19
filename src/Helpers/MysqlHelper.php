<?php

namespace Riskyworks\WordpressMigrator\Helpers;

class MysqlHelper
{
    public static function exchange($query)
    {
        $result = [];

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $result[] = $row;
            }
        }

        return $result;
    }
}