<?php

include __DIR__ . '/vendor/autoload.php';

use Discord\DiscordCommandClient;
use Discord\WebSockets\Event;

class MyBot
{
    private $dotenv;
    private $discord;
    private $translated = [];

    function __construct()
    {
        $this->dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    }

    function Run()
    {
        $this->dotenv->load();
        $this->dotenv->required('token')->required();

        $this->discord = new DiscordCommandClient(['token' => $_ENV['token']]);

        $this->discord->on('ready', function ($discord) {
            $discord->on(Event::MESSAGE_REACTION_ADD, function ($message_reaction) {
                if ($message_reaction->emoji->name == "🇷🇺") {
                    if (!in_array($message_reaction->message_id, $this->translated)) {
                        //echo file_put_contents('message_reaction.log', print_r($message_reaction, true)), PHP_EOL;
                        $promise = $message_reaction->channel->getMessage($message_reaction->message_id);
                        $promise->then(
                            function ($response) {
                                $this->getMessage($response);
                            }
                        );
                        $this->translated[] = $message_reaction->message_id;
                    }
                }
            });
        });
        $this->discord->run();
    }

    private function getMessage($message)
    {
        $text = $this->Lat2ru($message->content);
        $message->channel->sendMessage($text);
    }

    private function Lat2ru($string)
    {
        $cyr = array(
            "Щ", "Ш", "Ч", "Ц", "Ю", "Я", "Ж", "А", "Б", "В", "В",
            "Г", "Д", "Е", "Ё", "З", "И", "Й", "К", "Л", "М", "Н",
            "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ь", "Ы", "Ъ",
            "Э", "Є", "Ї", "І",
            "щ", "ш", "ч", "ц", "ю", "я", "ж", "а", "б", "в", "в",
            "г", "д", "е", "ё", "з", "и", "й", "к", "л", "м", "н",
            "о", "п", "р", "с", "т", "у", "ф", "х", "ь", "ы", "ъ",
            "э", "є", "ї", "і"
        );
        $lat = array(
            "Shch", "Sh", "Ch", "C", "Yu", "Ya", "J", "A", "B", "V", "W",
            "G", "D", "E", "E", "Z", "I", "y", "K", "L", "M", "N",
            "O", "P", "R", "S", "T", "U", "F", "H", "",
            "Y", "", "E", "E", "Yi", "I",
            "shch", "sh", "ch", "c", "Yu", "Ya", "j", "a", "b", "v", "w",
            "g", "d", "e", "e", "z", "i", "y", "k", "l", "m", "n",
            "o", "p", "r", "s", "t", "u", "f", "h",
            "", "y", "", "e", "e", "yi", "i"
        );
        $string = str_replace($lat, $cyr, $string);
        $string = str_replace("_", " ", $string);
        return ($string);
    }

    private function post2discord($text)
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
}

$bot = new MyBot();
$bot->Run();