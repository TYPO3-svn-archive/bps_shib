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
require_once ('class.tx_bpsshib_shibattribute.php');

class tx_bpsshib_groupmapping
{

    function compareShibAttributes($xml_shibattribute, $userAttibute)
    {

		if (strtolower($xml_shibattribute->name) === strtolower($userAttibute->name)){
			
	        if (strtolower($xml_shibattribute->casesensitve)!=="true"){
	        	$xml_shibattribute->value =strtolower($xml_shibattribute->value);
	        	$userAttibute->value=strtolower($userAttibute->value);
	        }
	        switch (strtolower($xml_shibattribute->operator)){
	        	case "is":
			        return $xml_shibattribute->value === $userAttibute->value;
			    case "contains":
			    	return strpos($userAttibute->value,$xml_shibattribute->value)!==false;
		        default:
    				return false;
	        }
	        
	        
	  	}else{
			return false;
		}

    }

    function isShibAttributeInArray($xml_shibattribute, $userAttibutes)
    {
        foreach ($userAttibutes as $userAttibute)
        {
            if ($this->compareShibAttributes($xml_shibattribute, $userAttibute))
            {
                return true;
            }
        }
        return false;
    }

    function getGroupIDs($userAttibutes)
    {
        $doc = new DOMDocument();
        $doc->load('typo3conf/ext/bps_shib/shibgroupmapping.xml');
        $groups = $doc->getElementsByTagName("group");
        $rvalue = "";
        foreach ($groups as $group)
        {
            $xml_group_id = $group->getAttribute("id");
            $passed = true;
            $shibattributes = $group->getElementsByTagName("shibattribute");
            foreach ($shibattributes as $shibattribute)
            {
                $xml_shibattribute = new tx_bpsshib_shibattribute();
                $xml_shibattribute->name = $shibattribute->getAttribute("name");
                $xml_shibattribute->value = $shibattribute->getAttribute("value");
                $xml_shibattribute->operator = $shibattribute->getAttribute("operator");
                  $xml_shibattribute->casesensitve = $shibattribute->getAttribute("casesensitve");
                if (!$this->isShibAttributeInArray($xml_shibattribute, $userAttibutes))
                {
                    $passed = false;
                }
            }
            if ($passed)
            {
                $rvalue=$rvalue.",".$xml_group_id;
            }

        }

        return $rvalue;
    }

}
?>