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
 * Table tl_tdwLectures 
 */
$GLOBALS['TL_DCA']['tl_tdwLectures'] = array
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
			'fields'                  => array('title'),
			'flag'                    => 1,
			'panelLayout'			  => 'sort,search,limit',
		),
		'label' => array
		(
			'fields'                  => array('title','speaker','classes'),
			'label_callback'		  => array('tl_tdwLectures','replaceLabels'),
			'showColumns'             => true,
			'format'                  => '%s'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwLectures']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwLectures']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwLectures']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tdwLectures']['show'],
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
		'default'                     => '{title_header},title,speaker;{classes_header},classes;{desc_header},description' //speaker,blocks,gradeMin,gradeMax;'
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
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tdwLectures']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'search'				  => true,
			'sorting'				  => true,
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255,'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tdwLectures']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'search'				  => true,
			'eval'                    => array('mandatory'=>true,'rte'=>'tinyMCE'),
			'sql'                     => "varchar(5000) NOT NULL default ''"
		),
		'speaker' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_tdwLectures']['speaker'],
			'exclude'				  => true,
			'inputType'				  => 'text',
			'search'				  => true,
			'sorting'				  => true,
			'eval'					  => array('maxlength'=>255, 'tl_class' => 'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
                
		/*
		5 6 7 8 9  10 11 12
		--------------------
		1 2 4 8 16 32 64 128  
		
		Binärcode gibt an in welchen Klassen der Vortrag gehalten wird (Codes der Klassenstufe werden addiert)
		
		*/ 
		'classes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tdwLectures']['classes'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'load_callback'           => array(array('tl_tdwLectures','loadClassDistribution')),
			'options'                 => array("5", "6", "7","8","9", "10", "11", "12"),
			'save_callback'           => array(array('tl_tdwLectures','saveClassDistribution')),
			'eval'                    => array('mandatory'=>true,'multiple'=>true),
			'sql'                     => "int(10) NOT NULL default '0'"
		)
		/*
                
        ***********
        Entfällt anscheiennd alles
        ***********        
        */
                
        // maxAnzahl, Blöcke 
		
               
		/*
		
			Binärcode für die 3 Blöcke
			
			a = "1. Block" * 1 + "2. Block" * 2 + "3. Block" * 4;
			
			Überprüfung:
			if(a & x)		x := 1,2,4 (1->1.Block,2->2.Block,4->3.Block)
			
		*/
		/*
                
                // Die Blöcken werden zugeteilt, die Schüler wählen nur
                
		'blocks' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_tdwClasses']['blocks'],
			'explanation' 			  => &$GLOBALS['TL_LANG']['tl_tdwClasses']['blocksHelp'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => array('1','2','3','4','5','6','7'),
			'eval'					  => array('includeBlankOption' => true, 'helpwizard' => true,  'mandatory'=>true,'maxlength'=>2, 'tl_class' => 'w50'),
			'sql'                     => "tinyint NOT NULL default '1'"
		),
                
                
                // Entfällt da kein eindeutiges Maximum oder Mininum
                
		'gradeMin' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_tdwClasses']['gradeMin'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => array('5','6','7','8','9','10','11','12'),
			'eval'					  => array('mandatory'=>true, 'tl_class' => 'w50'),
			'sql'                     => "tinyint NOT NULL default '5'"
		),
		'gradeMax' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_tdwClasses']['gradeMax'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => array('5','6','7','8','9','10','11','12'),
			'eval'					  => array('mandatory'=>true, 'tl_class' => 'w50'),
			'sql'                     => "tinyint NOT NULL default '12'"
		)
                */
		
	)
);

class tl_tdwLectures extends Backend {
    
	public function replaceLabels($row, $label, DataContainer $dc, $args)  {
		$classes = $this->loadClassDistribution($row['classes']);
		for($i = 0; $i < count($classes)-1; $i++) {
			$strClasses .= $classes[$i] . ', ';
		}
		$strClasses .= $classes[count($classes)-1];
		$args[2] = $strClasses;
		return $args;
	}
    
    public function loadClassDistribution($intClasses) {
        
        $checked = array();
        
        for($i = 0; $i <= 7; $i++)
            if($intClasses & pow(2,$i)) array_push($checked, $i + 5);
        return $checked;
    }
    
    public function saveClassDistribution($arr, $dc) {
   
        $arr = deserialize($arr);
        $int = 0;
        
        for($i = 5; $i <= 12; $i++) 
            if (in_array($i, $arr))
                $int = $int + pow(2,$i - 5);

        return $int;
        
    }

}    
            
