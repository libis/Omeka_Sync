<?php
require_once(__CA_MODELS_DIR__.'/ca_sets.php');
$t_set = new ca_sets();
$publicSets = $t_set->getSets(array('checkAccess' => 1));
$count = sizeof($publicSets);

$page = $_GET['page'];
$limit = $_GET['rows'];
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}

if ($page > $total_pages) $page=$total_pages;
$start = $limit*$page - $limit;
if($start <0) $start = 0;

$responce = array();
$responce["page"] = $page;
$responce["total"] = $total_pages;
$responce["records"] = $count;
$setRow = 0;
$arrayRows = array();
foreach($publicSets as $setItem){
    foreach($setItem as $caSetet){
        $arrayRows[$setRow]['id']=$setRow;
        $arrayRows[$setRow]['cell']=array($caSetet['set_code'],$caSetet['set_id'],$caSetet['item_count'],$caSetet['set_content_type'],$caSetet['fname'].' '.$caSetet['lname'],"Export publiek");
        $setRow++;
    }
}

$responce["rows"] = $arrayRows;
echo json_encode($responce);