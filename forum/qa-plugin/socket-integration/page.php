<?php

class socket_integration_page
{
    public function match_request($request)
    {
        return $request === 'user-id';
    }

    public function process_request()
    {
        if (($_SERVER['HTTP_TOKEN'] ?? '') !== QA_WS_TOKEN) {
            http_response_code(401);
            return;
        }

        $id = qa_get_logged_in_userid();
        if ($id === null) {
            http_response_code(403);
            return;
        }

        echo $id;
    }
}
