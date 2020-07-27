<?php
/*
    Plugin Name: On-Site-Notifications
    Plugin URI: http://www.q2apro.com/plugins/on-site-notifications
    Plugin Description: Facebook-like / Stackoverflow-like notifications on your question2answer forum that can replace all email-notifications.
    Plugin Version: → see qa-plugin.php
    Plugin Date: → see qa-plugin.php
    Plugin Author: q2apro.com
    Plugin Author URI: http://www.q2apro.com/
    Plugin License: GPLv3
    Plugin Minimum Question2Answer Version: → see qa-plugin.php
    Plugin Update Check URI: https://raw.githubusercontent.com/q2apro/q2apro-on-site-notifications/master/qa-plugin.php
    
    This program is free software. You can redistribute and modify it 
    under the terms of the GNU General Public License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    More about this license: http://www.gnu.org/licenses/gpl.html

*/

require_once QA_INCLUDE_DIR.'qa-app-options.php';

class q2apro_onsitenotifications_admin
{

    public function init_queries($tableslc) 
    {
    
        $tableName = qa_db_add_table_prefix('eventlog');
        
        if(qa_opt('event_logger_to_database') && in_array($tableName, $tableslc)) {
            if('' === qa_opt('event_logger_to_database') && '' === qa_opt('event_logger_to_files')) {
                qa_opt('event_logger_to_database', 1);
            }
        } else {
            qa_opt('event_logger_to_database', 1);
            qa_opt('event_logger_to_files', '');
            qa_opt('event_logger_directory', '');
            qa_opt('event_logger_hide_header', '');
        
            if (!in_array($tableName, $tableslc)) {
                require_once QA_INCLUDE_DIR.'qa-app-users.php';
                require_once QA_INCLUDE_DIR.'qa-db-maxima.php';

                return 'CREATE TABLE IF NOT EXISTS ^eventlog ('.
                    'datetime DATETIME NOT NULL,'.
                    'ipaddress VARCHAR (15) CHARACTER SET ascii,'.
                    'userid ' . qa_get_mysql_user_column_type() . ','.
                    'handle VARCHAR(' . QA_DB_MAX_HANDLE_LENGTH . '),'.
                    'cookieid BIGINT UNSIGNED,'.
                    'event VARCHAR (20) CHARACTER SET ascii NOT NULL,'.
                    'params VARCHAR (800) NOT NULL,'.
                    'KEY datetime (datetime),'.
                    'KEY ipaddress (ipaddress),'.
                    'KEY userid (userid),'.
                    'KEY event (event)'.
                ') ENGINE=MyISAM DEFAULT CHARSET=utf8';
            }
        }
        
        $tableName = qa_db_add_table_prefix('usermeta');
        if (!in_array($tableName, $tableslc)) {
            qa_db_query_sub(
                'CREATE TABLE IF NOT EXISTS ^usermeta (
                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext,
                PRIMARY KEY (meta_id),
                UNIQUE (user_id,meta_key)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
            );
        }
    }

    public function option_default($option)
    {
        switch($option) {
            case 'q2apro_onsitenotifications_enabled':
                return 1; // true
            case 'q2apro_onsitenotifications_nill':
                return 'N'; // days
            case 'q2apro_onsitenotifications_maxage':
                return 365; // days
            case 'q2apro_onsitenotifications_maxevshow':
                return 100; // max events to show in notify box
            case 'q2apro_onsitenotifications_newwindow':
                return 1; // true
            case 'q2apro_onsitenotifications_rtl':
                return 0; // false
            default:
                return null;
        }
    }
    
    public function allow_template($template)
    {
        return 'admin' !== $template;
    }
    
    public function admin_form(&$qa_content)
    {                       
        $ok = null;
        if (qa_clicked('q2apro_onsitenotifications_save')) {
            qa_opt('q2apro_onsitenotifications_enabled', (bool) qa_post_text('q2apro_onsitenotifications_enabled'));
            qa_opt('q2apro_onsitenotifications_nill', qa_post_text('q2apro_onsitenotifications_nill'));
            qa_opt('q2apro_onsitenotifications_maxevshow', (int) qa_post_text('q2apro_onsitenotifications_maxevshow'));
            qa_opt('q2apro_onsitenotifications_newwindow', (bool) qa_post_text('q2apro_onsitenotifications_newwindow'));
            qa_opt('q2apro_onsitenotifications_rtl', (bool) qa_post_text('q2apro_onsitenotifications_rtl'));
            qa_opt('q2apro_onsitenotifications_votes', (bool) qa_post_text('q2apro_onsitenotifications_votes'));
            qa_opt('q2apro_onsitenotifications_bestanswers', (bool) qa_post_text('q2apro_onsitenotifications_bestanswers'));
            qa_opt('q2apro_onsitenotifications_flags', (bool) qa_post_text('q2apro_onsitenotifications_flags'));
            
            $ok = qa_lang('admin/options_saved');
        }
        
        $fields = $this->prepareAdminForm();
       
       return [
           'ok' => ($ok && !isset($error)) ? $ok : null,
           'fields' => $fields,
           'buttons' => [
               [
                   'label' => qa_lang_html('main/save_button'),
                   'tags' => 'name="q2apro_onsitenotifications_save"'
               ],
           ],
       ];
    }

    private function prepareAdminForm(): array
    {
        return [
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/enable_plugin'),
                'tags' => 'name="q2apro_onsitenotifications_enabled"',
                'value' => qa_opt('q2apro_onsitenotifications_enabled')
            ],
            [
                'type' => 'input',
                'label' => qa_lang('q2apro_onsitenotifications_lang/no_notifications_label'),
                'tags' => 'name="q2apro_onsitenotifications_nill" style="width:100px;"',
                'value' => qa_opt('q2apro_onsitenotifications_nill')
            ],
            [
                'type' => 'input',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_maxeventsshow'),
                'tags' => 'name="q2apro_onsitenotifications_maxevshow" style="width:100px;"',
                'value' => qa_opt('q2apro_onsitenotifications_maxevshow')
            ],
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_newwindow'),
                'tags' => 'name="q2apro_onsitenotifications_newwindow"',
                'value' => qa_opt('q2apro_onsitenotifications_newwindow')
            ],
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_rtl'),
                'tags' => 'name="q2apro_onsitenotifications_rtl"',
                'value' => qa_opt('q2apro_onsitenotifications_rtl')
            ],
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_flags'),
                'tags' => 'name="q2apro_onsitenotifications_flags"',
                'value' => qa_opt('q2apro_onsitenotifications_flags')
            ],
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_votes'),
                'tags' => 'name="q2apro_onsitenotifications_votes"',
                'value' => qa_opt('q2apro_onsitenotifications_votes')
            ],
            [
                'type' => 'checkbox',
                'label' => qa_lang('q2apro_onsitenotifications_lang/admin_bestanswers'),
                'tags' => 'name="q2apro_onsitenotifications_bestanswers"',
                'value' => qa_opt('q2apro_onsitenotifications_bestanswers')
            ],
            [
                'type' => 'static',
                'note' => '<span style="font-size:12px;color:#789;">' . strtr(
                    qa_lang('q2apro_onsitenotifications_lang/contact'),
                    [ 
                        '^1' => '<a target="_blank" href="http://www.q2apro.com/plugins/on-site-notifications">',
                        '^2' => '</a>'
                    ]
                ) . '</span>'
            ]
        ];
    }
}
