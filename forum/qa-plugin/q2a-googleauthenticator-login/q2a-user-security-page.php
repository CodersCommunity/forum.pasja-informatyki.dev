<?php
declare(strict_types=1);

require_once QA_INCLUDE_DIR . 'db/users.php';

class user_security_page
{
    private $directory;
    private $urltoroot;
    private $requestParts;

    public function load_module(string $directory, string $urltoroot): void
    {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    public function match_request(string $request): bool
    {
        $this->requestParts = $request;

        return $request === 'account/security';
    }

    public function process_request(): ?array
    {
        $qa_content = qa_content_prepare();
        $qa_content['title'] = 'Ustawienia zabezpieczeń konta';

        // logged in user id
        $loggedIn = (int) qa_get_logged_in_userid();

        if (empty($loggedIn)) {
            $qa_content['error'] = qa_lang_html('block_pm/logged_in');

            return $qa_content;
        }

        $twoFactorIsEnabled = qa_db_read_all_assoc(
            qa_db_query_sub('SELECT 2fa_enabled FROM ^users WHERE handle = $', qa_get_logged_in_handle())
        )[0]['2fa_enabled'];

        if (false === (bool) $twoFactorIsEnabled && null !== qa_get('restore') && 1 === (int) qa_get('restore')) {
            $qa_content['error'] = qa_lang('plugin_2fa/recover_code_page_info');

            return $qa_content;
        }

        $qa_content['navigation']['sub'] = qa_user_sub_navigation($this->getUsername($loggedIn), '', false);

        return $qa_content;
    }

    private function getUsername(int $userId)
    {
        return qa_db_read_one_value(qa_db_query_sub(
            'SELECT handle FROM ^users WHERE userid=#',
            $userId
        ));
    }
}
