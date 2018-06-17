<?php

class qa_html_theme_layer extends qa_html_theme_base
{
    private $isLogged;
    
    public function head_script()
    {      
        qa_html_theme_base::head_script();
        $this->isLogged = qa_is_logged_in(); 
        
        if ($this->isLogged && 'question' === $this->template) {
            $this->output(
                '
                <script>
                    const flagAjaxURL = "' . qa_path('ajaxflagger') . '";
                    const flagQuestionid = ' . $this->content['q_view']['raw']['postid'] . ';
                </script>
            '
            );
            $this->output(
                '
                <script type="text/javascript" src="' . QA_HTML_THEME_LAYER_URLTOROOT . 'script.js"></script>
                <link rel="stylesheet" href="' . QA_HTML_THEME_LAYER_URLTOROOT . 'styles.css">
            '
            );
        }
    }
    public function q_view_buttons($q_view)
    {
        if ($this->isLogged && isset($q_view['form']['buttons']['flag'], $q_view['raw']['postid'])) {
            $q_view['form']['buttons']['flag']['tags'] =
                'data-postid="' . $q_view['raw']['postid'] . '" data-posttype="q" style="cursor: pointer" ';
        }
        qa_html_theme_base::q_view_buttons($q_view);
    }
    public function a_item_buttons($a_item)
    {
        if ($this->isLogged && isset($a_item['form']['buttons']['flag'], $a_item['raw']['postid'])) {
            $a_item['form']['buttons']['flag']['tags'] =
                'data-postid="' . $a_item['raw']['postid'] . '" data-posttype="a" style="cursor: pointer" ';
        }
        qa_html_theme_base::a_item_buttons($a_item);
    }
    public function c_item_buttons($c_item)
    {
        if ($this->isLogged && isset($c_item['form']['buttons']['flag'], $c_item['raw']['postid'])) {
            $c_item['form']['buttons']['flag']['tags'] = 'data-postid="' . $c_item['raw']['postid'] . '" data-posttype="c" data-parentid="' . $c_item['raw']['parentid'] . '" style="cursor: pointer" ';
        }
        qa_html_theme_base::c_item_buttons($c_item);
    }
    public function body_hidden()
    {
        if ($this->isLogged && 'question' === $this->template) {
            $this->output(
                '
            <div id="flagbox-popup" class="modal-background flagbox-popup" hidden>
                <div class="flagbox-center">
                    <div class="qa-flag-reasons-wrap">
                        <p><b>
                          '
                          . qa_lang('q2apro_flagreasons_lang/reason')
                          . '
                        </b></p>
                        ');
                        for ($i=1; $i<=6; $i++) {
                            $this->output('<label for="qa-spam-reason-radio-' . $i . '">
                            <input type="radio" class="qa-spam-reason-radio" name="qa-spam-reason-radio" id="qa-spam-reason-radio-' . $i . '" value="' . $i . '">
                            '
                            . q2apro_flag_reasonid_to_readable($i)
                            . '
                            </label>');
                        }
                        $this->output('
                        <p><b>
                            '
                            . qa_lang('q2apro_flagreasons_lang/note')
                            . '
                        </b></p>
                        <div class="qa-spam-reason-text-wrap">
                            <input type="text" name="qa-spam-reason-text" class="qa-spam-reason-text" placeholder="'
                . qa_lang('q2apro_flagreasons_lang/enter_details')
                . '">
                        <div id="qa-spam-reason-error" class="qa-error" hidden></div></div>
                        
                        <input type="button" class="qa-form-tall-button qa-form-tall-button-ask qa-form-wide-text qa-go-flag-send-button" value="'
                . qa_lang('q2apro_flagreasons_lang/send')
                . '">
                        
                        <button class="close-preview-btn">X</button>
                    </div>
                </div> 
            </div>
            ');
        }
        qa_html_theme_base::body_hidden();
    }
    public function post_tags($post, $class)
    {
        qa_html_theme_base::post_tags($post, $class);
        $postId      = $post['raw']['postid'];
        $flagReasons = q2apro_get_postflags($postId);
        if (!empty($flagReasons)) {
            $flagsOut = '<p class="qa-flagreason">';
            foreach ($flagReasons as $flag) {
                $userHandle = qa_userid_to_handle($flag['userid']);
                $reason     = q2apro_flag_reasonid_to_readable($flag['reasonid']);
                $notice     = $flag['notice'];
                $addedBr    = false;
                if (!empty($notice)) {
                    $notice = '<q>' . $notice . '</q>';
                }
                $flagsOut .= '
                    <span class="flagreason-what">Post zgłoszony z powodu <i><strong>' . $reason . '</strong></i> </span>
                    przez
                    <span class="flagreason-who"><a href="' . qa_path('user') . '/' . $userHandle . '"><b>' . $userHandle . '</b></a></span>.';
                
                if (null !== $notice) {
                    $flagsOut .= '<br>Treść notatki: ' . $notice . '.';
                    $flagsOut .= '<br><br>';
                    $addedBr = true;
                }
                    
                if (!$addedBr) {
                    $flagsOut .= '<br><br>';
                }
            }
            $flagsOut  .= '</p>';
            $userLevel = qa_get_logged_in_level();
            if ($userLevel > QA_USER_LEVEL_EXPERT) {
                $this->output($flagsOut);
            }
        }
    }
    public function post_meta_flags($post, $class)
    {
        if (('qa-a-item' === $class || 'qa-c-item' === $class) && !empty($post['flags']['suffix'])) {
            $flagInfo = q2apro_count_postflags_output($post['raw']['postid']);
            if (!empty($flagInfo) && qa_get_logged_in_level() > QA_USER_LEVEL_EXPERT) {
                $post['flags']['suffix'] .= ': <br>' . $flagInfo;
            }
        }
        qa_html_theme_base::post_meta_flags($post, $class);
    }
}
