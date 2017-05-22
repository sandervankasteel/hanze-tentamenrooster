<?php
date_default_timezone_set('Europe/Amsterdam');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/hanze.php';
require __DIR__ . '/utils.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$hanze = new Hanze($_ENV['HANZE_USERNAME'], $_ENV['PASSWORD']);
$client = $hanze->getToken();

// Get the Excel file
$response = $client->request('GET', $_ENV['URL_EXCEL_FILE']);

// Create temp file and write to that file
$tmpFile = tmpfile();
fwrite($tmpFile, $response->getBody()->getContents());

// Create an Excel Reader object
$objReader = new PHPExcel_Reader_Excel2007();
$objReader->setReadDataOnly(true); //optional
$objPHPExcel = $objReader->load(stream_get_meta_data($tmpFile)['uri']);


// All the information is based in the first sheet
$sheet = $objPHPExcel->getSheet(0);

// First let's clear all the old events
$googleClient = getGoogleClient();
clearGoogleCalender($_ENV['GOOGLE_CALENDER_ID'], $googleClient);

// Iterate over all the rows *\o/*
foreach($sheet->getRowIterator() as $row) {
    // Skip over the first row because we don't need it!
    if($row->getRowIndex() == 1) {
        continue;
    }

    $tmpEvent = [];
    foreach($row->getCellIterator() as $cell) {
        switch ($cell->getColumn()) {
            case "G":
                $cell->getStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DMYSLASH);
                break;

            case "H":
            case "I":
                //h:mm
                $cell->getStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                break;
            default:
            break;
        };

        array_push($tmpEvent, $cell->getFormattedValue());
    }

    // If we match a certain code, then we need to add it.
    if(strpos($tmpEvent[9], $_ENV['EXAM_CODE_PATTERN']) !== false) {

        $beginDateRaw = explode('/', $tmpEvent[6]);
        $beginTimeRaw = explode(':', $tmpEvent[7]);
        $endTimeRaw = explode(':', $tmpEvent[8]);

        $beginTime = \Carbon\Carbon::create("20" .$beginDateRaw[2], $beginDateRaw[1], $beginDateRaw[0], $beginTimeRaw[0], $beginTimeRaw[1]);
        $endTime = \Carbon\Carbon::create("20" .$beginDateRaw[2], $beginDateRaw[1], $beginDateRaw[0], $endTimeRaw[0], $endTimeRaw[1]);

        $event = new Google_Service_Calendar_Event([
            'summary' => $tmpEvent[10],
            'location' => $tmpEvent[15],
            'start' => [
                'dateTime' => $beginTime->toIso8601String(),
                'timeZone' => 'Europe/Amsterdam'
            ],
            'end' => [
                'dateTime' => $endTime->toIso8601String(),
                'timeZone' => 'Europe/Amsterdam'
            ],
        ]);

        $calender = new Google_Service_Calendar($googleClient);
        $calender->events->insert($_ENV['GOOGLE_CALENDER_ID'], $event);

    }
}

// Clean up as a good boy I am
fclose($tmpFile);