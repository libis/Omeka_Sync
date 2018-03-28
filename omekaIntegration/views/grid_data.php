<?php
require_once(__CA_MODELS_DIR__.'/ca_sets.php');

$t_set = new ca_sets();
$publicSets = $t_set->getSets(array('checkAccess' => 1));

$setsToDisplay = array();
$setRows = array();
$rowId = 1;
foreach($publicSets as $setItem){

    foreach($setItem as $caSetet){
        $setRows[] = array('id' => $rowId, 'data' => array("", $caSetet['set_code'], $caSetet['set_id'], $caSetet['item_count'], $caSetet['set_content_type'], $caSetet['fname'].' '.$caSetet['lname'], "Select template", "Select mapping"));
        $rowId++;

    }
}
$setsToDisplay['rows'] = $setRows;
echo json_encode($setsToDisplay);