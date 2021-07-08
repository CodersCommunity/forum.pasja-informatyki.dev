<?php
declare(strict_types=1);

class block_pm_admin
{
    public function init_queries(array $tableslc)
    {
        $table = qa_db_add_table_prefix('blockedpw');

        $sql = 'CREATE TABLE IF NOT EXISTS `qa_blockedpw` (
            `from_user_id` int(10) unsigned NOT NULL, 
            `to_user_id` int(10) unsigned NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8';

        return in_array($table, $tableslc, true) ? null : $sql;
    }
}