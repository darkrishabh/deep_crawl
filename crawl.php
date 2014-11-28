<?php

$BASE = "";
if($_GET && isset($_GET['url']) && $_GET['url']!=""){
    $BASE = $_GET['url'];
}
else{
    $BASE = "https://kamcord.com/developers";
}

include('crawler.php');

$crawlObject = new Crawler($BASE);
echo ( $crawlObject->start_crawl());

?>