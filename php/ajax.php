<?php

include('db.class.php');

if($_GET['action'] == 'saveRot')
{
	$sql = "INSERT into rounds (round, user, rot, season) values (:round,:user,:rot, :season) on duplicate key update rot = :rot";

	$stmt = $db->prepare($sql);
	
	$stmt->bindParam(':user', $_GET['user'], PDO::PARAM_INT);

	$stmt->bindParam(':rot', $_GET['spiel'], PDO::PARAM_INT);

	$stmt->bindParam(':round', $_GET['round'], PDO::PARAM_INT);
	
	$stmt->bindParam(':season', $season, PDO::PARAM_INT);
	
	$stmt->execute();
	$error = $stmt->errorInfo();


    if($error[0] != '00000' && $error[0] != '')
    {
        echo "Fehler: ".$error[2];
    }
}

if($_GET['action'] == 'saveTip')
{
	$sql = "INSERT into tips (eventId, user, tip".$_GET['typ'].", round, season) values (:eventId, :user,:tip,:round, :season) on duplicate key update tip".$_GET['typ']." = :tip";

	$stmt = $db->prepare($sql);
	
	$stmt->bindParam(':user', $_GET['user'], PDO::PARAM_INT);

	$stmt->bindParam(':tip', $_GET['tipp'], PDO::PARAM_INT);

	$stmt->bindParam(':round', $_GET['round'], PDO::PARAM_INT);
	
	$stmt->bindParam(':eventId', $_GET['eventId'], PDO::PARAM_INT);

	$stmt->bindParam(':season', $season, PDO::PARAM_INT);
	echo $sql.','
		.$_GET['eventId'].','
		.$_GET['user'].','
		.$_GET['tipp'].','
		.$_GET['round'].','
		.$season;
	
	$stmt->execute();
	$error = $stmt->errorInfo();


    if($error[0] != '00000' && $error[0] != '')
    {
        echo "Fehler: ".$error[2];
    }
}

if($_GET['action'] == 'saveBez')
{
	$sql = "INSERT into rounds (round, user, bezahlt, season) values (:round,:user,:bezahlt,:season) on duplicate key update bezahlt = :bezahlt";

	$stmt = $db->prepare($sql);
	
	$stmt->bindParam(':user', $_GET['user'], PDO::PARAM_INT);

	$stmt->bindParam(':round', $_GET['round'], PDO::PARAM_INT);
	
	$stmt->bindParam(':bezahlt', $_GET['bezahlt'], PDO::PARAM_INT);

	$stmt->bindParam(':season', $season, PDO::PARAM_INT);

	$stmt->execute();
	$error = $stmt->errorInfo();
	if($error[0] != '00000' && $error[0] != '')
    {
        echo "Fehler: ".$error[2];
    }
}

if($_GET['action'] == 'saveResult')
{
	$sql = "UPDATE events SET erg1 = :erg1, erg2 = :erg2, refreshed = now() where eventId = :eventId";

	$stmt = $db->prepare($sql);
	
	$stmt->bindParam(':erg1', $_GET['erg1'], PDO::PARAM_INT);
	
	$stmt->bindParam(':erg2', $_GET['erg2'], PDO::PARAM_INT);
	
	$stmt->bindParam(':eventId', $_GET['eventId'], PDO::PARAM_INT);

	$stmt->execute();
	$error = $stmt->errorInfo();
	if($error[0] != '00000' && $error[0] != '')
    {
        echo "Fehler: ".$error[2];
    }
}


if($_GET['action'] == 'savePoints')
{
	$sql = "UPDATE tips SET punkte = :punkte where user = :user and eventId = :eventId";

	$stmt = $db->prepare($sql);
	
	$stmt->bindParam(':punkte', $_GET['punkte'], PDO::PARAM_INT);
	
	$stmt->bindParam(':user', $_GET['user'], PDO::PARAM_INT);
	
	$stmt->bindParam(':eventId', $_GET['eventId'], PDO::PARAM_INT);

	$stmt->execute();
	$error = $stmt->errorInfo();
	if($error[0] != '00000' && $error[0] != '')
    {
        echo "Fehler: ".$error[2];
    }
}


?>