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

return [
    // default
    'enable_plugin'          => 'Włącz plugin',
    'minimum_level'          => 'Wymagane uprawnienia aby uzyskać dostęp do tego panelu:',
    'plugin_disabled'        => 'Plugin został wyłączony.',
    'access_forbidden'       => 'Dostęp zabroniony.',
    'plugin_page_url'        => 'Otwórz stronę:',
    'contact'                => 'W razie pytań odwiedź ^1q2apro.com^2',
    'no_notifications_label' => 'Jeśli brak powiadomień, to będzie wyświetlane na belce:', // Label for notify bubble on top, next to user name
    'admin_maxeventsshow'    => 'Maksymalna ilość powiadomień do pokazania:', // extra
    'admin_newwindow'        => 'Otwieraj linki w nowej karcie:', // extra
    'admin_rtl'              => 'Język RTL?', // extra (EN)
    'admin_flags'            => 'Pokazywać powiadomienia o flagach?',
    'admin_bestanswers'      => 'Pokazywać powiadomienia o najlepszych odpowiedziach?',
    'admin_votes'            => 'Pokazywać powiadomienia o głosach?',
    
    // plugin
    'my_notifications'       => 'Moje powiadomienia',
    'show_notifications'     => 'Pokaż powiadomienia',
    'one_notification'       => '1 powiadomienie',
    'x_notifications'        => 'Nowe powiadomienia',
    'close'                  => 'zamknij',
    'in_answer'              => 'Odpowiedź do',
    'in_comment'             => 'Komentarz do',
    'in_bestanswer'          => 'Najlepsza odpowiedź dla',
    'in_unbestanswer'        => 'Cofnięta najlepsza odpowiedź dla',
    'in_upvote'              => 'Upvote dla',
    'in_downvote'            => 'Downvote dla',
    'in_flag'                => 'Flaga dla',
    'in_unflag'              => 'Cofnięta flaga dla',  
    'in_block'               => 'Zostałeś zablokowany',
    'in_unblock'             => 'Zostałeś odblokowany',
    'you_received'           => 'Odebrano',
    'message_from'           => 'prywatną wiadomość',
    'wallpost_from'          => 'post na ścianie',
];
