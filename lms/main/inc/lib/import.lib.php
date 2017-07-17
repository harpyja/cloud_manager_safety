<?php

/*
 ==============================================================================
 导入功能库
 ==============================================================================
 */
/**
 ==============================================================================
 * This class provides some functions which can be used when importing data from
 * external files into ZLMS
 * @package	 ZLMS.library
 ==============================================================================
 */
require_once ('document.lib.php');
require_once ("PHPExcel.php");
require_once ("PHPExcel/Writer/PDF.php");
require_once ("PHPExcel/Writer/Excel2007.php");
require_once ("PHPExcel/Writer/Excel5.php");
require_once ("PHPExcel/Writer/CSV.php");
require_once ("PHPExcel/Reader/Excel2007.php");
require_once ("PHPExcel/Reader/Excel5.php");
require_once ("PHPExcel/Reader/CSV.php");

class Import {

	/**
	 * Reads a CSV-file into an array. The first line of the CSV-file should
	 * contain the array-keys.
	 * Example:
	 * FirstName;LastName;Email
	 * John;Doe;john.doe@mail.com
	 * Adam;Adams;adam@mail.com
	 * returns
	 * $result [0]['FirstName'] = 'John';
	 * $result [0]['LastName'] = 'Doe';
	 * $result [0]['Email'] = 'john.doe@mail. com';
	 * $result [1]['FirstName'] = 'Adam';
	 * ...
	 * @param string $filename Path to the CSV-file which should be imported
	 * @return array An array with all data from the CSV-file
	 */
	function csv_to_array($filename) {
		$result = array ();
		$handle = fopen ( $filename, "r" );
		if ($handle === false) {
			return $result;
		}
		$keys = fgetcsv ( $handle, 1000, "," );
		while ( ($row_tmp = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
			$row = array ();
			foreach ( $row_tmp as $index => $value ) {
				$row [$keys [$index]] = $value;
			}
			$result [] = $row;
		}
		fclose ( $handle );
		return $result;
	}

	function parse_to_array($filename, $type = 'xls') {
		ini_set ( "memory_limit", "512M" );
		try {
			switch ($type) {
				case 'xlsx' :
					$objReader = new PHPExcel_Reader_Excel2007 ();
					$objReader->setReadDataOnly ( true );
					$objPHPExcel = $objReader->load ( $filename );
					break;
				case 'csv' :
					$objReader = new PHPExcel_Reader_CSV ();
					$objReader->setReadDataOnly ( true );
					$objReader->setDelimiter ( ',' );
					$objReader->setEnclosure ( '"' );
					$objReader->setLineEnding ( "\r\n" );
					$objReader->setSheetIndex ( 0 );
					$objPHPExcel = $objReader->load ( $filename );
					break;
				case 'xls' :
					$objReader = new PHPExcel_Reader_Excel5 ();
					$objReader->setReadDataOnly ( true );
					$objPHPExcel = $objReader->load ( $filename );
					break;
			}
			$currentSheet = $objPHPExcel->getSheet ( 0 );
		} catch ( Exception $ex ) {
			echo $ex->getMessage ();
		}
		
		//$objActiveSheet=$objPHPExcel->getActiveSheet();
		//$cell_collection=$objActiveSheet->getCellCollection();
		

		/**取得一共有多少列*/
		$columnCount = $currentSheet->getHighestColumn (); // <=26
		/**取得一共有多少行*/
		$rowCount = $currentSheet->getHighestRow ();
		
		$data_header = array ();
		if (strlen ( $columnCount ) == 1) {
			for($currentColumn = 'A'; $currentColumn <= $columnCount; $currentColumn ++) {
				$data_header [] = $currentSheet->getCell ( $currentColumn . "1" )->getValue ();
			}
		} /*else {
			for($currentColumn = 'A'; $currentColumn <= $columnCount; $currentColumn ++) {
				$data_header [] = $currentSheet->getCell ( 'A'.$currentColumn . "1" )->getValue ();
			}
		}*/
		
		$data = array ();
		for($currentRow = 2; $currentRow <= $rowCount; $currentRow ++) {
			$data_row = array ();
			$idx = 0;
			for($currentColumn = 'A'; $currentColumn <= $columnCount; $currentColumn ++, $idx ++) {
				$address = $currentColumn . $currentRow;
				//$data_key = $data_header [$idx];
				//$data_key=mb_convert_encoding($data_key,SYSTEM_CHARSET);
				//$data_row [$data_key] = $currentSheet->getCell ( $address )->getValue ();
				$data_row [$data_header [$idx]] = $currentSheet->getCell ( $address )->getValue ();
			}
			$data [$currentRow] = $data_row;
		}
		
		return array ("data" => $data, "header" => $data_header );
	}
}
