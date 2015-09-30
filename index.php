<?php

	include("php/db.class.php");
	include("php/proxy.php");

	$jokerTipp = true;

	$test = get_data($url);
	$season = 2015;
	$xml = new SimpleXMLElement($test);
	foreach($xml->fixture as $fixture)
	{
		if($fixture['status'] == 'PRE-MATCH')
		{
			$actSpieltag = $fixture['round'];
			break;
		}
	}

	checkResults($actSpieltag, $season);

	function checkResults($actSpieltag, $season) {
		global $db;
		$sql = "SELECT count(*) as cnt from events where round = :actSpieltagM1 and season = :season and refreshed = 0";
		$stmt = $db->prepare($sql);
		$prevSpieltag = $actSpieltag-1;
		$stmt->bindParam(':actSpieltagM1', $prevSpieltag, PDO::PARAM_INT);
		$stmt->bindParam(':season', $season, PDO::PARAM_INT);
		$stmt->execute();

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$result = $result[0]['cnt'];
		if ($result > 0) {
			updateSpieltag($prevSpieltag);
		}

	}

	function updateSpieltag($spieltag) {
		global $db;
		$json = json_decode(getRound($spieltag, 'json'));
		foreach($json->fixture as $fixture) {
			$eventid = $fixture->eventId;
			$erg1 =  $fixture->teamHome->score->total;
			$erg2 =  $fixture->teamAway->score->total;

			$sql = "UPDATE events SET erg1 = :erg1, erg2 = :erg2, refreshed = :refreshed WHERE eventId = :eventId ";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':erg1', $erg1, PDO::PARAM_INT);
			$stmt->bindParam(':erg2', $erg2, PDO::PARAM_INT);
			$stmt->bindParam(':refreshed', $spieltag, PDO::PARAM_INT);
			$stmt->bindParam(':eventId', $eventid, PDO::PARAM_INT);
			$stmt->execute();
		}
	}
