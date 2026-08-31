<?php
/**
 * Health check za produkcijski container (docker-compose.prod.yml).
 *
 * Prejšnja različica je klicala file_get_contents('http://localhost/wp-login.php')
 * in štela false kot napako. V produkciji je definiran FORCE_SSL_ADMIN, zato
 * wp-login.php vrne 302 na https://zvij.si/… — file_get_contents je sledil
 * preusmeritvi na TUJ strežnik (dokler DNS ni preklopljen) in vračal false.
 * Container je bil zato trajno »unhealthy«, čeprav je stran delovala.
 *
 * Zdaj preverimo samo to, kar nas res zanima: da PHP/Apache odgovori z veljavno
 * HTTP glavo. Preusmeritvam namenoma ne sledimo.
 */

$context = stream_context_create([
    'http' => [
        'method'          => 'HEAD',
        'follow_location' => 0,
        'ignore_errors'   => true,
        'timeout'         => 5,
    ],
]);

@file_get_contents('http://127.0.0.1/wp-login.php', false, $context);

$status = $http_response_header[0] ?? '';
exit(str_starts_with($status, 'HTTP/1.') ? 0 : 1);
