<?php
    require_once(__CA_MODELS_DIR__.'/ca_sets.php');
    $t_set = new ca_sets();
    $publicSets = $t_set->getSets(array('checkAccess' => 1));

    $response = json_encode($availableSets[1][1]);


$page = 3;
$limit = 15;
$sidx = 2;
$sord = 'ASC';
if(!$sidx) $sidx =1;
$count = 4;
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}
if ($page > $total_pages) $page=$total_pages;
$start = $limit*$page - $limit;
if($start <0) $start = 0;
$count = 10;

header("Content-type: text/xml;charset=utf-8");

$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

$row['invid'] = 10;
$row['amount'] = 100;
$row['tax'] = 110;
$row['total'] = 15;
$row['note'] = 'this is note';



$s .= "<row id='". $row['invid']."'>";
$s .= "<cell>". $row['invid']."</cell>";
//$s .= "<cell>". $row['invdate']."</cell>";
$s .= "<cell>". $row['amount']."</cell>";
$s .= "<cell>". $row['tax']."</cell>";
$s .= "<cell>". $row['total']."</cell>";
$s .= "<cell><![CDATA[". $row['note']."]]></cell>";
$s .= "</row>";

$s .= "<row id='". $row['invid']."'>";
$s .= "<cell>". $row['invid']."</cell>";
//$s .= "<cell>". $row['invdate']."</cell>";
$s .= "<cell>". $row['amount']."</cell>";
$s .= "<cell>". $row['tax']."</cell>";
$s .= "<cell>". $row['total']."</cell>";
$s .= "<cell><![CDATA[". $row['note']."]]></cell>";
$s .= "</row>";


// be sure to put text data in CDATA
/*while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
    $s .= "<row id='". $row['invid']."'>";
    $s .= "<cell>". $row['invid']."</cell>";
    $s .= "<cell>". $row['invdate']."</cell>";
    $s .= "<cell>". $row['amount']."</cell>";
    $s .= "<cell>". $row['tax']."</cell>";
    $s .= "<cell>". $row['total']."</cell>";
    $s .= "<cell><![CDATA[". $row['note']."]]></cell>";
    $s .= "</row>";
}*/

$s .= "</rows>";

echo $s;
?>