//	$actSpieltag = 0;

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset=utf-8 />
	<title></title>
	<style>
		body { background:url(img/bg.jpg) #538f15; font-family: Arial, Sans-Serif}
		th img { width: 25px; height: 25px;}
		th img:first-child { margin-right:2px;}
		th { padding:10px 15px; white-space: nowrap; cursor: pointer;}
		th.spieltag { text-align: left}
		td { padding:10px; text-align: center; white-space: nowrap;}
		td input { width: 15px; border:0px;text-align: center}
		td.name{ text-align: left}
		.punkte {}
		.erg  { font-weight: bold}
		.erg input { font-weight: bold}
		.rot { color:red;}
		.rot input { color:red;}
		.tipTR:hover { background-color: #cdcdcd}

		.tipTR { display: none }
		#div<?=$actSpieltag?> .tipTR { display: table-row; }

		.tbl { background-color:rgba(255, 255, 255, 0.8); margin:0px auto; margin-bottom: 10px; border:0px; border-spacing:0px; border-collapse: collapse;}
		small { font-size: 12px; font-weight: normal }

		.scrollTop { position: fixed; bottom: 20px; right: 20px; width: 20px; height: 20px; background: white; text-align: center; line-height: 25px; font-weight: bold;cursor: pointer}

		.tagfarbe1 { border-bottom:4px solid #555555 }
		.tagfarbe2 { border-bottom:4px solid #ffffff }
		.tagfarbe3 { border-bottom:4px solid #60b200 }
		.rotKnopf {background-color: rgba(0,0,0,0.5); height: 11px; width: 11px; display: inline-block; margin-right: 5px; border-radius: 5px; cursor: pointer; position: relative;top:1px;}
		.rot .rotKnopf {background-color: rgba(255,0,0,0.5);cursor: default;}
		.tableLink { position: absolute; bottom:28px; right: 77px;background-color:rgba(255, 255, 255, 0.8);  padding:5px; }
		.tableLink a { color:black; text-decoration: none }
		.headerDiv { margin:0px auto; width: 1271px; position: relative; }
		.headerDiv table { background-color:rgba(255, 255, 255, 0.8); margin-bottom: 10px; border:0px; border-spacing:0px; border-collapse: collapse; display: inline-block; margin-right: 15px}

		.vTeam { position: relative; width: 95px; }
		.vPunkte { color:white; position: absolute; bottom:0.6rem; left: 0rem; font-size: 2.5rem;text-shadow: 0px 0px 6px rgba(0, 0, 0, 1);}
		.vLogo img { width: 100%; height: 100% }
		.vLogo {position: relative; }
		.spieltagDv {z-index:1; position: relative;}

		/* #tableList { position: absolute; top:0x; left:30px; z-index: 0 } */
		#tableList { width: 430px; display: inline-block; position: relative; top:-12px;}
		#tableList .vTeam { display: inline-block; width: 80px; margin-right: 5px }

		.pointsName { text-align: left; }

		#tableInjured { display: inline-block; position: relative; top:0px; width: 529px; margin-right:0px;    vertical-align: top;}
		#tableInjured td,tr {text-align: left;}
		#tableInjured td.tage {text-align: right;}

		.dayWinner {background-color: pink;}

		.highTeam {background-color: black;}
	</style>
</head>
<body>

	<div class="headerDiv">

		<table id="points"><tr><th>Name</th> <th>Punkte</th> <th>Platz</th> <th>&euro;</th></tr></table>
		<div id="tableList"><div class="tableLink"><a href="http://www.bundesliga.de/de/liga/tabelle/" target="_blank">zur Bl. Tabelle</a></div></div>
		<table id="tableInjured"><tr><th>Art</th><th>Name</th><th>News</th><th>fehlt seit</th></tr></table>
	</div>

<?php
	$teams = array(
				"t156"	=> "Bayern",
				"t172"	=> "Wolfsburg",
				"t159"	=> "Frankfurt",
				"t160"	=> "Freiburg",
				"t162"	=> "Berlin",
				"t171"	=> "Bremen",
				"t387"	=> "Köln",
				"t161"	=> "Hamburg",
				"t808"	=> "Hannover",
				"t167"	=> "Schalke",
				"t1902"	=> "Hoffenheim",
				"t1772"	=> "Augsburg",
				"t157"	=> "Dortmund",
				"t164"	=> "Leverkusen",
				"t1743"	=> "Paderborn",
				"t810"	=> "Mainz",
				"t683"	=> "Gladbach",
				"t169"	=> "Stuttgart",
				"t2743" => "Ingolstadt",
				"t1756" => "Darmstadt");

	$spieldatum = -1;
	$farbe = 0;
	$datumOld = '';
	$knownRound = 16;


	$sql = "SELECT * FROM events WHERE season = :season order by date ASC";
		$stmt = $db->prepare($sql);

		$stmt->bindParam(':season', $season, PDO::PARAM_INT);

		$stmt->execute();

		$events = array();

		while ($temp = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$events[] = $temp;
		}

	$sql = "SELECT t.*,u.name from tips t left join user u on t.user = u.id where t.season = :season";
		$stmt = $db->prepare($sql);

		$stmt->bindParam(':season', $season, PDO::PARAM_INT);
		$stmt->execute();

		$tips = array();

		while ($temp = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$tips[$temp['round']][$temp['user']][$temp['eventId']] = $temp;
		}

	$sql = "SELECT * from user";
		$stmt = $db->prepare($sql);
		$stmt->execute();

		$users = array();

		$userJS = 'var userNames=[';
		while ($temp = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$users[$temp['id']]= $temp['name'];
			$userJS .= "'".$temp['name']."',";
		}
		$userJS .= '];';



	$sql = "SELECT * from rounds where season = :season";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':season', $season, PDO::PARAM_INT);

		$stmt->execute();

		$rounds = array();

		while ($temp = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rounds[$temp['round']][$temp['user']] = $temp;
		}


	for($spieltag = 1; $spieltag <= 34; $spieltag++) {

		if($spieltag < $knownRound) {
			$farbe = 0;
		}

		$temptip = array();
		$spieldatum = -1;

		foreach ($events as $key => $event) {

			if($event['round'] == $spieltag) {

				$locdate = date('d.m.Y', strtotime($event['date']));

				if( $spieldatum < 0 ) {
					$spieldatum = $locdate;
				}

				if($datumOld != $locdate) {
					$farbe++;
					$datumOld=$locdate;
				}

				$events[$key]['hgfarbe'] = $farbe;
			}
		}


		echo '<div id="div'.$spieltag.'"  class="spieltagDv"><table class="tbl" id="round'.$spieltag.'" data-spieltag="'.$spieltag.'"><tr class="tableHeader"> <th class="spieltag">'. $spieltag .' <small>'.$spieldatum.'</small></th>';
		$erg = '';
		$i = 1;

		foreach ($events as $key => $event) {

			if($event['round'] == $spieltag) {

				echo '<th data-eventId="'.$event['eventId'].'" class="begegnung tagfarbe'.$event['hgfarbe'].'"><img data-teamid="'.$event['team1'].'" class="'.$event['team1'].'" src="img/'.$event['team1'].'.png" title="'.$teams[$event['team1']].'"/> <img data-teamid="'.$event['team2'].'" class="'.$event['team2'].'" src="img/'.$event['team2'].'.png" title="'.$teams[$event['team2']].'"/></th><th></th>';
				$erg1 = ($event['erg1'] != -1) ?  $event['erg1'] : 'x';
				$erg2 = ($event['erg2'] != -1) ?  $event['erg2'] : 'x';
				$erg .= '<td>'.$erg1.' - '.$erg2.'</td><td></td>';

				foreach ($users as $key => $value) {

					if (!isset($temptip[$key])) {
						$temptip[$key] = '';
					}

					if (!isset($temptip[$key]['content'])) {
						$temptip[$key]['content'] = '';
					}

					if (!isset($temptip[$key]['bezahlt'])) {
						$temptip[$key]['bezahlt'] = 0;
					}

					if (!isset($temptip[$key]['points'])) {
						$temptip[$key]['points'] = 0;
					}

					$rot = ( isset($rounds[ $spieltag ][ $key ]['rot']) && $rounds[ $spieltag ][ $key ]['rot'] == $i) ? 'class="tipTD rot"' : 'class="tipTD"';

					if($jokerTipp) {
						$rotKnopf = '<div class="rotKnopf"></div>';
					} else {
						$rotKnopf = '';
						$rot = 'class="tipTD"';
					}

					if ( isset($tips[ $spieltag ][$key][ $event['eventId'] ]) ) {
						$_tip = $tips[ $spieltag ][$key][ $event['eventId'] ];

						if($spieltag < $actSpieltag) {
							$temptip[ $key ]['content'] .= '<td  data-spiel="'.$i.'" '.$rot.' data-eventId="'.$event['eventId'].'">'.$_tip['tip1'].' - '.$_tip['tip2'].'</td><td td class="matchpoints" data-spiel="'.$i.'">'.$_tip['punkte'].'</td>';
						} else {
							$temptip[ $key ]['content'] .= '<td  data-spiel="'.$i.'" '.$rot.' data-eventId="'.$event['eventId'].'">'.$rotKnopf.'<input type="tel" value="'.$_tip['tip1'].'" /> - <input type="tel" value="'.$_tip['tip2'].'" /></td><td class="matchpoints" data-spiel="'.$i.'">0</td>';
						}

					} else {

						if ($spieltag >= $actSpieltag) {
							$temptip[ $key ]['content'] .= '<td data-spiel="'.$i.'" '.$rot.' data-eventId="'.$event['eventId'].'">'.$rotKnopf.'</div><input type="tel" value="x" /> - <input type="tel" value="x" /></td><td class="matchpoints" data-spiel="'.$i.'">0</td>';
						} else {
							$temptip[ $key ]['content'] .= '<td data-spiel="'.$i.'" '.$rot.' data-eventId="'.$event['eventId'].'">x - x</td><td class="matchpoints" data-spiel="'.$i.'">0</td>';
						}
					}

					$temptip[ $key ]['points'] += 0;
					$temptip[ $key ]['bezahlt'] = isset($rounds[ $spieltag ][ $key ]['bezahlt']) ?  $rounds[ $spieltag ][ $key ]['bezahlt'] : 0;
				}

				$i++;

			}
		}

		echo '<th>bez</th> <th class="punkteTH">Pkt</th></tr>';

		foreach ($temptip as $key => $value) {
			echo "<tr class='tipTR' data-user='".$key."'>";
			echo "<td class='name'>".$users[$key]."</td>";
			echo $value['content']."";

			$bezahlt = ($value['bezahlt']  == 1) ? ' checked="checked" disabled="disabled"' : '';

			echo "<td><input type='checkbox' ".$bezahlt."></td><td class='punkte'>".$value['points']."</td></tr>";
		}

		echo '<tr class="erg"> <td class="results">Erg</td>'.$erg.'<td></td> <td></td> </tr>';
		echo "</table></div>";
	}


?>
	<div class="scrollTop">^</div>
	<script>
		var actSpieltag = <?=$actSpieltag?>;
		<?=$userJS?>
	</script>

	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/functions.js?new=29"></script>

</body>
</html>
