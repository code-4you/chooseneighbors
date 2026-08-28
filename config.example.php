<?php
/**
 * Choose Neighbors — configuration EXAMPLE.
 * Copy to config.php and fill in your own values. config.php is gitignored;
 * never commit real credentials.
 */

/* Site identity */
define('SITE_NAME', 'Choose Neighbors');
define('SITE_URL',  'https://example.com');   // no trailing slash

/* Database (MySQL, localhost on typical shared hosting) */
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

/* Google sign-in (https://console.cloud.google.com/apis/credentials) */
define('GOOGLE_CLIENT_ID', '');

/* reCAPTCHA v3 (score-based) and v2 (checkbox) — https://www.google.com/recaptcha/admin */
define('RECAPTCHA_SITE_KEY',    '');
define('RECAPTCHA_SECRET_KEY',  '');
define('RECAPTCHA2_SITE_KEY',   '');
define('RECAPTCHA2_SECRET_KEY', '');

/* Avatar / image hosting via ImgBB (https://api.imgbb.com/) */
define('IMGBB_API_KEY', '');

/* Gemini API — powers the find-a-community directory population (https://aistudio.google.com/) */
define('GEMINI_API_KEY', '');

/* Outgoing mail (SMTP) */
define('SMTP_HOST',   '');
define('SMTP_PORT',   587);
define('SMTP_SECURE', 'tls');   // 'tls' or 'ssl'
define('SMTP_USER',   '');
define('SMTP_PASS',   '');
define('MAIL_FROM',   'no-reply@example.com');

session_start();
