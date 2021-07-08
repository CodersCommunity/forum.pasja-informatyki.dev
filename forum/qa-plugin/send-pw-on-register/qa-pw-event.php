<?php

class qa_pw_event
{
    public function process_event($event, $userId, $handle, $cookieId, $params)
    {
        if (qa_opt('sendPwMessageOnRegister_enabled') && 'u_register' === $event) {
            require_once QA_INCLUDE_DIR . 'db/messages.php';
            require_once QA_INCLUDE_DIR . 'db/selects.php';
            require_once QA_INCLUDE_DIR . 'db/users.php';
                
            $botId = qa_opt('sendPwMessageOnRegister_botId');
            $messageContent = qa_opt('sendPwMessageOnRegister_messageContent');
            
            if (empty($botId) || empty($messageContent) || [] === qa_db_user_get_userid_handles(qa_post_text('botId'))) {
                return;
            }
			
            $messsageId = qa_db_message_create(
                $botId,
                $userId,
                $messageContent,
                '',
                false
            );
                
            $fromUserHandle = qa_db_query_sub('SELECT `handle` FROM ^users WHERE `userid` = #', $botId);
            
            qa_report_event('u_message', $botId, $fromUserHandle, qa_cookie_get(), [
                'userid' => $userId,
                'handle' => $handle,
                'messageid' => (int) $messageId,
                'message' => $messageContent
            ]);
        }
    }
}
