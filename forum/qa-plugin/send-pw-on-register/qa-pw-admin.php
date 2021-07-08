<?php

class qa_pw_admin
{
    public function admin_form(&$qa_content)
    {
        $isSaved = false;
        $info = null;
		
        if (qa_clicked('sendPwMessageOnRegister_save')) {
            $canSave = (bool) qa_post_text('enablePlugin');
            qa_opt('sendPwMessageOnRegister_messageContent', qa_post_text('messageContent'));
            
            if (empty(qa_post_text('messageContent'))) {
                $canSave = false;
                $info['content'] = 'Empty message content';
            }
			
            require_once QA_INCLUDE_DIR . 'db/users.php';
			
            if ([] === qa_db_user_get_userid_handles(qa_post_text('botId'))) {
                $canSave = false;
                $info['botId'] = 'Invalid bot id';
            } else {
                qa_opt('sendPwMessageOnRegister_botId', qa_post_text('botId'));
            }

            if ($canSave) {
                qa_opt('sendPwMessageOnRegister_enabled', qa_post_text('enablePlugin'));
                $isSaved = true;
            }
        }
        
        return $this->prepareAdminForm($isSaved, $info);
    }
    
    private function prepareAdminForm($isSaved, $info)
    {
        return [
            'ok' => $isSaved ? 'Settings saved' : '',
            'fields' => [
                [
                    'label' => 'Enable plugin',
                    'tags' => 'name="enablePlugin"',
                    'value' => qa_opt('sendPwMessageOnRegister_enabled'),
                    'type' => 'checkbox'
                ],
                [
                    'label' => '<span style="color: red; font-weight: bold;">DANGER ZONE!</span> Bot for sending messages (id): <span style="color: red; font-weight: bold;">DANGER ZONE!</span>',
                    'tags' => 'name="botId" type="number"',
                    'value' => qa_opt('sendPwMessageOnRegister_botId'),
                    'error' => empty($info['botId']) ? '' : $info['botId']
                ],
                [
                    'label' => 'Message content:',
                    'tags' => 'name="messageContent"',
                    'value' => qa_html(qa_opt('sendPwMessageOnRegister_messageContent')),
                    'type' => 'textarea',
                    'rows' => 20,
                    'error' => empty($info['content']) ? '' : $info['content']
                ]
            ],
            'buttons' => [
                [
                    'label' => 'Save Changes',
                    'tags' => 'name="sendPwMessageOnRegister_save"',
                ]
            ]
        ];        
    }
}
