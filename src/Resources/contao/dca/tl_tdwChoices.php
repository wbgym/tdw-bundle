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
 * Table tl_twdChoices 
 */
$GLOBALS['TL_DCA']['tl_tdwChoices'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('student','lecture'),
			'flag'                    => 1,
			'panelLayout'			  => 'sort,filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('student','lecture'),
			'label_callback'		  => array('tl_tdwChoices','replaceLabels'),
            'showColumns'             => true
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwChoices']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwChoices']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwChoices']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwChoices']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Edit
	'edit' => array
	(
		'buttons_callback' => array()
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array(''),
		'default'                     => '{student_legend},student,lecture;'
	),

	// Subpalettes
	'subpalettes' => array
	(
		''                            => ''
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'student' => array
		(
            'label'                   => &$GLOBALS['TL_LANG']['tl_tdwChoices']['student'],
            'inputType'               => 'select',
            'foreignKey'              => 'tl_member.username',
			'options_callback'		  => array('WBGym\WBGym','studentList'),
			'search'				  => true,
			'sorting'                 => true,
			'eval'			  		  => array('search'=>true,'mandatory'=>true, 'tl_class' => 'w50','chosen'=>true),
			'sql'		         	  => "int(10) unsigned NOT NULL default '0'",
		),
		'lecture' => array
		(
            'label'                   => &$GLOBALS['TL_LANG']['tl_tdwChoices']['lecture'],
            'inputType'               => 'select',
            'foreignKey'              => 'tl_tdwLectures.title',
			'options_callback'		  => array('WBGym\WBGym','tdwLectureList'),
            'sorting'                 => true,
			'filter'				  => true,
			'search'				  => true,
			'eval'			  		  => array('mandatory'=>true, 'tl_class' => 'w50','chosen'=>true),
			'sql'			  		  => "int(10) unsigned NOT NULL"
		)
	)
);

class tl_tdwChoices extends Backend {
	
	public function replaceLabels($row, $label, DataContainer $dc, $args) {
		$args[0] = WBGym\WBGym::student($row['student']);
		$args[1] = WBGym\WBGym::tdwLecture($row['lecture']);
		return $args;
	}
	
}