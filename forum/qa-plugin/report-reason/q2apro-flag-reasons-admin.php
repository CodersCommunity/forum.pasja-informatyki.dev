<?php

class q2apro_flagreasons_admin
{
    const NOTICE_LENGTH = 255;

    public function init_queries($tablesLc)
    {
        $tableName = qa_db_add_table_prefix('flagreasons');
        $result = null;

        if (!in_array($tableName, $tablesLc, true)) {
            require_once QA_INCLUDE_DIR . 'qa-app-users.php';

            $result = '
                CREATE TABLE IF NOT EXISTS `' . $tableName . '` (
                  `userid` int(10) UNSIGNED NOT NULL,
                  `postid` int(10) UNSIGNED NOT NULL,
                  `reasonid` int(10) UNSIGNED NOT NULL,
                  `notice` varchar(' . self::NOTICE_LENGTH . ') NULL,
                  PRIMARY KEY (userid, postid)
                )
                ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ';
        }

        return $result;
    }

    public function option_default($option)
    {
        return 'q2apro_flagreasons_enabled' === $option ? 1 : null;
    }

    public function allow_template($template)
    {
        return 'admin' !== $template;
    }
}
