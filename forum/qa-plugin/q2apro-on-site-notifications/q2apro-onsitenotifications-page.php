<?php
/*
    Plugin Name: On-Site-Notifications
    Plugin URI: http://www.q2apro.com/plugins/on-site-notifications
    Plugin Description: Facebook-like / Stackoverflow-like notifications on your question2answer forum that can replace all email-notifications.
    Plugin Version: → see qa-plugin.php
    Plugin Date: → see qa-plugin.php
    Plugin Author: q2apro.com
    Plugin Author URI: http://www.q2apro.com/
    Plugin License: GPLv3
    Plugin Minimum Question2Answer Version: → see qa-plugin.php
    Plugin Update Check URI: https://raw.githubusercontent.com/q2apro/q2apro-on-site-notifications/master/qa-plugin.php

    This program is free software. You can redistribute and modify it
    under the terms of the GNU General Public License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    More about this license: http://www.gnu.org/licenses/gpl.html

*/

class q2apro_onsitenotifications_page
{
    public $directory;
    public $urltoroot;
    
    public function load_module($directory, $urltoroot)
    {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    public function suggest_requests(): array
    {
        return [
            [
                'title' => 'On-Site-Notifications Page', // title of page
                'request' => 'eventnotify', // request name
                'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
            ],
        ];
    }

    public function match_request($request): bool
    {
        if ('eventnotify' === $request) {
            return true;
        }
        return false;
    }

    public function process_request($request)
    {
        // we received post data, it is the ajax call!
        $transferString = qa_post_text('ajax');
        if ($transferString !== null) {
            
            $userId = qa_get_logged_in_userid();
            if (empty($userId)) {
                echo 'Userid is empty!';
                return;
            }

            $notifyBoxEvents = '';
            
            if (isset($userId) && 'receiveNotify' === $transferString) {
                $lastVisit = qa_db_read_one_value(
                    qa_db_query_sub(
                        'SELECT UNIX_TIMESTAMP(meta_value) FROM ^usermeta WHERE user_id=# AND meta_key="visited_profile"',
                        $userId
                    ), true
                );

                $maxEvents = qa_opt('q2apro_onsitenotifications_maxevshow');
                
                $eventQuery = qa_db_query_sub(
                    'SELECT
                        e.event,
                        e.userid,
                        BINARY e.params as params,
                        UNIX_TIMESTAMP(e.datetime) AS datetime
                    FROM
                        ^eventlog AS e
                    WHERE
                        FROM_UNIXTIME(#) <= datetime
                        AND
                        (e.userid=# AND e.event LIKE "in_%")
                        OR ((e.event LIKE "u_message" OR e.event LIKE "u_wall_post") AND e.params LIKE "userid=#\t%")
                    ORDER BY datetime DESC
                    LIMIT #', // Limit
                    qa_opt('q2apro_onsitenotifications_maxage'), // events of last x days
                    $userId,
                    $userId,
                    $maxEvents
                );

                $events = [];
                $postIds = [];
                $count = 0;

                while (null !== ($event = qa_db_read_one_assoc($eventQuery, true))) {

                    if (1 === preg_match('/postid=([0-9]+)/', $event['params'], $m)) {
                        $event['postid'] = (int) $m[1];
                        $postIds[] = (int) $m[1];
                        $events[$m[1] . '_' . $count++] = $event;
                    }

                    if ('u_message' === $event['event']) {
                        // example of $event['params']: userid=1  handle=admin  messageid=4  message=hi admin, how are you?

                        $string = $event['params'];
                        
                        if(1 === preg_match('/messageid=([0-9]+)/', $string, $m)) {
                            $event['messageid'] = (int) $m[1];
                        }

                        require_once QA_INCLUDE_DIR.'qa-app-posts.php';
                        $event['handle'] = qa_post_userid_to_handle($event['userid']);
                        
                        $event['message'] = substr($string, strpos($string, 'message=') + 8, strlen($string) - strpos($string,'message=') + 8);
                        $events[$m[1] . '_' . $count++] = $event;
                    } else if ($event['event']=='u_wall_post') {
                        // example of $event['params']: userid=1    handle=admin    messageid=8 content=hi admin!   format= text=hi admin!
                        $string = $event['params'];
                        
                        if (1 === preg_match('/messageid=([0-9]+)/', $string, $m)) {
                            $event['messageid'] = (int) $m[1];
                        }

                        require_once QA_INCLUDE_DIR.'qa-app-posts.php';
                        
                        $event['handle'] = qa_post_userid_to_handle($event['userid']);
                        $event['message'] = substr($string, strpos($string, 'text=') + 5, strlen($string) - strpos($string, 'text=') + 5);
                        $events[$m[1] . '_' . $count++] = $event;
                    }
                }

                $posts = null;
                if(!empty($postIds)) {
                    $postQuery = qa_db_read_all_assoc(
                        qa_db_query_sub(
                            'SELECT postid, type, parentid, BINARY title as title FROM ^posts
                                WHERE postid IN (' . implode(',', $postIds) . ')'
                        )
                    );
                    foreach($postQuery as $post) {
                        $posts[(string) $post['postid']] = $post;
                    }
                }

                $notifyBoxEvents = '<div id="nfyWrap" class="nfyWrap">
                <div class="nfyTop">' .
                qa_lang('q2apro_onsitenotifications_lang/my_notifications') .
                ' <a id="nfyReadClose">' .
                qa_lang('q2apro_onsitenotifications_lang/close') .
                ' | × |</a> </div>
                <div class="nfyContainer">
                    <div id="nfyContainerInbox">
                ';

               foreach($events as $postIdString => $event) {
                   $type = $event['event'];
                   if ('u_message' === $type) {
                        $eventName = qa_lang('q2apro_onsitenotifications_lang/you_received') . ' ';
                        $itemIcon = '<div class="nicon nmessage"></div>';
                        $activityUrl = qa_path_absolute('message') . '/' . $event['handle'];
                        $linkTitle = qa_lang('q2apro_onsitenotifications_lang/message_from') . ' ' . $event['handle'];
                    } else if('u_wall_post' === $type) {
                        $eventName = qa_lang('q2apro_onsitenotifications_lang/you_received') . ' ';
                        $itemIcon = '<div class="nicon nwallpost"></div>';
                        
                        require_once QA_INCLUDE_DIR.'qa-app-posts.php';
                        $userHandle = qa_post_userid_to_handle($userId);
                        
                        $activityUrl = qa_path_absolute('user') . '/' . $userHandle . '/wall';
                        $linkTitle = qa_lang('q2apro_onsitenotifications_lang/wallpost_from') . ' ' . $event['handle'];
                    } else {
                        $postId = preg_replace('/_.*/','', $postIdString);
                        
                        $post = @$posts[$postId];
                        $params = [];
                        
                        $paramsArray = explode("\t", $event['params']);
                        foreach ($paramsArray as $param) {
                            $paramArray = explode('=', $param);
                            if (isset($paramArray[1])) {
                                $params[$paramArray[0]] = $paramArray[1];
                            }   else {
                                $params[$param] = $param;
                            }
                        }

                        $link = '';
                        $linkTitle = '';
                        $activityUrl = '';
                        
                        if (isset($post) && 0 !== strpos($event['event'], 'q_') && 0 !== strpos($event['event'],'in_q_')) {
                            if (!isset($params['parentid'])) {
                               $params['parentid'] = $post['parentid'];
                            }

                            if (!isset($params['postid'])) {
                               $params['postid'] = $post['parentid'];
                            }

                            $parent = qa_db_select_with_pending(
                                qa_db_full_post_selectspec(
                                    $userId,
                                    $params['parentid']
                                )
                            );

                            if ($parent['type'] == 'A') {
                                $parent = qa_db_select_with_pending(
                                    qa_db_full_post_selectspec(
                                        $userId,
                                        $parent['parentid']
                                    )
                                );
                            }

                            $anchor = qa_anchor((0 === strpos($event['event'], 'a_') || 0 === strpos($event['event'], 'in_a_') ? 'A' : 'C'), $params['postid']);
                            $activityUrl = qa_path_absolute(qa_q_request($parent['postid'], $parent['title']), null, $anchor);
                            $linkTitle = $parent['title'];
                            $link = '<a target="_blank" href="' . $activityUrl . '">' . $parent['title'] . '</a>';
                        } else if (isset($post)) { // question
                            if (!isset($params['title'])) {
                                $params['title'] = $posts[$params['postid']]['title'];
                            }

                            if (null !== $params['title']) {
                                $linkTitle = qa_db_read_one_value(qa_db_query_sub("SELECT title FROM `^posts` WHERE `postid` = " . $params['postid'] . " LIMIT 1"), true);
                                if (!isset($linkTitle)) {
                                    $linkTitle = '';
                                }

                                $activityUrl = qa_path_absolute(qa_q_request($params['postid'], $linkTitle), null, null);
                                $link = '<a target="_blank" href="' . $activityUrl . '">' . $linkTitle . '</a>';
                            }
                        }

                        $eventName = '';
                        $itemIcon = '';

                        $eventTime = $event['datetime'];
                        $whenHtml = qa_html(qa_time_to_string(qa_opt('db_time') - $eventTime));
                        $when = qa_lang_html_sub('main/x_ago', $whenHtml);
                        $cssNewEv = '';
                    
                        if($eventTime > $lastVisit) {
                            $cssNewEv = '-new';
                        }
        
                        if (in_array($type, ['in_c_question', 'in_c_answer', 'in_c_comment'])) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_comment');
                            $itemIcon = '<div class="nicon ncomment"></div>';
                        } else if (in_array($type, ['in_q_vote_up', 'in_a_vote_up'])) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_upvote');
                            $itemIcon = '<div class="nicon nvoteup"></div>';
                        } else if (in_array($type, ['in_q_vote_down', 'in_a_vote_down'])) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_downvote');
                            $itemIcon = '<div class="nicon nvotedown"></div>';
                        } else if ('in_a_question' === $type) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_answer');
                            $itemIcon = '<div class="nicon nanswer"></div>';
                        } else if ('in_a_select' === $type) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_bestanswer');
                            $itemIcon = '<div class="nicon nbestanswer"></div>';
                        } else if ('in_a_unselect' === $type) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_unbestanswer');
                            $itemIcon = '<div class="nicon nunbestanswer"></div>';
                        } else if (in_array($type, ['in_q_flag', 'in_a_flag', 'in_c_flag'])) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_flag');
                            $itemIcon = '<div class="nicon nflag"></div>';
                        } else if (in_array($type, ['in_q_unflag', 'in_a_unflag', 'in_c_unflag'])) {
                            $eventName = qa_lang('q2apro_onsitenotifications_lang/in_unflag');
                            $itemIcon = '<div class="nicon nunflag"></div>';
                        } else {
                            // ignore events [u_edit, u_level, a_post, c_post, u_block, u_unblock]
                            continue;
                        }
                    }


