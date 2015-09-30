<?php
/******************************************
* 
*  - 2010
*******************************************/
$season = 2015;
try
{
	// Connection-String erstellen
	$constr = sprintf("mysql:host=%s;port=%d;dbname=%s", 'localhost', '3306', 'buli');

	// Versuchen, eine DB-Verbindung herzustellen
	$db = new PDO($constr, 'root', 'root');
	
	// Errormode setzen
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
	// Fehler beim Verbinden -> Skript beenden
	// $logger->debug("Kann keine Verbindung zur Datenbank herstellen: " . $e->getMessage());
	die ($e->getMessage());
}
?>