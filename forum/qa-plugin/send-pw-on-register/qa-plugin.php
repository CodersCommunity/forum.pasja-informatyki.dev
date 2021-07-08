<?php
/*
    Plugin Name: Auto-send register PW message.
    Plugin URI: https://github.com/CodersCommunity/forum.pasja-informatyki.local
    Plugin Description: This plugin automatically send private message to new user.
    Plugin Version: 1.0
    Plugin Date: 2018-06-10 
    Plugin Author: Mariusz08
    Plugin Author URI: https://forum.pasja-informatyki.pl/user/Mariusz08
    Plugin License: GNU GPL v2
    Plugin Update Check URI: https://github.com/CodersCommunity/forum.pasja-informatyki.local
    Plugin Minimum Question2Answer Version: 1.7
    Plugin Minimum PHP Version: 5.3
*/

if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

qa_register_plugin_module(
  'event',
  'qa-pw-admin.php',
  'qa_pw_admin',
  'PW Admin'
);

qa_register_plugin_module(
  'event',
  'qa-pw-event.php',
  'qa_pw_event',
  'PW Event');
