<?php

include ('feedUrls.php');

if(isset($_GET['type'])) {

	if($_GET['type'] == 'table') { getTable((int)$_GET['matchday']); }

	if($_GET['type'] == 'injured')	{ getInjured(); }

	if($_GET['type'] == 'round') { echo getRound((int)$_GET['round']); }
}


function getRound($round, $type="xml") {
	global $feed1_url;
	$url = $feed1_url.$round."_onl1.".$type;

	$xml = get_data($url);

	return $xml;
}

function getTable($spieltag) {
	global $feed2_url;
	$url = $feed2_url.$spieltag.".xml?cb=".rand(100000,999999);

	$xml = get_data($url);

	echo $xml;
}


function getInjured()
{
	global $feed3_url;
	$html = get_data($feed3_url);

	$dom = new DOMDocument();

	@$dom->loadHTML($html);

	$xpath = new DOMXPath($dom);
	$bayern = $xpath->query("//*[@id='liste']//*[contains(@title,'FC Bayern')][1]/parent::div/following-sibling::*[2]/tbody")->item(0);

	$players = array();
	$i = 0;
	foreach($bayern->getElementsByTagName('tr') as $tr)
	{

		$players[$i]['typ'] = $tr->getElementsByTagName('img')->item(0)->getAttribute('title');
		$players[$i]['name'] = $tr->getElementsByTagName('td')->item(1)->nodeValue;
		$players[$i]['news'] = trim($tr->getElementsByTagName('a')->item(1)->nodeValue);
		$players[$i]['fehlt'] = trim($tr->getElementsByTagName('td')->item(3)->nodeValue);
		$i++;
	}
	echo json_encode($players);
}


$url = $feed4_url;

function get_data($url)
{
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

?>
