<?php

$username = "username";
$password = "password";
$serverUrl = "http://localhost:8080/partisRss/";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "http://www.partis.si/user/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array( 'user[username]'=>$username, 'user[password]'=>$password)));
curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, 'http://www.partis.si/brskaj/?rs=false&offset=0');
curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest", "Content-Type: text/html; charset=utf-8"));
curl_setopt($ch, CURLOPT_POST, 0);

$content = curl_exec($ch);

preg_match_all("/\/torrent\/prenesi\/([0-9]+)/", $content, $output_array);

header("Content-Type: text/xml; charset=ISO-8859-1");
$rssfeed = '<?xml version="1.0" encoding="ISO-8859-1"?>';
$rssfeed .= '<rss version="2.0">';
$rssfeed .= '<channel>';
$rssfeed .= '<title>Partis Rss Feed</title>';
$rssfeed .= '<link>http://www.partis.si</link>';
$rssfeed .= '<description>This is Partis Rss Feed</description>';
$rssfeed .= '<language>en-us</language>';

for($x=0; $x<count($output_array[0]);$x++)
{
	curl_setopt($ch, CURLOPT_URL, 'http://www.partis.si'.$output_array[0][$x]);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest", "Content-Type: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8"));
	$contentTorrent = curl_exec($ch);

	$destination = "torrenti/torrent".$x.".torrent";
	$file = fopen($destination, "w+");
	fputs($file, $contentTorrent);
	fclose($file);
	
	$startIndex = strpos($content, ">", strpos($content, "/torrent/podrobno/".$output_array[1][$x]));
	$endIndex = strpos($content, "</a>", $startIndex);
	
	$title = "<![CDATA[".substr($content, $startIndex+1, $endIndex-$startIndex-1)."]]>";
	$link = $serverUrl.$destination;
	
	$rssfeed .= '<item>';
    $rssfeed .= '<title>' . $title . '</title>';
    $rssfeed .= '<description></description>';
    $rssfeed .= '<link>' . $link . '</link>';
    $rssfeed .= '</item>';
}

curl_close($ch);

$rssfeed .= '</channel>';
$rssfeed .= '</rss>'; 
echo $rssfeed;

?>