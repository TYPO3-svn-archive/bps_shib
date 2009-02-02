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
 * Plugin 'shibboleth' for the 'bps_shib' extension.
 *
 * @author	Ingmar Kroll <support@bps-system.de>
 */

require_once (PATH_tslib . 'class.tslib_pibase.php');
require_once ('class.tx_bpsshib_session.php');
class tx_bpsshib_pi1 extends tslib_pibase {
	var $prefixId = 'tx_bpsshib_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_bpsshib_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'bps_shib'; // The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		//Cache off
		$GLOBALS["TSFE"]->set_no_cache();
		$bps_s = new tx_bpsshib_session();

		$sessTypo = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'bps_shib_auth');
		$isAuthTypo = $sessTypo['isAuth'];
		$sessPHP = $bps_s->getSessionVariables($_COOKIE["fe_typo_user"]);
		$isAuthPHP = $sessPHP['isAuth'];
		
		
		if(t3lib_div::GPVar("shiblogout") == "1"){
			$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_auth', null);
			$GLOBALS["TSFE"]->storeSessionData();
			$blackUrl=str_replace("&logintype=logout","",$this->getUrl());
			t3lib_div::devLog("user logged out",$this->extKey);
			Header('Location: '.$blackUrl);
		}	

		if ($isAuthTypo==null || $isAuthTypo != true) {
			if ($isAuthPHP != null &&  $isAuthPHP == true) {
				//Nutzer wurde von Shib-geschützer Seite nach hier weitergeleitet
				t3lib_div::devLog("user is authenticated",$this->extKey);
				$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_auth', array("isAuth" => true));
				$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_data', $sessPHP);
				$GLOBALS["TSFE"]->storeSessionData();
				$bps_s->deleteSession($_COOKIE["fe_typo_user"]);
				$isAuthTypo=true;
				
			} else {
				//Nutzer hat keine Auth und muss zur Shib-Seite geleitet werden
				t3lib_div::devLog("offer redirect 2 shib",$this->extKey);
				$array["backLink"] = $this->getUrl();
				$bps_s->setSessionVariables($_COOKIE["fe_typo_user"], $array);
				#Header('Location: '.t3lib_div::locationHeaderUrl("/typo3conf/ext/bps_shib/pi1/shib_protected.php"));
				#$content="redirect to ".t3lib_div::locationHeaderUrl("/typo3conf/ext/bps_shib/pi1/shib_protected.php")." faild!";
				$content='
				<form action="'.t3lib_div::locationHeaderUrl("/typo3conf/ext/bps_shib/pi1/shib_protected.php").'" method="POST">
                	<input type="hidden" name="no_cache" value="1">
                	<input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL('login_button_label')).'">
            	</form>
				';
				return $this->pi_wrapInBaseClass($content);
			}
		}
		if ($isAuthTypo==true && $isAuthPHP == true){
			$sessPHP = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'bps_shib_data');
			t3lib_div::devLog("load attributes from session",$this->extKey);
			if($this->check_bps_shib_configuration()==false){
				t3lib_div::devLog("configuration error",$this->extKey);
				$content="please update bps_shib configuration!";
				$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_auth', array("isAuth" => false));
				$GLOBALS["TSFE"]->storeSessionData();
				return $this->pi_wrapInBaseClass($content);
			}else{
				// Konfiguration ok 
				$userdata = $this->getShibUserData();
				$username = $userdata['username'];
				
				if (!$this->userExists($username)){
					//Nutzer muss angelegt werden
					$this->import_singleuser($username);
				}else{
					//Nutzerdaten updaten
					$this->update_singleuser($username);
				}
				//versuche Login
				if ($this->userLogin($username) == true){
					//wird nicht angezeigt, da redirect
					$content=$content = $this->pi_getLL('login_info_ok');
					//damit die Navigation erneuert wird
					Header('Location: '.$this->getUrl());
				}else{
					$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_auth', null);
					$GLOBALS["TSFE"]->fe_user->setKey('ses', 'bps_shib_data', null);
					$GLOBALS["TSFE"]->storeSessionData();
					$content=$content . $this->pi_getLL('login_info_faild');
				}
				
			}
			
		}
		
		$content=$content .$this->pi_getLL('logout_info').'
				<form action="'.$this->getUrl().'&logintype=logout" method="POST">
                	<input type="hidden" name="no_cache" value="1">
                	<input type="hidden" name="shiblogout" value="1">
                	<input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL('logout_button_label')).'">
            	</form>
				';
		return $this->pi_wrapInBaseClass($content);
	}
	
	function check_bps_shib_configuration(){
			t3lib_div::devLog("enter check_bps_shib_configuration()",$this->extKey);
			//Auslesen der Konfiguration -- siehe ext_conf_template.txt
			$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bps_shib']);
			return($_extConfig!=null);
	}
	
	function getShibUserData(){
		t3lib_div::devLog("enter getShibUserData()",$this->extKey);
		$sessPHP = $GLOBALS["TSFE"]->fe_user->getKey('ses', 'bps_shib_data');
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bps_shib']);
		$username = $sessPHP[$_extConfig['username']];
		$email = $sessPHP[$_extConfig['email']];
		//optional
		$name="";
		if($_extConfig['sn']!=null){
			if($sessPHP[$_extConfig['sn']]!=null){
				$name=$sessPHP[$_extConfig['sn']];
			}
		}
				
		if($_extConfig['gn']!=null){
			if($sessPHP[$_extConfig['gn']]!=null){
				$name=$name . " ".$sessPHP[$_extConfig['gn']];
			}
		}

		$userdata=array('username' => $username,'email'=>$email,'name'=>$name);
		
		return $userdata;
	}
	
	function getUrl(){
			$thisserver=(isset($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'];
			$url = $thisserver . $_SERVER['REQUEST_URI']; 
			return $url;
	}
	
	function userLogin($username){
			t3lib_div::devLog("enter userLogin(".$username.")",$this->extKey);
			// Login user
			$loginData=array(
			        'uname' => $username,
					'uident'=> "85ae4c74b5f6ab15d6e9d19014c6bc65",
			        'status' =>'login'
			);
			$GLOBALS['TSFE']->fe_user->checkPid = FALSE;
			$info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
			$user = $GLOBALS['TSFE']->fe_user->fetchUserRecord( $info['db_user'],
			$loginData['uname'] );
			$ok=$GLOBALS['TSFE']->fe_user->compareUident( $user, $loginData );
			if( $ok ) {
			        $GLOBALS['TSFE']->fe_user->createUserSession( $user );
			        $GLOBALS['TSFE']->loginUser = 1;
			        $GLOBALS['TSFE']->fe_user->start();
			        t3lib_div::devLog("login successfull for ".$username,$this->extKey);
			}else{
				t3lib_div::devLog("login faild for ".$username,$this->extKey,3);
			}
			return $ok;
	}
	
 	
 	//existiert der FE- Nutzer?
 	function userExists($username,$user_table="fe_users"){
 		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bps_shib']);
		$pid=$_extConfig['sysfolderpid'];
 		$query = (($pid)?'pid ='.$pid.' AND ':'')."NOT deleted AND lower(username) = '".$GLOBALS['TYPO3_DB']->quoteStr(strtolower($username),$user_table)."'";
 		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $user_table, $query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return (is_array($row));
 	}
 
	function import_singleuser($username,$user_table="fe_users") {
		t3lib_div::devLog("enter import_singleuser(".$username.")",$this->extKey);
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bps_shib']);
		$pid=$_extConfig['sysfolderpid'];
		$usergroup=$_extConfig['usergroupid'];
		$query = (($pid)?'pid ='.$pid.' AND ':'')."NOT deleted AND lower(username) = '".$GLOBALS['TYPO3_DB']->quoteStr(strtolower($username),$user_table)."'";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('email', $user_table, $query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if (!is_array($row) && ($username != '')){
			//Nutzer nicht gefunden , kann angelegt werden
			$userdata = $this->getShibUserData();
			//debugster($userdata);
			$username = $userdata['username'];
			$insValues=array('crdate' => time(),
	               	'tstamp' => time(),
	                'pid'=> $pid,
					'username' => str_replace("'", "''", $username),
					'name' => str_replace("'", "''",$userdata['name']),
					'email' => $userdata['email'],
					'usergroup' => $usergroup,
					'password' => "85ae4c74b5f6ab15d6e9d19014c6bc65"
					);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery($user_table,$insValues);
		}
	}		
			
		
	
		
		
	function update_singleuser($username, $user_table= 'fe_users') {
		t3lib_div::devLog("enter update_singleuser(".$username.")",$this->extKey);
		$_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bps_shib']);
		$pid=$_extConfig['sysfolderpid'];
		$usergroup=$_extConfig['usergroupid'];
		$userdata = $this->getShibUserData();
		$username = $userdata['username'];
		$updateArray=array(
	                'pid'=> $pid,
					'username' => str_replace("'", "''", $username),
					'name' => str_replace("'", "''",$userdata['name']),
					'email' => $userdata['email'],
					'usergroup' => $usergroup,
					'password' => "85ae4c74b5f6ab15d6e9d19014c6bc65"
					);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($user_table,"lower(username) = '".strtolower($GLOBALS['TYPO3_DB']->quoteStr($username,$user_table))."' AND pid=".$pid,$updateArray);
	
	}
		
	
	
}	

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bps_shib/pi1/class.tx_bpsshib_pi1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bps_shib/pi1/class.tx_bpsshib_pi1.php']);
}
?>