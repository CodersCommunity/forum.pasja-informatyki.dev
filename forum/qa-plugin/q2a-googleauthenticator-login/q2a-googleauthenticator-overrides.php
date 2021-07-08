<?php

function qa_set_logged_in_user($userid, $handle = '', $remember = false, $source = null)
{
    if (!isset($userid)) {
        qa_set_logged_in_user_base($userid, $handle, $remember, $source);
    }

    require_once QA_INCLUDE_DIR . 'db/selects.php';

    $result = qa_db_read_all_assoc(
        qa_db_query_sub('SELECT 2fa_enabled FROM ^users WHERE userid = #', $userid)
    );

    if (isset($result[0]['2fa_enabled']) && (bool) $result[0]['2fa_enabled']) {
        if (QA_FINAL_EXTERNAL_USERS) {
            qa_fatal_error('User login is handled by external code');
        }

        if (qa_is_logged_in()) {
            qa_redirect('');
        }

        $inpassword = qa_post_text('password');
        $userinfo = qa_db_single_select(qa_db_user_account_selectspec($userid, true));
        $inremember = qa_post_text('remember');
        $topath = qa_get('to');

        if (strtolower(qa_db_calc_passcheck($inpassword, $userinfo['passsalt'])) === strtolower($userinfo['passcheck'])) {
            $factorCode = qa_random_alphanum(32);
            qa_db_query_sub('UPDATE ^users SET 2fa_login_code=$, 2fa_login_code_created=CURRENT_TIMESTAMP WHERE userid=#', $factorCode, $userid);

            qa_redirect('2fa-auth', ['redirect' => $topath, 'remember' => $inremember, 'handle' => $handle, 'login_code' => $factorCode]);
        }

        qa_set_logged_in_user_base($userid, $handle, $remember, $source);
    }

    qa_set_logged_in_user_base($userid, $handle, $remember, $source);
}