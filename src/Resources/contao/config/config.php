<?php 

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package   wbgym 
 * @author    Erik Ziegler 
 * @license   wbgym 
 * @copyright Webteam 2015 
 */
 
 /**
 * Backend Modules
 */
 
$GLOBALS['TL_CSS'][] = 'bundles/wbgymtdw/style.css';
 
array_insert($GLOBALS['BE_MOD'], 2, array('tdw' => array(
 
	'tdwChoiceAdmin' => array(
		'callback' => 'WBGym\ModuleTdwChoiceAdmin',
	),
	'tdwLectures' => array(
		'tables' => array('tl_tdwLectures'),
	),
	'tdwChoices' => array(
		'tables' => array('tl_tdwChoices'),
	)

)));

/**
* Frontend Modules
*/

$GLOBALS['FE_MOD']['tdw'] = array
(
	'tdwChoice' => 'WBGym\ModuleTdwChoice'
);