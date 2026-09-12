<?php

if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io');
}
if (!defined('MAIL_USER')) {
    define('MAIL_USER', $_ENV['MAIL_USER'] ?? 'bc69bc3b5a9557');
}
if (!defined('MAIL_PASS')) {
    define('MAIL_PASS', $_ENV['MAIL_PASS'] ?? 'af1c3fa2ceb46c');
}
if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 2525));
}
if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', $_ENV['MAIL_FROM'] ?? 'correo@gym.com');
}
if (!defined('MAIL_URL')) {
    define('MAIL_URL', $_ENV['APP_URL'] ?? ($_ENV['MAIL_URL'] ?? 'http://localhost:3000'));
}
if (!defined('MAIL_ENCRYPTION')) {
    define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? (MAIL_PORT === 465 ? 'ssl' : 'tls'));
}
