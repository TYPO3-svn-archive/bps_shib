<?php
/* 
 	Einbindung in das Template:
	 	Template Datei im Filesystem suchen und an geeigneter Stelle folgendes eintragen:
	 	 	<!-- ###BPS_SHIB_LOGIN### start -->
	 		<!-- ###BPS_SHIB_LOGIN### end -->
 		Einbindung ins Typoscript:
 			page.10.subparts.BPS_SHIB_LOGIN = PHP_SCRIPT_EXT
			page.10.subparts.BPS_SHIB_LOGIN.file=typo3conf/ext/bps_shib/pi1/bps_shib_login.php
*/

## Letzten Login anzeigen: http://www.npostnik.de/typo3/letzen-login-anzeigen/
require_once ('class.tx_bpsshib_pi1.php');
$pi=new tx_bpsshib_pi1();

$content="Nutzername: ".$GLOBALS['TSFE']->fe_user->user["username"];
?>