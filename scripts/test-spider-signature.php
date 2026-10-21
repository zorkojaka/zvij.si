<?php
if (PHP_SAPI !== 'cli') { exit; }
require '/var/www/html/wp-load.php';
$config = zvij_spider_config();
if (! $config) { exit('Missing config'); }
$r = new WP_REST_Request('POST', '/zvij/v1/spider-command');
$body = '{"eventId":"test-no-mutation"}'; $at = (string) round(microtime(true) * 1000);
$r->set_body($body); $r->set_header('X-Spider-Time', $at);
$r->set_header('X-Spider-Signature', hash_hmac('sha256', $at . '.' . $body, $config['secret']));
if (! zvij_spider_verify_command($r)) { exit('FAIL signed request'); }
$r->set_body('tampered'); if (zvij_spider_verify_command($r)) { exit('FAIL tampered'); }
echo "PASS production signature validation, tampered payload rejected; no order changed\n";
