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

/* The following code originates from q2a plugin "History" by NoahY and has been modified by q2apro.com
 * It is licensed under GPLv3 http://www.gnu.org/licenses/gpl.html
 * Link to plugin file: https://github.com/NoahY/q2a-history/blob/master/qa-history-check.php
 */

require_once QA_INCLUDE_DIR.'qa-app-posts.php';

class q2apro_history_check
{

    public function process_event($event, $userId, $handle, $cookieId, $params)
    {
        
        if (!qa_opt('event_logger_to_database')) {
            return;
        }
        
        // @TODO: Add event dispatching to `q2a-comment-voting-master` plugin and handle it.


        // We can ignore events ['a_vote_nil', 'q_vote_nil']
        // At the moment I think, it cannot be dispatched
        // More info: https://github.com/CodersCommunity/forum.pasja-informatyki.local/blob/1b0c6d3a6b5ebe5a60df46fc6162273d5a774faa/forum/qa-include/app/votes.php#L128
        $events = [
            'a_select',
            'a_unselect',

            'q_vote_up',
            'a_vote_up',
            
            'q_vote_down',
            'a_vote_down',
            
            'q_flag',
            'a_flag',
            'c_flag',
            
            'q_unflag',
            'a_unflag',
            'c_unflag',
            
            'u_edit',
            'u_level',
            'u_block',
            'u_unblock',
         ];
         
         $special = [
            'a_post',
            'c_post'
        ];
         
        if (in_array($event, ['a_select', 'a_unselect']) && !qa_opt('q2apro_onsitenotifications_bestanswers')) {
            return;
        } else if (in_array($event, ['q_vote_up', 'a_vote_up', 'q_vote_down', 'a_vote_down']) && !qa_opt('q2apro_onsitenotifications_votes')) {
            return;
        } else if (in_array($event, ['q_flag', 'a_flag', 'c_flag', 'q_unflag', 'a_unflag', 'c_unflag']) && !qa_opt('q2apro_onsitenotifications_flags')) {
            return;
        }

        // events with voting/selecting/flagging
        if (in_array($event, $events)) {
            
            if (0 === strpos($event, 'u_')) {
                $actionUserId = $params['userid'];
            } else {
                $actionUserId = qa_db_read_one_value(
                    qa_db_query_sub(
                        'SELECT userid FROM ^posts WHERE postid = #',
                        $params['postid']
                    ),
                    true
                );
            }
            
            if ($userId != $actionUserId) {
                $handle = qa_post_userid_to_handle($actionUserId);
                $eventName = 'in_' . $event;
                $paramString = '';
                
                foreach ($params as $key => $value) {
                    $paramString .= (strlen($paramString) ? '\t' : '') . $key . '=' . $this->valueToText($value);                     
                }
                
                qa_db_query_sub(
                    'INSERT INTO ^eventlog (datetime, ipaddress, userid, handle, cookieid, event, params) VALUES (NOW(), $, $, $, #, $, $)',
                    qa_remote_ip_address(), $actionUserId, $handle, $cookieId, $eventName, $paramString
                );
            }
        }
        
        // events with new answers/comments
        if (in_array($event, $special)) {

            $postAuthorId = qa_db_read_one_value(
                qa_db_query_sub(
                    'SELECT userid FROM ^posts WHERE postid=#',
                    $params['postid']
                ),
                true
            );

            $parentPostAuthorId = qa_db_read_one_value(
                qa_db_query_sub(
                    'SELECT userid FROM ^posts WHERE postid=#',
                    $params['parentid']
                ),
                true
            );

            if ($postAuthorId !== $parentPostAuthorId) {
        
                $parentPostAuthorHandle = qa_post_userid_to_handle($parentPostAuthorId);
                
                if ('a_post' === $event) {
                    $eventName = 'in_a_question';
                } else if ('Q' === $params['parenttype']) {
                    $eventName = 'in_c_question';
                } else {
                    $eventName = 'in_c_answer';
                }
                
                $paramString='';
                
                foreach ($params as $key => $value) {
                    $paramString .= (strlen($paramstring) ? '\t' : '') . $key . '=' . $this->valueToText($value);
                }
                
                qa_db_query_sub(
                    'INSERT INTO ^eventlog (datetime, ipaddress, userid, handle, cookieid, event, params) VALUES (NOW(), $, $, $, #, $, $)',
                    qa_remote_ip_address(), $parentPostAuthorId, $parentPostAuthorHandle, $cookieId, $eventName, $paramString
                );              
            }

            if ('c_post' === $event) {
                $eventName = 'in_c_comment';
                // check if we have more comments to the parent
                // DISTINCT: if a user has more than 1 comment just select him unique to inform him only once
                $comments = qa_db_read_one_assoc(
                    qa_db_query_sub(
                        'SELECT DISTINCT userid FROM `^posts` WHERE `parentid` = # AND `type` = "C" AND `userid` IS NOT NULL',
                        $params['parentid']
                    ),
                    true
                );

                if (null !== $comment) {
                    $commentUserId = $comment['userid'];

                    // don't inform author
                    if ($commentUserId != $postAuthorId && $commentUserId != $parentPostAuthorId) {
                        $handle = qa_post_userid_to_handle($commentUserId);
                        $paramString = '';

                        foreach ($params as $key => $value) {
                            $paramString .= (strlen($paramString) ? '\t' : '') . $key . '=' . $this->valueToText($value);
                        }

                        qa_db_query_sub(
                            'INSERT INTO ^eventlog (datetime, ipaddress, userid, handle, cookieid, event, params) VALUES (NOW(), $, $, $, #, $, $)',
                            qa_remote_ip_address(), $commentUserId, $handle, $cookieId, $eventName, $paramString
                        );
                    }
                }
            }   
        }
    }

    public function valueToText($value)
    {
        if (is_array($value)) {
            $text = 'array(' . count($value) . ')';
        } elseif (strlen($value) > 40) {
            $text = substr($value, 0, 38) . '...';
        } else {
            $text = $value;
        }
            
        return strtr($text, "\t\n\r", '   ');
    }
    
}
