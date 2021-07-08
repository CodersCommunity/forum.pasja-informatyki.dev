<?php

require_once GOOGLEAUTHENTICATOR_BASIC_PATH . '/src/GoogleAuthenticator.php';
require_once QA_INCLUDE_DIR . 'app/users.php';
require_once QA_INCLUDE_DIR . 'db/users.php';

class q2a_googleauthenticator_page_login
{
    public function match_request($request): bool
    {
        return '2fa-auth' === $request;
    }

    public function process_request()
    {
        $login = qa_get('handle');
        $loginCode = qa_get('login_code');
        $code = qa_post_text('2fa_code');

        if (empty($loginCode) || empty($login)) {
            qa_redirect('');
        }

        if (empty($code)) {
            $content = qa_content_prepare();
            $content['title'] = qa_lang('plugin_2fa/title');
            $content['form']  = $this->prepareTwoFactorAuthForm();

            return $content;
        }

        $authenticationData = qa_db_read_all_assoc(
            qa_db_query_sub('SELECT 2fa_login_code, 2fa_secret FROM ^users WHERE handle = $', $login)
        )[0];

        $secret = $authenticationData['2fa_secret'];
        $init = new GoogleAuthenticator($secret);

        if (!isset($secret) && empty($secret)) {
            qa_fatal_error(qa_lang('plugin_2fa/secret_error'));
        }


        if (($loginCode === $authenticationData['2fa_login_code']) && $init->verifyCode($code)) {
            $userId = qa_db_read_all_assoc(qa_db_query_sub('SELECT userid FROM ^users WHERE handle = $', $login))[0]['userid'];
            $this->login($userId, $login, (bool) qa_get('remember'), qa_get('redirect'));

            qa_db_query_sub('UPDATE ^users SET 2fa_login_code=null, 2fa_login_code_created=null WHERE userid=#', $userId);
        }

        $recoveryCode = qa_db_read_all_assoc(
            qa_db_query_sub('SELECT 2fa_recovery_code FROM ^users WHERE handle = $', $login)
        )[0]['2fa_recovery_code'];

        if (($code === $recoveryCode) && ($loginCode === $authenticationData['2fa_login_code'])) {
            qa_db_query_sub('UPDATE ^users SET 2fa_recovery_code = 0, 2fa_login_code = 0, 2fa_login_code_created = 0 WHERE handle = $', $login);
            $userId = qa_db_read_all_assoc(qa_db_query_sub('SELECT userid FROM ^users WHERE handle = $', $login))[0]['userid'];
            $this->login($userId, $login, (bool) qa_get('remember'), 'account/security?restore=1');
        }

        $content = qa_content_prepare();
        $content['title'] = qa_lang('plugin_2fa/title');
        $content['form']  = $this->prepareTwoFactorAuthForm();
        $content['error'] = qa_lang('plugin_2fa/invalid_code');

        return $content;
    }

    private function login($userid, $login, $remember = false, $redirectPath = null): void
    {
        qa_set_session_user($userid, '2fa');

        require_once QA_INCLUDE_DIR . 'db/selects.php';

        $userinfo = qa_db_single_select(qa_db_user_account_selectspec($userid, true));
        if (empty($userinfo['sessioncode']) || ('2fa' !== $userinfo['sessionsource'])) {
            $sessioncode = qa_db_user_rand_sessioncode();
            qa_db_user_set($userid, 'sessioncode', $sessioncode);
            qa_db_user_set($userid, 'sessionsource', '2fa');
        } else {
            $sessioncode = $userinfo['sessioncode'];
        }

        qa_db_user_logged_in($userid, qa_remote_ip_address());
        qa_set_session_cookie($login, $sessioncode, $remember);
        qa_report_event('u_login', $userid, $userinfo['handle'], qa_cookie_get());

        $topath = qa_get('to') ?? $redirectPath;

        if (isset($topath)) {
            qa_redirect_raw(qa_path_to_root() . $topath);
        }

        qa_redirect('');
    }

    private function prepareTwoFactorAuthForm(): array
    {
        return [
            'tags'    => 'method="post" action="' . qa_self_html() . '"',
            'style'   => 'wide',
            'title'   => qa_lang('plugin_2fa/title'),
            'fields'  => [
                'old' => [
                    'label' => qa_lang('plugin_2fa/code_input'),
                    'tags'  => 'name="2fa_code"',
                    'type'  => 'input'
                ],
            ],
            'buttons' => [
                'send' => [
                    'label' => qa_lang('plugin_2fa/send')
                ]
            ],
            'hidden'  => [
                'code'     => qa_get_form_security_code('2faform'),
            ]
        ];
    }
}
