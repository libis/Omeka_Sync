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

header("Content-type: text/xml;charset=utf-8");
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

$row = 1;
foreach($publicSets as $item){
    foreach($item as $set){
        $s .= "<row id='". $row."'>";
        $s .= "<cell><![CDATA[". $set['set_code']."]]></cell>";
        $s .= "<cell><![CDATA[". $set['set_id']."]]></cell>";
        $s .= "<cell>". $set['item_count']."</cell>";
        $s .= "<cell><![CDATA[". $set['set_content_type']."]]></cell>";
        $s .= "<cell><![CDATA[". $set['fname'].' '.$set['lname']."]]></cell>";
        $s .= "<cell><![CDATA[". "Export publiek"."]]></cell>";
        $s .= "</row>";

        $row++;
    }
}
$s .= "</rows>";
echo $s;
?>

