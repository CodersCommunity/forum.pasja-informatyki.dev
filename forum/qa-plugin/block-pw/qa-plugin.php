<?php
/*
    Plugin Name: Block pm
    Plugin URI: https://forum.pasja-informatyki.pl
    Plugin Description: Very powerful and useful plugin for blocking pm from unpleasant users :)
    Plugin Version: 1.0
    Plugin Date: 2020-03-16
    Plugin Author: https://forum.pasja-informatyki.pl/user/Mariusz08
    Plugin License: GPLv3
    Plugin Minimum Question2Answer Version: 1.7
    Plugin Update Check URI: 
    Minimum PHP version: 7.1

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    More about this license: http://www.gnu.org/licenses/gpl.html
    
*/

declare(strict_types=1);

if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

qa_register_plugin_phrases('block-pm-lang-*.php', 'block_pm');
qa_register_plugin_module('page', 'block-pm-page.php', 'block_pm_page', 'Block pm page');
qa_register_plugin_module('page', 'block-pm-user-list-page.php', 'block_pm_user_list_page', 'Block pm user list page');
qa_register_plugin_overrides('block-pm-override.php');
qa_register_plugin_layer('block-pm-layer.php', 'Block pm layer');
qa_register_plugin_module('module', 'block-pm-admin.php', 'block_pm_admin', 'Block pm admin');

/**
 * Parameters order does not matter.
 */
function ifUserIsBlocked(int $fromUserId, int $toUserId): bool
{
    $blockedPrivateMessages = qa_db_query_sub('SELECT `from_user_id`, `to_user_id` FROM ^blockedpw WHERE (from_user_id = # AND to_user_id = #) OR (from_user_id = # AND to_user_id = #)', $fromUserId, $toUserId, $toUserId, $fromUserId);
    $allowedPrivateMessages = qa_opt('allow_private_messages');
    $blockedPrivateMessageBool = 0 !== $blockedPrivateMessages->num_rows;
    $toUserDb = qa_db_select_with_pending(qa_db_user_account_selectspec($toUserId, false));

    if (($blockedPrivateMessageBool || !$allowedPrivateMessages) && $toUserDb['level'] < QA_USER_LEVEL_EDITOR) {
        return true;
    }
    
    return false;
}
