<?php

return [

    /*
    | Short links live at the top level (crqr.in/{code}) next to app pages and
    | bio pages (crqr.in/@username). These words can't be used as short-link
    | aliases or usernames so they never clash with a route, now or later.
    */
    'reserved' => [
        'about', 'account', 'admin', 'api', 'app', 'assets', 'auth', 'billing', 'bio', 'blog', 'build',
        'contact', 'crqr', 'dashboard', 'docs', 'email', 'favicon', 'favicon.ico', 'features', 'forgot-password',
        'help', 'home', 'link', 'links', 'login', 'logout', 'mail', 'password', 'pricing', 'privacy', 'profile',
        'qr', 'register', 'report', 'reset-password', 'robots.txt', 'root', 'settings', 'signin', 'signup',
        'static', 'stats', 'storage', 'support', 'terms', 'up', 'user', 'users', 'verify', 'www',
    ],

    // Hosts that must not be shortened again (prevents redirect loops). APP_URL's host is added automatically.
    'own_hosts' => ['crqr.in', 'www.crqr.in'],

    // Random short codes skip look-alike characters (0/o, 1/l/i) so printed links are easy to type.
    'code_alphabet' => 'abcdefghjkmnpqrstuvwxyz23456789',
    'code_length' => 6,

];
