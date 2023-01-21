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
 * Namespace
 */
namespace WBGym;

/**
 * Class ModuleTdwChoiceAdmin 
 *
 * @copyright  Webteam 2015  
 * @author     Erik Ziegler 
 * @package    Devtools
 */
class ModuleTdwChoiceAdmin extends \BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_tdw_choice_admin';
	protected $csvSeperator = ';';


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$this->getStats();
		if (strlen(\Input::post('storeChoicesInCsvFile'))) {
			$this->storeChoicesInCsvFile();
			$this->Template->message = 'Export der Wünsche erfolgreich!';
		}
		if (strlen(\Input::post('importLecturesFromCsv'))) {
			$this->importLecturesFromCsv();
			$this->Template->message = 'Import der Vorträge erfolgreich!';
		}
		if (strlen(\Input::post('copyLecturesToArticle'))) {
			$this->copyLecturesTo(\Input::post('copyArticle'));
			$this->Template->message = 'Kopieren der Vorträge erfolgreich!';
		}

		$this->Template->pages = $this->getArticles();
		$this->Template->lectureCount = $this->lectureCount;
		$this->Template->studentsHaveChosen = $this->studentsHaveChosen;
		$this->Template->studentsHaveToChoose = $this->studentsHaveToChoose;
	}

	protected function getStats()
	{
		//Get number of different lectures
		$this->lectureCount = $this->Database->prepare("SELECT count(*) FROM tl_tdwLectures")->execute()->fetchRow()[0];

		//Get number of Students who have already chosen
		$this->studentsHaveChosen = $this->Database->prepare("SELECT count(DISTINCT student) FROM tl_tdwChoices")->execute()->fetchRow()[0];

		//Get number of Students who have to choose altogether
		$strCsvFile = TL_ROOT . '/files/tdw/input_files/T_Schueler_fuer_Schuelerwahl.csv';
		if (file_exists($strCsvFile)) {
			$handle = fopen($strCsvFile, 'r');
			$count = 0;
			$row = 1;
			while (($data = fgetcsv($handle, null, $this->csvSeperator)) !== false) {
				if ($row > 1) {
					$count++;
				}
				$row++;
			}
			$this->studentsHaveToChoose = $count;
		}
	}

	protected function storeChoicesInCsvFile()
	{

		//build array which assigns every student id from our DB the new ID from CSV
		$students = $this->Database->prepare("SELECT id,firstname,lastname,dateOfBirth FROM tl_member WHERE student")->execute();

		while ($student = $students->fetchAssoc()) {

			$strCsvFile = TL_ROOT . '/files/tdw/input_files/T_Schueler_fuer_Schuelerwahl.csv';
			if (file_exists($strCsvFile)) {
				$handle = fopen($strCsvFile, 'r');
				$row = 1;

				while (($data = fgetcsv($handle, null, $this->csvSeperator)) !== false) {
					if (
						$row > 1 &&
						$data[1] == $student['lastname'] &&
						/*$data[2] == $student['firstname'] && */
						$data[3] == date(
							'd.m.Y',
							$student['dateOfBirth']
						)
					) {
						$arrStIds[$student['id']] = $data[0];
					}
					$row++;
				}
			}
		}

		//write every choice into new file
		$choices = $this->Database->prepare("SELECT student, lecture FROM tl_tdwChoices")->execute();
		$strData = implode($this->csvSeperator, array('Schueler', 'Vortrag')) . "\n";

		while ($choice = $choices->fetchAssoc()) {

			$studentId = $choice['student'];
			$lectureId = $choice['lecture'];

			if ($arrStIds[$studentId]) {
				$strData .= implode($this->csvSeperator, array($arrStIds[$studentId], $lectureId)) . "\n";
			}
		}

		$choicesCsvFile = fopen(TL_ROOT . '/files/tdw/export_files/choices.txt', 'w');
		fwrite($choicesCsvFile, $strData);
		fclose($choicesCsvFile);
	}

	protected function storeStudentsInCsvFile()
	{

		$students = $this->Database->prepare("SELECT id, firstname, lastname FROM tl_member WHERE student")->execute();

		$strData = implode($this->csvSeperator, array('Id', 'Name')) . "\n";

		while ($student = $students->fetchAssoc()) {


			$strData .= implode($this->csvSeperator, array($student['id'], $student['firstname'] . ' ' . $student['lastname'])) . "\n";

		}

		$objFile = new \File('files/tdw/export_files/students.csv');
		$objFile->write($strData);

	}

	protected function storeLecturesInCsvFile()
	{

		$lectures = $this->Database->prepare("SELECT id, title, description FROM tl_tdwLectures")->execute();

		$strData = implode($this->csvSeperator, array('Id', 'Titel', 'Beschreibung')) . "\n";

		while ($lecture = $lectures->fetchAssoc()) {


			$strData .= implode($this->csvSeperator, array($lecture['id'], $lecture['title'], $lecture['description'])) . "\n";

		}

		$objFile = new \File('files/tdw/export_files/lectures.csv');
		$objFile->write($strData);

	}

	protected function importLecturesFromCsv()
	{
		$this->Database->prepare("DELETE FROM tl_tdwLectures")->execute();
		$row = 1;

		$strCsvFile = TL_ROOT . '/files/tdw/input_files/T_Vortragsthemen_fuer_Schuelerwahl.csv';

		$handle = fopen($strCsvFile, 'r');

		while (($data = fgetcsv($handle, null, $this->csvSeperator)) !== false) {

			$num = count($data);

			if ($row > 1) {

				$bn = 0;
				$arrColumns = array();

				for ($c = 0; $c < $num; $c++)
					array_push($arrColumns, $data[$c]);

				/*Neue Variante mit Speaker*/
				for ($i = 0; $i < 8; $i++)
					$bn = $bn + pow(2, $i) * !!$arrColumns[$i + 4];

				$lectureId = $arrColumns[0];
				$lectureTitle = $arrColumns[1];

				/*Neue Variante mit Speaker*/
				$lectureSpeaker = $arrColumns[2];
				$lectureDescription = $arrColumns[3];

				$this->Database->prepare("INSERT INTO tl_tdwLectures (id, tstamp, title, speaker, description, classes) VALUES (?,?,?,?,?,?)")
					->execute($lectureId, time(), $lectureTitle, $lectureSpeaker, $lectureDescription, $bn);
			}

			$row++;
		}
	}

	protected function copyLecturesTo($articleId)
	{
		$intSort = 0;
		$lectures = $this->Database->prepare("SELECT * FROM tl_tdwLectures ORDER BY title")->execute();
		while ($arrLecture = $lectures->fetchAssoc()) {
			$intSort += pow(2, 7);

			if ($arrLecture['speaker'])
				$strHeadline = $arrLecture['speaker'] . ': ' . $arrLecture['title'];
			else
				$strHeadline = $arrLecture['title'];

			$this->Database->prepare("INSERT INTO tl_content (pid, type, sorting, tstamp, mooHeadline, text, ptable) VALUES (?,?,?,?,?,?,?)")
				->execute($articleId, 'accordionSingle', $intSort, time(), $strHeadline, $arrLecture['description'], 'tl_article');
		}
	}

	protected function getArticles()
	{
		$articles = $this->Database->prepare("
			SELECT p.id AS pageId, a.id AS articleId, p.title AS pageTitle, a.title AS articleTitle, a.inColumn 
			FROM tl_page p JOIN tl_article a ON p.id = a.pid WHERE type = ? AND a.inColumn = ? ORDER BY p.pid, p.id, a.sorting; 
		")->execute('regular', 'main');

		while ($arrArticle = $articles->fetchAssoc()) {
			$arrArticles[$arrArticle['pageTitle']][$arrArticle['articleId']] = array('id' => $arrArticle['articleId'], 'title' => $arrArticle['articleTitle']);
		}
		return $arrArticles;
	}
}