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
 
class tx_bpsshib_session {
	var $fileEx = ".bps_session";

	/* returns an array of session variables by given identifier ($fe_typo_user) */
	function getSessionVariables($fe_typo_user) {
		$filename = sys_get_temp_dir() . "/" . $fe_typo_user . $this->fileEx;
		if (file_exists($filename)) {
			$datei = fopen($filename, "r");
			return unserialize(file_get_contents($filename));
		} else {
			return null;
		}
	}
	/* set session variables (array $arr)for given identifier ($fe_typo_user) */
	function setSessionVariables($fe_typo_user, $arr) {
		$filename = sys_get_temp_dir() . "/" . $fe_typo_user . $this->fileEx;
		if (file_exists($filename)) {
			unlink($filename);
		}
		$datei = fopen($filename, "w");
		fwrite($datei, serialize($arr));
		fclose($datei);
	}
	/* delete session file by given identifier ($fe_typo_user) */
	function deleteSession($fe_typo_user) {
		$filename = sys_get_temp_dir() . "/" . $fe_typo_user . $this->fileEx;
		if (file_exists($filename)) {
			unlink($filename);
		}
		//$this->cleanupOldSessionFiles();
	}

	/*
	function cleanupOldSessionFiles() {
		$dir = sys_get_temp_dir();
		$files = array ();
		while ($file = readdir($dir)) {
			if (!is_dir($file) && $file != "." && $file != ".." && stripos($file, $this->fileEx) !== false) {
				$files[] = $file;
			}
		}
		closedir($dir);
		foreach ($files as $file) {
			if (date() - filemtime($file) > 600) {
				unlink($file);
			}

		}

	}
	*/

}
?>
