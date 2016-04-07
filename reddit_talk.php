<?php

require_once("reddit.php");
$user = "";
$pass = "";

$reddit = new reddit($user, $pass);
$sub = "/r/all";
$titles = getTitles($reddit, $sub);

$x = 0;
foreach ($titles as $t) {
    if ($x < 10) {
        $clean = strip_tags(stripslashes($t));
        $clean = str_replace('"', "", $clean);
        $clean = str_replace("'", "", $clean);
        $clean = preg_replace("/[^a-zA-Z0-9 ]+/", "", $clean);

        echo $clean . "\n";
        $cmd = 'espeak -vdefault+m3 -p 40 -s 160 -g 2 "' . $clean . '"';
        $output = shell_exec($cmd);
        sleep(1);
    }
    $x++;
}

function getTitles($reddit, $sub) {
    echo "Fetching Sub: " . $sub . "\n";

    $s = new \stdClass();
    $s->sub = $sub;
    $s->subname = $sub;
    $s->items = getNew($reddit, $s->sub);
    echo "Fetching Sub Complete: " . $sub . "\n";

    foreach ($s->items as $i) {
        $titles[] = $i->title;
    }
    return $titles;
}

function getNew($reddit, $sub) {
    $response = $reddit->getRawJSON($sub);
    foreach ($response->data->children as $item) {
        $data = $item->data;
        $out[] = $data;
    }
    return $out;
}
