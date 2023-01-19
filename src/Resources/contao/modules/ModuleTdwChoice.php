<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package   wbgym 
 * @author    Erik Ziegler 
 * @copyright Webteam 2015 - 2016
 */


/**
 * Namespace
 */
namespace WBGym;


/**
 * Class ModuleTdwChoice 
 *
 * @copyright  Webteam 2015 
 * @author     Erik Ziegler 
 */

class ModuleTdwChoice extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_tdw_choice';
	protected $arrChoicesAmount = array
	(
		'5' => 5,
		'6' => 5,
		'7' => 6,
		'8' => 6,
		'9' => 7,
		'10' => 7,
		'11' => 7,
		'12' => 7,
	);

	/**
	 * Generate the module
	 */
	public function generate()
	{

		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### Tag der Wissenschaft Wahl ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	protected function compile()
	{
		$this->import('FrontendUser', 'User');
		$this->import('Database');

		if (!FE_USER_LOGGED_IN)
			return false;

		if ($_POST['FORM_SUBMIT'] == 'tdw-choice') {

			$arrChoices = array();

			foreach ($_POST as $key => $val) {
				if (strpos($key, 'lecture') !== false) {
					array_push($arrChoices, $val);
				}
			}

			if (count($arrChoices) == $this->arrChoicesAmount[WBGym::grade()]) {

				// Bereits gewählte Vorträge laden
				$rows = $this->Database->prepare("SELECT lecture FROM tl_tdwChoices WHERE student = ?")
					->execute($this->User->id)
					->fetchAllAssoc();


				$alreadyChosen = array();

				if (count($rows) > 0)
					foreach ($rows as $row)
						array_push($alreadyChosen, $row['lecture']);

				// Elemente aus den bereits gew�hlten Vortr�gen die nicht in den neu gew�hlten Enthalten sind, werden gel�scht!
				$delChoices = array_diff($alreadyChosen, $arrChoices);
				if (count($delChoices) > 0)foreach ($delChoices as $delChoice)
						$this->Database->prepare("DELETE FROM tl_tdwChoices WHERE student = ? AND lecture = ?")
							->execute($this->User->id, $delChoice); // Alle weitere Vortr�ge hinzuf�gen
				foreach ($arrChoices as $choice)
					if (!in_array($choice, $alreadyChosen))
						$this->Database->prepare("INSERT INTO tl_tdwChoices (tstamp,student,lecture) VALUES (?,?,?)")->execute(time(), $this->User->id, $choice);
				$this->Template->message = $GLOBALS['TL_LANG']['MSC']['success'];

			} else {
				$this->Template->error = $GLOBALS['TL_LANG']['MSC']['choice_amount_error'];
			}

		}

		//Get Student ID from Bicher List
		$strCsvFile = TL_ROOT . '/files/tdw/input_files/T_Schueler_fuer_Schuelerwahl.csv';
		if (file_exists($strCsvFile)) {
			$delimiter = ';';
			$handle = fopen($strCsvFile, 'r');
			$row = 1;
			while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
				if (
					$row > 1 &&
					$data[1] == $this->User->lastname &&
					$data[2] == $this->User->firstname &&
					$data[3] == date(
						'd.m.Y',
						$this->User->dateOfBirth
					)
				) {
					$stIdNew = $data[0];
				}
				$row++;
			}
		}

		//Find courses that the student visited last year
		$strCsvFile = TL_ROOT . '/files/tdw/input_files/T_Schueler_Themen_Vorjahr.csv';
		if (file_exists($strCsvFile)) {
			$delimiter = ';';
			$handle = fopen($strCsvFile, 'r');
			$row = 1;

			while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
				if ($data[0] == $stIdNew) {
					$arrLectionsLastYear[] = $data[1];
				}
				$row++;
			}
		}

		$arrAllLectures = $this->Database->prepare("SELECT * FROM tl_tdwLectures")->execute()->fetchAllAssoc();
		$arrLectures = array();

		foreach ($arrAllLectures as $lecture) {

			$bn = pow(2, WBGym::grade() - 5);

			$lecture['hasDescription'] = ($lecture['title'] != $lecture['description']);

			if (!empty($arrLectionsLastYear) && in_array($lecture['id'], $arrLectionsLastYear)) {
				$lecture['visitedLastYear'] = true;
			}

			if ($lecture['classes']& $bn) {
				$arrLectures[] = $lecture;
			}
		}

		$arrChoices = $this->Database->prepare("SELECT * FROM tl_tdwChoices WHERE student = ?")->execute($this->User->id)->fetchAllAssoc();

		$arrChoicesLectures = array();
		if (count($arrChoices) > 0)
			foreach ($arrChoices as $choice)
				array_push($arrChoicesLectures, $choice['lecture']);

		$this->Template->userMaxChoiceAmount = $this->arrChoicesAmount[WBGym::grade()];
		$this->Template->arrLectures = $arrLectures;
		$this->Template->arrChoices = $arrChoicesLectures;
		$this->Template->User = $this->User;

	}

}