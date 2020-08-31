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

        $this->discord = new DiscordCommandClient([
            'token' => $_ENV['token'],
            'prefix' => '!'
        ]);

//        $this->discord->registerCommand('ping', function ($message) {
//            return 'pong!';
//        }, [
//            'description' => 'pong!',
//        ]);

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

    public function Lat2ru($string)
    {
        $lat = array(
            "jesli",
            "shch", "sh", "ch", "ca", "c", "yu", "ju", "ya", "ja", "zh", "j", "a", "b", "v", "w",
            "g", "d", "e", "e", "z", "iy", "i", "k", "l", "m", "n",
            "o", "p", "r", "s", "t", "u", "f", "h", "", "y", "",
            "e", "e", "yi", "i"
        );
        $cyr = array(
            "если",
            "щ", "ш", "ч", "ца", "ц", "ю", "ю", "я", "я", "ж", "ж", "а", "б", "в", "в",
            "г", "д", "е", "ё", "з", "ий", "и", "к", "л", "м", "н",
            "о", "п", "р", "с", "т", "у", "ф", "х", "ь", "ы", "ъ",
            "э", "є", "ї", "і"
        );
        $string = str_replace($lat, $cyr, strtolower($string));
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

/*
$text = [
    'dazhe w reale, tolka cherez trudnye wremena i srachiki stanowjatsa wse blizhe. jesli nebudet takowo ispytanie, to my nekogda neuznaem naskolko wse serezna.  wot jesli probombit i i wse posrutsa, i posle etogo wse eshe budut na meste, wot togda budet klasno, a jesli wse razbegutsa, to budet ponjatno, shto budushego i tak nebylo',
    'wy ponimaete pochemu NUZHNO adoptirowatsa? i toshta bylo zajawleno 2 meseza nazad, nashet rorak, uzhe neaktualno... netykaite na eto postojano. ja sam etamu nerad, no u nas netu wybora. ili delaem kak nada, ili umeraem i wse.',
    'Situacyja menjaetsa kazhdyi deni, i jesli postojano operatsa na infu, kotoroi 2 meseca. To mozhna swarachiwatsa. Po moemu my uzhe wse obgoeorili neskoloo raz na sobranijah. I dokumenty jesti. Po fakty kazhetsa prosto hochet igrati sam sebe i nepsritsa',
    'etoi korpe w princype nenuzhny dazhe nalogi pohoroshemu.  zdesi takaja wozmozhnasti zarabatywati samim sebe, no neispolzuetsa. ja predstowljal sebe shto budet wse nanmnogo aktivnei i zhewei proishodit, a s perehodam, wse stalo kak u dichei',
    'nenuzhna wozmushjatsa, ja nechego swerh jesttestwengo netrebuju. trebuju to shto i sam delaju. i posle nashego sobranie, tak nerazu nekto i nedelal nechego w public kopke. smysol mne odnomu opjati na wseh rabotati? poluchitsa kak s ally lunami, ja takoe nehochu.  wy wzroslye i golowa na plechjah jesti. ostalosi tolka podumati kak i delati probywati. za was nekto delati nechego nebudet i eto ne negativ a fakt.'
];
foreach ($text as $txt) {
    echo "\n\n";
    echo $bot->Lat2ru($txt), "\n\n";
}
*/
