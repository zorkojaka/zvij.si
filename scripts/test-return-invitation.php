<?php
if (PHP_SAPI !== 'cli') { exit; }
require '/var/www/html/wp-load.php';
$body = zvij_return_invitation_body('TEST-KODA');
if (strpos($body, '☝️😤') === false || strpos($body, 'TEST-KODA') === false || strpos($body, 'samodejno prijavili') === false) { exit("FAIL invitation content\n"); }
if (strpos($body, 'dev.inteligent.si') !== false && home_url() === 'https://zvij.si') { exit("FAIL production links\n"); }
$admins = get_users(['role' => 'administrator', 'number' => 1]);
wp_set_current_user($admins[0]->ID);
$mail_count = 0;
add_filter('pre_wp_mail', function () use (&$mail_count) { $mail_count++; return true; });
ob_start(); zvij_return_invitation_page(); $html = ob_get_clean();
if ($mail_count || strpos($html, 'Pošlji test samo meni') === false || strpos($html, 'name="confirmed"') === false) { exit("FAIL safe preview\n"); }
echo "PASS message, preview without emails, explicit confirmation, production links\n";
