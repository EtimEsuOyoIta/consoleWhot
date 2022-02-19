<?php
/**
 * Created by PhpStorm.
 * User: EtimEsuOyoIta
 * Date: 19/02/2022
 * Time: 13:22
 */

const Suits = [
    "whot" => [
        "symbol" => "W",
        "cards" => [20, 20, 20, 20, 20],
        "call" => true,
        "covered" => null
    ],
    "star" => [
        "symbol" => "\u{2605} ",
        "cards" => [1, 2, 3, 4, 5, 7, 8],
        "call" => false,
        "covered" => [1, 4, 7, 8]
    ],
    "angle" => [
        "symbol" => "\u{25b2} ",
        "cards" => [1, 2, 3, 4, 5, 7, 8, 10, 11, 12, 13, 14],
        "call" => false,
        "covered" => [1, 4, 7, 8]
    ],
    "circle" => [
        "symbol" => "\u{2609} ",
        "cards" => [1, 2, 3, 4, 5, 7, 8, 10, 11, 12, 13, 14],
        "call" => false,
        "covered" => [1, 4, 7, 8]
    ],
    "cross" => [
        "symbol" => "\u{2629} ",
        "cards" => [1, 2, 3, 5, 7, 10, 11, 13, 14],
        "call" => false,
        "covered" => [1, 7]
    ],
    "square" => [
        "symbol" => "\u{25a9} ",
        "cards" => [1, 2, 3, 5, 7, 10, 11, 13, 14],
        "call" => false,
        "covered" => [1, 7]
    ],
];

function game($id = null) {
    return isset($id) ? getGame($id) : newGame();
}

function newGame() {
    // generate a new game id
    $t = time();
    $id = isset($id) ? $id : uniqid("$t.");

    // populate new game object
    $g = new stdClass;
    $g->id = $id;
    $g->createdAt = $t;
    $g->createdWhen = date("r", $t);
    $g->startedAt = null;
    $g->startedWhen = null;
    $g->endedAt = null;
    $g->endedWhen = null;
    $g->loaded = false;
    $g->players = [];
    $g->play = [];
    $g->market = [];
    $g->log = [];
    $g->lastPlayed = new stdClass;
    $g->gameFile = "games" . DIRECTORY_SEPARATOR . $g->id . ".game";
    $g->save = function() use ($g) {
        $gameFile = fopen($g->gameFile, "w");
        $state = json_encode($g);
        fwrite($gameFile, $state);
    };
    return $g;
}

function getGame($id) {
    $gameFile = "games" . DIRECTORY_SEPARATOR . $id . ".game";
    if (!file_exists($gameFile)) return false;
    $meta = file_get_contents($gameFile);
    if (!$meta) return false;
    return json_decode($meta);
}

function freshDeck() {
    $freshDeck = [];
    foreach (Suits as $suit => $cards) {
        foreach ($cards['cards'] as $value) {
            $freshDeck[] = card($suit, $value);
        }
    }

    return $freshDeck;
}

function card($suit, $value) {
    if (!array_key_exists($suit, Suits)) return false;
    $meta = Suits[$suit];

    $c = new stdClass;
    $c->symbol = $meta['symbol'];
    $c->suit = $suit;
    $c->value = $value;
    $c->name = "$suit-$value";
    $c->marker = "[ ". $c->symbol. (intval($value) <= 9 ? " $value " : "$value "). "]";
    return $c;
}

function newLine($yenyen = "") {
    echo "$yenyen\n";
}

function gist($yenyen, array $log = null, $show = true) {
    $t = time();
    $log[] = date("r", $t) . ": $yenyen";
    if ($show) newLine(date("H.i:s a", $t). ": $yenyen");
    sleep(1);
}

// we will build a form then fit our players into this form
function player($name = "me") {
    $p = new stdClass;
    $p->name = $name;
    $p->level = $name != "CPU" ? "human" : "CPU"; // also "CPU"
    $p->hand = [];
    $p->current = false;
    $p->pick = function($num = 1, array $market = null) use ($p){
        if (!empty(Market)) {
            for($i = 0; $i < $num; $i++) {
                ($p->hand)[] = array_pop($market);
            }
        }
    };
    return $p;
}

function shuffleDeck($deck) {
    shuffle($deck);
    gist("Deck shuffled...");
    return $deck;
}

function createPlayers($game) {
    $cpu = player("CPU");
    $me = player(readline("\nWida you, abobby? Who goes there? "));
    $desc = "{$cpu->name} vs {$me->name}";
    gist("Today's game features $desc...", $game->log);
    $players = [$cpu, $me];
    $game->desc = $desc;
    $game->players = $players;
    save($game);
}

function save($game) {
    ($game->save)();
}

function cutCards($deck) {
    $cut = 10;
    for ($i=0;$i<10;$i++) $play[] = array_pop($market);
    gist("Cards ready to be shared...");

}

function play($game) {
    $loaded = $game->loaded;
    if (!$loaded) {
        createPlayers($game);
        shuffleDeck(freshDeck());
    }

}

function mainMenu() {
    clearScreen();
    gist("Whot Game begins", null, false);

    newLine("Whot Game Menu");
    newLine("++++++++++++++");
    newLine();
    newLine("Welcome to Whot. Please select an option:");

    $option = readline(join("\n", [
        "N: New Game",
        "O: Open Game",
        "S: Game Stats",
        "X: Exit Game",
        "",
        "Your choice? "
    ]));
    $optionUsed = strtoupper($option);

    $valid = str_split("NOSX");
    if (!in_array($optionUsed, $valid)) {
        newLine("Your choice of $option was not a valid input");
        newLine("You will be prompted to provide a more valid input in 2 seconds.");
        sleep(2);
        mainMenu();
    } else {
        newLine("Your choice is valid, moving in 2 secs...");
        sleep(2);

        switch ($optionUsed) {
            case "N":
                newLine("You are starting a new game...");
                $game = newGame();
                createPlayers($game);
            break;

            case "O":
                break;

            case "S":
                break;

            case "X":
                newLine("You have chosen to quit the game... Bye-bye!");
                gist("User exits game.", null,false);
                clearScreen();
                die();
                break;

            default:
                break;

        }
    }
}

function clearScreen() {
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';   //^[H^[J
}

function whot($id = null) {
    static $hott;
    $hott++;

    static $market = [];
    static $game;

    mainMenu();

    //print_r(getGame('1645279186.6210f7d25234a'));
    die();

    gist("So, try dey baff $hott,ok");
    if ($hott >= 3) {
        gist("Hott ended at $hott.");
        $game = game();
        ($game->save)();
        die();
    }
    whot();
}

whot();
