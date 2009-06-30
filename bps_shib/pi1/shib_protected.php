<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Ingmar Kroll <support@bps-system.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @author	Ingmar Kroll <support@bps-system.de>
 */
 
require_once ('class.tx_bpsshib_session.php');
$fe_typo_user = $_COOKIE["fe_typo_user"];
if ($fe_typo_user == null) {
	die("Direct access is not allowed. Please use Typo3 frontend.");
}
$session = new tx_bpsshib_session();
$array = $session->getSessionVariables($fe_typo_user);
if(!is_array($array)){
	die("Sorry, an error occurred, please try again.");
}
$arrayback=array_merge($array,$_SERVER);
$arrayback["isAuth"] = true;
$session->setSessionVariables($fe_typo_user, $arrayback);
Header('Location: ' . $arrayback["backLink"]);


?>