                    // If activity url is empty, activity does not exist (probably removed?) and we're gonna delete notification
                    if ('' === $activityUrl) {
                        // records don't have unique id, so delete record with query based on known params

                        qa_db_query_sub(
                            'DELETE FROM ^eventlog WHERE userid = # AND params = $ AND event = $ AND datetime = $',
                            $event['userid'],
                            $event['params'],
                            $event['event'],
                            $event['datetime']
                        );

                        continue;
                    } else {
                        $notifyBoxEvents .= '<div class="itemBox' . $cssNewEv . '">
                            ' . $itemIcon . '
                            <div class="nfyItemLine">
                                <p class="nfyWhat">' . $eventName . '
                                    <a ' . ('u_message' === $type || 'u_wall_post' === $type ? 'title="' . htmlspecialchars($event['message'], ENT_QUOTES, "UTF-8") . '" ' : '') . ' href="' . $activityUrl . '"' . (qa_opt('q2apro_onsitenotifications_newwindow') ? ' target="_blank"' : '') . '>' . htmlspecialchars($linkTitle, ENT_QUOTES, "UTF-8") . '</a>
                                </p>
                                <p class="nfyTime">' . $when . '</p>
                            </div>
                        </div>';
                    }
                }

                 $notifyBoxEvents .= '</div>
                    </div>
                    <div class="nfyFooter">
                        <a href="http://www.q2apro.com/">by q2apro.com</a>
                    </div>
                </div>
                ';
                header('Access-Control-Allow-Origin: ' . qa_path(null));
                echo $notifyBoxEvents;
                
                // update database entry so that all user notifications are seen as read
                qa_db_query_sub(
                    'INSERT INTO ^usermeta (user_id,meta_key,meta_value) VALUES(#,$,NOW()) ON DUPLICATE KEY UPDATE meta_value=NOW()',
                    $userId, 'visited_profile'
                );
                exit();
            } else {
                echo 'Unexpected problem detected! No userid, no transfer string.';
                exit();
            }
        }
    }
}
