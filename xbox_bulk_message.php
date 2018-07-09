#!/usr/bin/php
<?php
date_default_timezone_set('Europe/Lisbon');
error_reporting(E_ALL & ~E_USER_DEPRECATED);

define('BASE_PATH', realpath(dirname(realpath(__FILE__))));

if (!file_exists(BASE_PATH . '/vendor')) {
    die('Please use `composer install`.');
}

if (!file_exists(BASE_PATH . '/config/options.ini')) {
    die('In the config directory, copy the file `options.ini.dist` to `options.ini`.');
}

$options = parse_ini_file(BASE_PATH . '/config/options.ini', true);

if ($options['xbl.io']['apikey'] === 'APIKEY') {
    die('Add your key to options.ini under the apikey (APIKEY).');
}

require BASE_PATH . '/vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;

try {
    $client = new GuzzleHttp\Client(['base_uri' => $options['xbl.io']['base_uri']]);

    $message_array = explode("\n", wordwrap(preg_replace('!\s+!', ' ', $options['xbox']['message']), '240'));

    foreach ($message_array as $key => &$value) {
        $message_counter = '(' . ($key + 1) . '/' . count($message_array) . ')';
        $value .= " $message_counter";
        $payload = [
            'to'      => implode(',', $options['xbox']['gamertag']),
            'message' => $value,
        ];

        echo "Sending message to: {$payload['to']}\n";
        echo "Message: {$payload['message']}\n";

        $request = $client->request('POST', 'conversations', [
            'headers' => [
                'Accept'          => 'application/json',
                'X-Authorization' => $options['xbl.io']['apikey'],
            ],
            'json' => $payload,
        ]);

        echo "Status: {$request->getStatusCode()}\n";
    }
} catch (Exception | GuzzleException $e) {
    die($e->getMessage());
}