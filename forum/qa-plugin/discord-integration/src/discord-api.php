<?php

class discord_api
{
    protected $discord_url = 'https://discord.com/api';

    public function get_discord_url()
    {
        return $this->discord_url;
    }

    public function get_token($code)
    {
        return $this->request('/oauth2/token', http_build_query([
            'code' => $code,
            'client_id' => qa_opt('discord_integration_client_id'),
            'client_secret' => qa_opt('discord_integration_client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => qa_path_absolute('chat-discord')
        ],'', '&'), [], 'POST');
    }

    public function get_user($token)
    {
        return $this->request('/users/@me', [], ["Authorization: Bearer {$token}"]);
    }

    public function join_user_to_guild($user_id, $nick, $token)
    {
        $url = '/guilds/' . qa_opt('discord_integration_guild_id') . '/members/' . $user_id;

        return $this->request($url, json_encode([
            'nick' => $nick,
            'access_token' => $token
        ]), [
            'Content-Type: application/json',
            'Authorization: Bot ' . qa_opt('discord_integration_bot_token')
        ], 'PUT');
    }

    public function remove_user_from_guild($user_id)
    {
        return $this->request('/guilds/' . qa_opt('discord_integration_guild_id') . '/members/' . $user_id, [], [
            'Content-Type: application/json',
            'Authorization: Bot ' . qa_opt('discord_integration_bot_token')
        ], 'DELETE');
    }

    public function change_user_nick($user_id, $nick)
    {
        $url = '/guilds/' . qa_opt('discord_integration_guild_id') . '/members/' . $user_id;

        return $this->request($url, json_encode([
            'nick' => $nick,
        ]), [
            'Content-Type: application/json',
            'Authorization: Bot ' . qa_opt('discord_integration_bot_token')
        ], 'PATCH');
    }

    protected function request($uri, $post_data = [], $headers = [], $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->discord_url . $uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($post_data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        }
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $response;
    }
}
