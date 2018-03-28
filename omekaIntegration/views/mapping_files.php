<?php
/*
 * Get list of mapping files. Files exist in /helpers/mappings directory.
 * */

$mappingDirPath    = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/mappings";
$files = glob($mappingDirPath.'/*.csv');
$mappingFiles = array();
foreach($files as $file){
    $fileName = basename($file, ".csv");
    if(!empty($fileName));
        $mappingFiles[$fileName] = basename($file);
}
ksort($mappingFiles, SORT_STRING|SORT_FLAG_CASE); //sort mapping files alphabetically
echo json_encode($mappingFiles);