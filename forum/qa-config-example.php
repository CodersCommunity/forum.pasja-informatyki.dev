<?php
/*
    Question2Answer by Gideon Greenspan and contributors
    http://www.question2answer.org/

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

/*
    IMPORTANT!
    This is file with default configuration.
    To start, copy this file to qa-config.php and modify it if you need.
    Don't modify and don't remove this file!
*/
define('QA_ENVIRONMENT', 'local'); // production or local

define('QA_MYSQL_HOSTNAME', 'forum-mysql');
define('QA_MYSQL_USERNAME', 'test');
define('QA_MYSQL_PASSWORD', 'test');
define('QA_MYSQL_DATABASE', 'forum');
define('QA_MYSQL_TABLE_PREFIX', 'qa_');

define('SENTRY_DSN', '');
define('SENTRY_ENVIRONMENT', 'local');

define('QA_COOKIE_HTTPONLY', true);
define('QA_COOKIE_SECURE', false);
define('QA_RESOURCE_VERSION', null);

define('QA_WS_TOKEN', 'secretKey');
/*
  WebSocket port number should be equal to the Node's one in
  https://github.com/CodersCommunity/http-websocket-server/blob/master/.env.example
*/
define('QA_WS_PORT', 3000);
define('QA_WS_URL', 'http://web_socket:' . QA_WS_PORT);

define('CHANGE_USERNAME_LIMIT_IN_DAYS', 30);

/*
    Set QA_EXTERNAL_USERS to true to use your user identification code in qa-external/qa-external-users.php
    This allows you to integrate with your existing user database and management system. For more details,
    consult the online documentation on installing Question2Answer with single sign-on.

    The constants QA_EXTERNAL_LANG and QA_EXTERNAL_EMAILER are deprecated from Q2A 1.5 since the same
    effect can now be achieved in plugins by using function overrides.
*/
define('QA_EXTERNAL_USERS', false);

/*
    Some settings to help optimize your Question2Answer site's performance.

    If QA_HTML_COMPRESSION is true, HTML web pages will be output using Gzip compression, if
    the user's browser indicates this is supported. This will increase the performance of your
    site, but may make debugging harder if PHP does not complete execution.

    QA_MAX_LIMIT_START is the maximum start parameter that can be requested, for paging through
    long lists of questions, etc... As the start parameter gets higher, queries tend to get
    slower, since MySQL must examine more information. Very high start numbers are usually only
    requested by search engine robots anyway.

    If a word is used QA_IGNORED_WORDS_FREQ times or more in a particular way, it is ignored
    when searching or finding related questions. This saves time by ignoring words which are so
    common that they are probably not worth matching on.

    Set QA_ALLOW_UNINDEXED_QUERIES to true if you don't mind running some database queries which
    are not indexed efficiently. For example, this will enable browsing unanswered questions per
    category. If your database becomes large, these queries could become costly.

    Set QA_OPTIMIZE_LOCAL_DB to true if your web server and MySQL are running on the same box.
    When viewing a page on your site, this will use many simple MySQL queries instead of fewer
    complex ones, which makes sense since there is no latency for localhost access.

    Set QA_OPTIMIZE_DISTANT_DB to true if your web server and MySQL are far enough apart to
    create significant latency. This will minimize the number of database queries as much as
    is possible, even at the cost of significant additional processing at each end.

    Set QA_PERSISTENT_CONN_DB to true to use persistent database connections. Requires PHP 5.3.
    Only use this if you are absolutely sure it is a good idea under your setup - generally it is
    not. For more information: http://www.php.net/manual/en/features.persistent-connections.php

    Set QA_DEBUG_PERFORMANCE to true to show detailed performance profiling information at the
    bottom of every Question2Answer page.
*/
define('QA_HTML_COMPRESSION', true);
define('QA_MAX_LIMIT_START', 19999);
define('QA_IGNORED_WORDS_FREQ', 10000);
define('QA_ALLOW_UNINDEXED_QUERIES', false);
define('QA_OPTIMIZE_LOCAL_DB', false);
define('QA_OPTIMIZE_DISTANT_DB', false);
define('QA_PERSISTENT_CONN_DB', false);
define('QA_DEBUG_PERFORMANCE', false);
define('QA_DB_MAX_CONTENT_LENGTH', 15000);

/*
    If you wish, you can define QA_MYSQL_USERS_PREFIX separately from QA_MYSQL_TABLE_PREFIX.
    If so, it is used instead of QA_MYSQL_TABLE_PREFIX as the prefix for tables containing
    information about user accounts (not including users' activity and points). This allows
    multiple Q2A sites to have shared logins and users, but separate posts and activity.

    define('QA_MYSQL_USERS_PREFIX', 'qa_users_');
*/

/*
    If you wish, you can define QA_BLOBS_DIRECTORY to store BLOBs (binary large objects) such
    as avatars and uploaded files on disk, rather than in the database. If so this directory
    must be writable by the web server process - on Unix/Linux use chown/chmod as appropriate.
    Note than if multiple Q2A sites are using QA_MYSQL_USERS_PREFIX to share users, they must
    also have the same value for QA_BLOBS_DIRECTORY.

    If there are already some BLOBs stored in the database from previous uploads, click the
    'Move BLOBs to disk' button in the 'Stats' section of the admin panel to move them to disk.

    define('QA_BLOBS_DIRECTORY', '/path/to/writable_blobs_directory/');
*/

/*
    If you wish, you can define QA_COOKIE_DOMAIN so that any cookies created by Q2A are assigned
    to a specific domain name, instead of the full domain name of the request by default. This is
    useful if you're running multiple Q2A sites on subdomains with a shared user base.

    define('QA_COOKIE_DOMAIN', '.example.com'); // be sure to keep the leading period
*/

/*
    If you wish, you can define an array $QA_CONST_PATH_MAP to modify the URLs used in your Q2A site.
    The key of each array element should be the standard part of the path, e.g. 'questions',
    and the value should be the replacement for that standard part, e.g. 'topics'. If you edit this
    file in UTF-8 encoding you can also use non-ASCII characters in these URLs.

    $QA_CONST_PATH_MAP = [
        'questions' => 'topics',
        'categories' => 'sections',
        'users' => 'contributors',
        'user' => 'contributor',
    ];
*/

/*
    Out-of-the-box WordPress 3.x integration - to integrate with your WordPress site and user
    database, define QA_WORDPRESS_INTEGRATE_PATH as the full path to the WordPress directory
    containing wp-load.php. You do not need to set the QA_MYSQL_* constants above since these
    will be taken from WordPress automatically. See online documentation for more details.

    define('QA_WORDPRESS_INTEGRATE_PATH', '/PATH/TO/WORDPRESS');
*/
