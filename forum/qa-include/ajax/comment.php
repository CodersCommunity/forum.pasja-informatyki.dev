<?php
/*
    Question2Answer by Gideon Greenspan and contributors
    http://www.question2answer.org/

    File: qa-include/qa-ajax-comment.php
    Description: Server-side response to Ajax create comment requests

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    More about this license: http://www.question2answer.org/license.php
*/

require_once QA_INCLUDE_DIR . 'app/users.php';
require_once QA_INCLUDE_DIR . 'app/limits.php';
require_once QA_INCLUDE_DIR . 'db/selects.php';

function find_next_comment_id($last_id, $all_ids)
{
    foreach ($all_ids as $id) {
        if ($id > $last_id) {
            return $id;
        }
    }

    return end($all_ids);
}

// Load relevant information about this question and the comment parent
$questionid = qa_post_text('c_questionid');
$parentid = qa_post_text('c_parentid');
$userid = qa_get_logged_in_userid();

[$question, $parent, $children] = qa_db_select_with_pending(
    qa_db_full_post_selectspec($userid, $questionid),
    qa_db_full_post_selectspec($userid, $parentid),
    qa_db_full_child_posts_selectspec($userid, $parentid)
);

if (
    isset($question['basetype'], $parent['basetype'])
    && $question['basetype'] === 'Q' && ($parent['basetype'] === 'Q' || $parent['basetype'] === 'A')
) {
    if ($question['type'] === 'Q_HIDDEN' || $parent['type'] === 'A_HIDDEN') {
        echo "QA_AJAX_RESPONSE\n0\n" . qa_lang('question/question_answer_hidden');
        return;
    }
    if (qa_user_post_permit_error('permit_post_c', $parent, QA_LIMIT_COMMENTS)) {
        echo "QA_AJAX_RESPONSE\n0\n" . qa_lang('question/comment_limit');
        return;
    }

    require_once QA_INCLUDE_DIR . 'app/captcha.php';
    require_once QA_INCLUDE_DIR . 'app/format.php';
    require_once QA_INCLUDE_DIR . 'app/post-create.php';
    require_once QA_INCLUDE_DIR . 'app/cookies.php';
    require_once QA_INCLUDE_DIR . 'pages/question-view.php';
    require_once QA_INCLUDE_DIR . 'pages/question-submit.php';
    require_once QA_INCLUDE_DIR . 'util/sort.php';

    // Try to create the new comment
    $usecaptcha = qa_user_use_captcha(qa_user_level_for_post($question));
    $commentid = qa_page_q_add_c_submit($question, $parent, $children, $usecaptcha, $in, $errors);

    // If successful, page content will be updated via Ajax
    if ($commentid === null) {
        echo "QA_AJAX_RESPONSE\n0\n" . implode(' ', $errors ?? []);
        return;
    }

    $children = qa_db_select_with_pending(qa_db_full_child_posts_selectspec($userid, $parentid));
    $parent = array_merge($parent, qa_page_q_post_rules(
        $parent,
        ($questionid == $parentid) ? null : $question,
        null,
        $children
    ));
    // in theory we should retrieve the parent's siblings for the above, but they're not going to be relevant

    foreach ($children as $key => $child) {
        $children[$key] = array_merge($child, qa_page_q_post_rules($child, $parent, $children, null));
    }
    $usershtml = qa_userids_handles_html($children, true);
    qa_sort_by($children, 'created');
    $c_list = qa_page_q_comment_follow_list($question, $parent, $children, true, $usershtml, false, null);

    $themeclass = qa_load_theme_class(qa_get_site_theme(), 'ajax-comments', null, null);
    $themeclass->initialize();

    // Return only new comments
    $ids = array_keys($c_list['cs']);
    $from_id = find_next_comment_id((int) qa_post_text('last_comment_id'), $ids);
    $index = array_search($from_id, $ids);
    if ($index !== false) {
        $c_list['cs'] = array_slice($c_list['cs'], $index, null, true);
    }

    // Send back the ID of the new comment and HTML
    echo "QA_AJAX_RESPONSE\n1\n";
    echo qa_anchor('C', $commentid) . "\n";
    $themeclass->c_list_items($c_list['cs']);

    return;
}

echo "QA_AJAX_RESPONSE\n0"; // fall back to non-Ajax submission if there were any problems
