<?php

class socket_integration_event
{
    public function process_event($event, $userid, $handle, $cookieid, $params)
    {
        if (empty(QA_WS_URL) || empty(QA_WS_TOKEN)) {
            return;
        }

        switch ($event) {
            case 'q_post':
            case 'q_edit':
            case 'q_hide':
            case 'q_reshow':
            case 'q_close':
            case 'q_reopen':
            case 'q_move':
                $this->send_to_websocket($event, ['userId' => (int)$userid, 'questionId' => (int)$params['postid']]);
                break;
            case 'a_edit':
            case 'a_hide':
            case 'a_reshow':
            case 'a_select':
            case 'a_unselect':
                $this->send_to_websocket($event, ['userId' => (int)$userid, 'questionId' => (int)$params['parentid']]);
                break;
            case 'c_edit':
            case 'c_hide':
            case 'c_reshow':
            case 'a_to_c':
                $this->send_to_websocket($event, [
                    'userId' => (int)$userid,
                    'questionId' => (int)$params['questionid']
                ]);
                break;
            case 'a_post':
            case 'c_post':
                $questionParams = $params['question'] ?? $params['parent'];
                $slug = explode('/', qa_q_request($questionParams['postid'], $questionParams['title']))[1] ?? null;

                $this->send_to_websocket($event, [
                    'userId' => (int)$userid,
                    'questionId' => (int)$questionParams['postid'],
                    'questionSlug' => $slug,
                    'postId' => (int)$params['postid'],
                    'url' => qa_q_path(
                        $questionParams['postid'],
                        $questionParams['title'],
                        true,
                        isset($params['question']) ? 'C' : 'A',
                        $params['postid']
                    )
                ], $this->get_recipients($event, $params));
                break;
        }
    }

    private function get_recipients($event, $params = [])
    {
        $users = [];
        switch ($event) {
            case 'a_post':
                if (!empty($params['parent']['userid'])) {
                    $users = [$params['parent']['userid']];
                }
                break;
            case 'c_post':
                $users = qa_db_read_all_values(qa_db_query_sub(
                    'SELECT DISTINCT userid FROM `^posts` WHERE `parentid` = # AND `type` = "C" AND `userid` IS NOT NULL',
                    $params['parentid']
                ));
                if (!empty($params['parent']['userid'])) {
                    $users[] = $params['parent']['userid'];
                }
                break;
        }

        return array_values(array_filter(array_map('intval', $users), function ($id) {
            return $id !== (int)qa_get_logged_in_userid();
        }));
    }

    private function send_to_websocket($action, $data = [], $recipientIds = [])
    {
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" . "token: " . QA_WS_TOKEN . "\r\n",
                'method' => 'POST',
                'content' => json_encode([
                    'action' => $action,
                    'recipientIds' => $recipientIds,
                    'data' => $data,
                ], JSON_UNESCAPED_UNICODE)
            ]
        ];

        $context = stream_context_create($options);
        file_get_contents(QA_WS_URL, false, $context);
    }
}
