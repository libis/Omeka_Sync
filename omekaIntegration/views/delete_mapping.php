<?php

$mappingDirPath    = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/mappings";
if(isset($_POST['selected_files']) && is_array($_POST['selected_files']))
{
    $errors = array();
    $deleted_files = array();
    foreach($_POST['selected_files'] as $file){
        if(array_key_exists('Mapping File', $file)){
            $fileToDelete = current(explode("^", $file['Mapping File'])).".csv";
            $filePath = $mappingDirPath."/".$fileToDelete;
            if(file_exists($filePath)){
                if(unlink($filePath))
                    $deleted_files[] = $fileToDelete;
                else
                    $errors[] = "File '$fileToDelete' could not be deleted.";
            }
            else
                $errors[] = "File '$fileToDelete' does not exist.";
        }
        
    }
    if(sizeof($deleted_files) > 0){
        echo 'Following files have been deleted:<br>';
        echo implode('<br>' , $deleted_files);
    }

    if(sizeof($errors) > 0){
        echo '<br>Errors:('.sizeof($errors).')<br>'.
            implode('<br>' , $errors);
    }

}
else
    echo 'No file selected.';
