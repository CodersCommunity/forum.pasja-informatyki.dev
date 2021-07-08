<?php

namespace CodersCommunity;

require_once __DIR__ . '/../../vendor/autoload.php';

class q2a_googleauthenticator_admin
{
    public function init_queries()
    {
        $isActive = qa_opt('googleauthenticator_login');
        $result = null;

        if (1 === $isActive) {
            return null;
        }

        $queries = [];

        $columns = qa_db_read_all_values(qa_db_query_sub('describe ^users'));
        if (!in_array('2fa_enabled', $columns, true)) {
            $queries[] = 'ALTER TABLE ^users ADD `2fa_enabled` SMALLINT (1) DEFAULT 0';
        }

        if (!in_array('2fa_change_date', $columns, true)) {
            $queries[] = 'ALTER TABLE ^users ADD `2fa_change_date` VARCHAR (80) DEFAULT 0';
        }

        if (!in_array('2fa_secret', $columns, true)) {
            $queries[] =
                'ALTER TABLE ^users ADD `2fa_secret` VARCHAR ( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL';
        }

        if (!in_array('2fa_recovery_code', $columns, true)) {
            $queries[] = 'ALTER TABLE ^users ADD `2fa_recovery_code` VARCHAR (11) DEFAULT 0';
        }

        if (!in_array('2fa_login_code', $columns, true)) {
            $queries[] = 'ALTER TABLE ^users ADD `2fa_login_code` VARCHAR (32) DEFAULT 0';
        }

        if (!in_array('2fa_login_code_created', $columns, true)) {
            $queries[] = 'ALTER TABLE ^users ADD `2fa_login_code_created` TIMESTAMP NULL DEFAULT NULL;';
        }

        if(count($queries)) {
            $result = $queries;
        }

        // we're already set up
        qa_opt('googleauthenticator_login', 1);

        return $result;
    }

    public function admin_form()
    {
        $saved = false;

        if (qa_clicked('2fa_save_button')) {
            $enabled = qa_post_text('googleauthenticator_enable_plugin');
            qa_opt('googleauthenticator_login', empty($enabled) ? 0 : 1);

            $saved = true;
        }

        return [
            'ok' => $saved ? qa_lang('plugin_2fa/saved_plugin_settings') : null,
            'fields' => [[
                'type' => 'checkbox',
                'label' => qa_opt('googleauthenticator_login') ?
                    qa_lang('plugin_2fa/enabled_plugin') :
                    qa_lang('plugin_2fa/disabled_plugin'),
                'value' => qa_opt('googleauthenticator_login') ? true : false,
                'tags' => 'NAME="googleauthenticator_enable_plugin"'
                ]
            ],
            'buttons' => [[
                'label' => qa_lang('plugin_2fa/save_settings'),
                'tags' => 'NAME="2fa_save_button"'
                ]
            ]
        ];
    }
}
