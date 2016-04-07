<?php

include_once('simple_html_dom.php');
include_once('dbs.php');

$db = $GLOBALS['mysqli'];
saveHNPage($db, 'https://news.ycombinator.com/news', 'hns_home');
sleep(10);
saveHNPage($db, 'https://news.ycombinator.com/news?p=2', 'hns_home_pg2');
sleep(10);
saveHNPage($db, 'https://news.ycombinator.com/news?p=3', 'hns_home_pg3');
sleep(10);
saveHNPage($db, 'https://news.ycombinator.com/news?p=4', 'hns_home_pg4');

sleep(10);
saveHNPage($db, 'https://news.ycombinator.com/newest', 'hns_newest');


function saveHNPage($db, $url, $subkey) {
    $html = file_get_html($url);
    $links = getLinks($html);

    foreach ($links as $link) {
        $ni = new \stdClass();
        $ni->subreddit = $db->real_escape_string($subkey);
        $ni->item_title = $db->real_escape_string($link->title);
        $ni->item_url = $db->real_escape_string($link->href);
        $ni->img_path = null;
        $ni->img_name = null;
        $ni->hash_title = md5($link->title);
        $ni->hash_url = md5($link->href);

        $ni->jsondata = $db->real_escape_string(json_encode($link));

        $key = saveItem($db, $ni);
//        echo json_encode($ni) . "\n";
        echo "Item Saved: " . $key . " - " . $db->real_escape_string($link->title) . "\n";
        unset($ni);
        unset($key);
    }
}

function getLinks($html) {
    // Find all links
    $outlinks = Array();
    $links = $html->find('a');
    foreach ($links as $element) {
        $href = $element->href;
        $title = $element->innertext;

        if (!(strpos($href, 'http') === false) && checkUrl($href)) {

            $link = new \stdClass();
            $link->title = $title;
            $link->href = $href;
            $outlinks[] = $link;
            unset($link);
            echo $href . ' - ';
            echo $title . ' ';
            echo "\n";
        }
    }
    return $outlinks;
}

function checkUrl($url) {
    $ok = true;
    $urls[] = 'http://www.ycombinator.com';
    $urls[] = 'http://www.ycombinator.com/';
    $urls[] = 'http://www.ycombinator.com/apply/';
    $urls[] = 'http://www.ycombinator.com/resources/';
    $urls[] = 'http://www.ycombinator.com/contact/';
    $urls[] = 'https://github.com/HackerNews/HN/issues';

    foreach ($urls as $u) {
        if ($url == $u) {
            $ok = false;
        }
    }
    return $ok;
}

function saveItem($db, $i) {
    $sql = "INSERT INTO hn_links (id, hash_title, hash_url, ts_created, ts_screenshot, subreddit, item_title, item_url, img_path, img_name, ss_try1, jsondata) VALUES (NULL, '" . $i->hash_title . "', '" . $i->hash_url . "', '" . date('Y-m-d H:i:s') . "', null, '" . $i->subreddit . "', '" . $i->item_title . "', '" . $i->item_url . "', '" . $i->img_path . "', '" . $i->img_name . "', 0,'" . $i->jsondata . "')";
    $db->query($sql);
    return $db->insert_id;
}

function save2file($str, $file) {
    $current = file_get_contents($file);
    $current .= $str . "\n";
    file_put_contents($file, $current);
}
