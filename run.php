<?php

include __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('token')->required();
$dotenv->required('read_channel_id')->isInteger()->required();
$dotenv->required('relay2webhookurl')->required();

$discord = new \Discord\Discord([
    'token' => $_ENV['token']
]);

$discord->on('ready', function ($discord) {
    $discord->on('message', function ($message) {
        if ($message->channel_id == $_ENV['read_channel_id']) {
            post2discord($message->content);
        }
    });
});

$discord->run();

function post2discord($text)
{
    $webhookurl = $_ENV['relay2webhookurl'];
    $json_data = json_encode([
        "content" => $text,
        "username" => "ChannelRelayBot",
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);
}
