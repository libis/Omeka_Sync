<?php
/*
 * Get list of mapping files. Files exist in /helpers/mappings directory.
 * */

$mappingDirPath    = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/mappings";
$files = glob($mappingDirPath.'/*.csv');

$mappingsToDisplay = array();
$mappingDataRows = array();
$rowId = 1;
foreach($files as $file){
    $fileName = basename($file, ".csv");
    if(!empty($fileName));{
        $fileUrl = dirname($_SERVER['SCRIPT_NAME'])."/app/plugins/omekaIntegration/helpers/mappings/".$fileName.".csv";
        //$fileName = $fileName."^http://dhtmlx.com^_blank";
        $fileNameWithLink = $fileName."^".$fileUrl."^_blank";
        $mappingDataRows[] = array('id' => $rowId, 'data' => array("", $fileNameWithLink));
        $rowId++;
    }
}

$mappingsToDisplay['rows'] = $mappingDataRows;
echo json_encode($mappingsToDisplay);