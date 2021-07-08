<?php
declare(strict_types=1);

require_once QA_INCLUDE_DIR . 'db/users.php';

class block_pm_page
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
        $this->requestParts = explode('/', $request);

        return $this->requestParts[0] === 'message';
    }

    public function process_request(): ?array
    {
        // logged in user id
        $loggedIn = qa_get_logged_in_userid();
        // to message user id
        $user = $this->getUser();
        
        if (!$this->userExists($user)) {
            return include QA_INCLUDE_DIR.'qa-page-not-found.php';
        }
        
        if (empty($loggedIn)) {
            $qa_content = qa_content_prepare();
            $qa_content['error'] = qa_lang_html('block_pm/logged_in');
            
            return $qa_content;
        }
        
        $qa_content = require QA_INCLUDE_DIR . '/pages/message.php';

        if (ifUserIsBlocked($loggedIn, $user) && qa_get_logged_in_level() === QA_USER_LEVEL_BASIC) {
            $qa_content['custom'] = qa_lang_html('block_pm/cannot_send');
            unset($qa_content['form_message']);
        }

        return $qa_content;
    }
    
    private function getUser(): ?array
    {
        if (isset($this->requestParts[1])) {
            $user = qa_db_user_find_by_handle($this->requestParts[1]);
        } else {
            $user = null;
        }
        
        return $user;
    }
    
    private function userExists($user): bool
    {
        return !empty($user);
    }
}
