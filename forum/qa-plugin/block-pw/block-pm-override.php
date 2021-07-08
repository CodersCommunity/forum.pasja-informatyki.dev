<?php

function qa_get_request_content(): ?array
{
    $requestparts = qa_request_parts();
    $firstlower   = strtolower($requestparts[0]);
    $routing      = qa_page_routing();
    $page         = $firstlower . '/';

    if (isset($routing[$page]) && $requestparts[0] === 'message') {
        qa_set_template($firstlower !== '' ? $firstlower : 'qa');
        $qa_content = require QA_INCLUDE_DIR . 'pages/default.php';

        if (isset($qa_content)) {
            qa_set_form_security_key();
        }

        return $qa_content;
    }

    return qa_get_request_content_base();
}

function qa_user_permit_error(string $permitoption=null, string $limitaction=null, string $userlevel=null, bool $checkblocks=true)
{
    if (qa_post_text('domessage')) {
        $toUserId = qa_request_parts()[1] ?? '';
        $loggedIn = qa_get_logged_in_userid();
        
        if (empty($toUserId)) {
            return;
        }
        
        if (ifUserIsBlocked($loggedIn, $toUserId)) {
            return 'userblock'; // user is blocked so return missing permissions error
        }
    }
}
