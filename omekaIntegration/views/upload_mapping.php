<?php

$mappingDirPath    = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/mappings";
$path = $_FILES["file"]["tmp_name"];
$name = $_FILES["file"]["name"];
$type = $_FILES["file"]["type"];
$errorMsgs = array();
$successMsgs = array();
if($type != 'text/csv'){
    $errorMsgs[] = "This is not a valid csv file.";
}
else
{
    if(!empty($path) && !empty($name)){

        $fileToStore = $mappingDirPath."/".$name;
        if (file_exists($fileToStore)) {
            $errorMsgs[] = "The file $filename already exists. Rename the file or remove the existing file and upload again.";
        } else {
            $str = file_get_contents($path);
            if(!file_put_contents($fileToStore, $str))
                $errorMsgs[] = " File '$name' could not be uploaded.";
            else
                $successMsgs[] = " File '$name' uploaded successfully.";
        }
    }
    else
        $errorMsgs[] = "Invalid path/name of the file.";
}

echo json_encode(array('success' => $successMsgs, 'errors' => $errorMsgs));
