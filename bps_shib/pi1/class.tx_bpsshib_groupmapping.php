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

    function compareShibAttributes($a1, $a2, $casesensitve)
    {
        if ($casesensitve)
        {
            if ($a1->name === $a2->name and $a1->value === $a2->value)
            {
                return true;
            }
        } else
        {
            if (strtolower($a1->name) === strtolower($a2->name) and strtolower($a1->value) === strtolower($a2->value))
            {
                return true;
            }
        }
        return false;
    }

    function isShibAttributeInArray($a1, $aarray, $casesensitve)
    {
        foreach ($aarray as $a2)
        {
            if ($this->compareShibAttributes($a1, $a2, $casesensitve))
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
                $xml_shibattribute->value = $shibattribute->getAttribute("isValue");
                if (!$this->isShibAttributeInArray($xml_shibattribute, $userAttibutes, false))
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