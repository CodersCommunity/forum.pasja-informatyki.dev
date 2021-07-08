<?php
declare(strict_types=1);

class block_pm_user_list_page
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
        return $request === 'blocked-users';
    }

    public function process_request(): ?array
    {
        $qa_content = qa_content_prepare();
        $qa_content['title'] = qa_lang_html('block_pm/blocked_list_title');

        // logged in user id
        $loggedIn = qa_get_logged_in_userid();
        
        if (empty($loggedIn)) {
            $qa_content['error'] = qa_lang_html('block_pm/logged_in');
            
            return $qa_content;
        }
        
        if (qa_post_text('userid')) {
            qa_db_query_sub('DELETE FROM `^blockedpw` WHERE `from_user_id` = # AND `to_user_id` = #', $loggedIn, (int) qa_post_text('userid'));
        }
        
        $this->prepareBlockedList($loggedIn, $qa_content);

        $qa_content['navigation']['sub'] = qa_user_sub_navigation(qa_get_logged_in_handle(), 'blocklist', true);

        return $qa_content;
    }
    
    private function prepareBlockedList(string $loggedIn, array &$qa_content): void
    {
        $blockedUsers = qa_db_select_with_pending([
            'columns' => ['^users.userid', '^users.handle',  '^users.flags', '^users.email', 'avatarblobid' => 'BINARY avatarblobid', '^users.avatarwidth', '^users.avatarheight'],
            'source' => '^users JOIN (SELECT to_user_id FROM ^blockedpw WHERE from_user_id = #) s ON ^users.userid=s.to_user_id',
            'arguments' => [$loggedIn],
            'arraykey' => 'userid',
        ]);

        $pageContent = '';

        if (0 === count($blockedUsers)) {
            $pageContent = qa_lang_html('block_pm/empty_blocklist');
        } else {
            $qa_content['ranking'] = [
                'items' => [],
                'rows' => 2,
                'type' => 'users'
            ];

            $userHtml = qa_userids_handles_html($blockedUsers);

            foreach ($blockedUsers as $user) {
                $avatar = qa_get_user_avatar_html($user['flags'], $user['email'], $user['handle'], $user['avatarblobid'], $user['avatarwidth'], $user['avatarheight'], qa_opt('avatar_users_size'), true);
                $label = $user['handle'];
                $points = qa_db_query_sub('SELECT `points` FROM ^userpoints WHERE userid = #', $user['userid']);
                $pointsArray = $points->fetch_assoc();

                $qa_content['ranking']['items'][] = [
                    'avatar' => $avatar,
                    'label' => $userHtml[$user['userid']],
                    'score' => '<form method="post" style="margin: 0; padding: 0;"><input type="hidden" style="display: none;" name="userid" value="' . $user['userid'] . '"><input type="submit" style="margin: 0; cursor: pointer; background-color: rgba(0,0,0,0); border: none; color: white;" value="' . qa_lang_html('block_pm/block') . '"></form>',
                    'raw' => $label,
                ];
            }

            $qa_content['custom_head'] = '<style>.qam-user-score-icon::before { display: none; } .qam-user-score-icon { padding: 2px 6px 2px 6px; }</style>';            
        }

        if ('' !== $pageContent) {
            $qa_content['custom'] = $pageContent;
        }
        
    }
}
