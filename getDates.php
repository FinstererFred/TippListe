<?php

$feed_url = '';

include('db.class.php');

$from = (int)$_GET['from'];
$to = (int)$_GET['to'];

for($i = $from; $i <= $to; $i++)
{
	$url = $feed_url.$i."_onl1.json";

	$html = get_data($url);

	$test = json_decode($html);

	foreach($test->fixture as $key => $fix)
	{
		$date = explode("+", $fix->date);
		$sql =  "UPDATE events SET date='".$date[0]."' WHERE eventId=".$fix->eventId.";";
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}
}

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
