<?php

class discord_integration_page
{
    protected $page_uri = 'chat-discord';

    protected $api;

    public function __construct()
    {
        $this->api = new discord_api();
    }

    public function match_request($request)
    {
        return $request === $this->page_uri
            && !empty(qa_opt('discord_integration_client_id'))
            && !empty(qa_opt('discord_integration_client_secret'))
            && !empty(qa_opt('discord_integration_guild_id'))
            && !empty(qa_opt('discord_integration_bot_token'));
    }

    public function process_request()
    {
        $user_id = qa_get_logged_in_userid();
        $connected = $this->get_connected_user($user_id);
        if (!empty($connected)) {
            $action = qa_get('action');
            if ($action === 'disconnect') {
                $this->api->remove_user_from_guild($connected['discord_id']);
                qa_db_query_sub(
                    "UPDATE ^discord_integrations SET disconnected_date=NOW() WHERE id_integration=#",
                    $connected['id_integration']
                );

                return $this->get_page_content(null, qa_lang_html('discord_integration/disconnected_success'));
            }

            return $this->get_page_content($connected);
        }

        if (empty($user_id)) {
            return $this->get_page_content(
                null,
                null,
                qa_insert_login_links(qa_lang_html('discord_integration/not_logged'), 'login')
            );
        }
        if (qa_get_logged_in_flags() & QA_USER_FLAGS_USER_BLOCKED) {
            return $this->get_page_content(
                null,
                null,
                qa_lang_html('discord_integration/user_blocked')
            );
        }
        if (!empty(qa_get('error'))) {
            return $this->get_connection_error();
        }

        $code = qa_get('code');
        if (!empty($code)) {
            $token_data = $this->api->get_token($code);
            if (!isset($token_data['access_token'])) {
                return $this->get_connection_error();
            }
            $user_data = $this->api->get_user($token_data['access_token']);
            if (!isset($user_data['username'])) {
                return $this->get_connection_error();
            }

            $count = qa_db_read_one_value(qa_db_query_sub(
                'SELECT COUNT(id_integration) FROM ^discord_integrations WHERE discord_id=# AND disconnected_date IS NULL',
                $user_data['id']
            ));
            if ($count > 0) {
                return $this->get_page_content(
                    null,
                    null,
                    qa_lang_html('discord_integration/already_connected_account')
                );
            }

            $this->api->join_user_to_guild($user_data['id'], qa_get_logged_in_handle(), $token_data['access_token']);
            qa_db_query_sub(
                'INSERT INTO ^discord_integrations (id_user, discord_id, discord_username, discord_discriminator) VALUES ($, #, #, #)',
                $user_id,
                $user_data['id'],
                $user_data['username'],
                $user_data['discriminator']
            );

            return $this->get_page_content([
                'discord_username' => $user_data['username'],
                'discord_discriminator' => $user_data['discriminator']
            ], qa_lang_html('discord_integration/success_joined'));
        }

        return $this->get_page_content();
    }

    protected function get_page_content($connected_user = null, $success_message = null, $error_message = null)
    {
        $qa_content = qa_content_prepare();
        $qa_content['custom'] = '';
        $qa_content['title'] = qa_lang_html('discord_integration/title');
        $qa_content['head_lines'][] = '<style>
                .qa-form-tall-button {
                    font-size: 18px;
                    padding: 10px 30px;
                }
                .qa-form-tall-button:hover, .qa-form-tall-button:visited {
                    color: #fff;
                    text-decoration: none;
                }
                .connect-button {
                    text-align: center;
                    margin: 20px 0;
                }
                .qa-form-tall-ok {
                    margin-bottom: 16px;
                    display: block;
                }
            </style>';

        if (!empty($error_message)) {
            $qa_content['error'] = $error_message;
        }
        if (!empty($success_message)) {
            $qa_content['custom'] .= '<div class="qa-form-tall-ok">' . $success_message . '</div>';
        }

        if (!empty(qa_opt('discord_integration_top_info'))) {
            $qa_content['custom'] .= qa_opt('discord_integration_top_info');
        }
        if (empty($connected_user) && qa_is_logged_in() && !(qa_get_logged_in_flags() & QA_USER_FLAGS_USER_BLOCKED)) {
            $url = $this->api->get_discord_url() . '/oauth2/authorize?' . http_build_query([
                    'client_id' => qa_opt('discord_integration_client_id'),
                    'scope' => 'identify guilds.join',
                    'response_type' => 'code',
                    'redirect_uri' => qa_path_absolute($this->page_uri),
                    'prompt' => 'none'
                ],'', '&');
            $qa_content['custom'] .= '<p class="connect-button">
                <a href="' . $url . '" class="qa-form-tall-button">' . qa_lang_html('discord_integration/join') . '</a>
            </p>';
        } elseif (!empty($connected_user)) {
            $qa_content['custom'] .= '<p><strong>' . qa_lang_html('discord_integration/already_joined') .
                ' @' . $connected_user['discord_username'] . '#' . $connected_user['discord_discriminator'] . '</strong></p>
                <p><a href="?action=disconnect">' . qa_lang_html('discord_integration/disconnect_button') . '</a></p>';
        }
        if (!empty(qa_opt('discord_integration_bottom_info'))) {
            $qa_content['custom'] .= qa_opt('discord_integration_bottom_info');
        }

        return $qa_content;
    }

    protected function get_connection_error()
    {
        $error = qa_lang_html('discord_integration/join_error');
        $error = strtr($error, [
            '^1' => '<a href="' . qa_path_html('feedback') .'">',
            '^2' => '</a>',
        ]);

        return $this->get_page_content(null, null, $error);
    }

    protected function get_connected_user($user_id)
    {
        $result = qa_db_query_sub(
            'SELECT id_integration, discord_id, discord_username, discord_discriminator FROM ^discord_integrations WHERE id_user=$ AND disconnected_date IS NULL',
            $user_id
        );

        if ($result->num_rows === 0) {
            return null;
        }

        return qa_db_read_one_assoc($result);
    }
}
