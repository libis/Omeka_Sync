<?php

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/integrationQueue.php");
if(isset($_POST['selected_sets']) && is_array($_POST['selected_sets']))
{
    $queuing_server = new integrationQueue();
    $user = $this->request->getUser();

    $bundle = array(
        "bundles" => array(
            "ca_entities.idno" => "convertCodesToDisplayText:true",
            "ca_entities.preferred_labels.name" => "delimiter:true",
            "ca_entities.entity_id" => "delimiter:true",
            "ca_objects.access" => "convertCodesToDisplayText:true",
            "ca_objects.preferred_labels.name" => "delimiter:true",
            "ca_objects.objectBeschrijving" => "returnAsArray:true",
            "ca_objects.objectNaam" => "delimiter:true",
            "ca_objects.objectDatumInfo" => "returnAsArray:true",
            "ca_objects.objectMateriaalInfo" => "returnAsArray:true",
            "ca_objects.inhoudBeschrijving" => "returnAsArray:true",
            "ca_objects.objectTechniekInfo" => "returnAsArray:true",
            "ca_objects.created" => "returnAsArray:true",
            "ca_objects.lastModified" => "returnAsArray:true",
            "ca_objects.vervaardigerRol" => array("returnAsArray" => "true",
                "template" => "^ca_objects.objectVervaardigingInfo.vervaardigerRol"),
            "ca_objects.completenessInfo" => array("returnAsArray" => "true"),
            "ca_objects.toestandInfo" => array("returnAsArray" => "true"),
            "ca_objects.objectVervaardigingDate" => array("returnAsArray" => "true",
                "template" => "^ca_objects.objectVervaardigingInfo.objectVervaardigingDate"),
            "ca_vervaardiger" => array("template" =>
                "^ca_entities.preferred_labels.displayname",
                 "restrictToRelationshipTypes" => "vervaardigerRelatie"),
            "ca_eigenaar" => array("template" =>
                "^ca_entities.preferred_labels.displayname",
                "restrictToRelationshipTypes" => "eigenaarRelatie"),
            "ca_bewaarplaats" => array("template" =>
                "^ca_entities.preferred_labels.displayname",
                "restrictToRelationshipTypes" => "bewaarinstellingRelatie"),
            "ca_collections" => array("template" =>"^ca_collections.preferred_labels"),
            "ca_collections.preferred_labels" => array("template" =>
                "^ca_collections.preferred_labels"),
            "ca_objects.dimensions_width" => array("template" =>
                "<ifdef code='^ca_objects.dimensionsInfo.dimensions_width'>Breedte: ^ca_objects.dimensionsInfo.dimensions_width </ifdef><ifdef code='^ca_objects.dimensionsInfo.dimensions_type'>(^ca_objects.dimensionsInfo.dimensions_type)</ifdef>"),
            "ca_objects.dimensions_lengte" => array("template" =>
                "<ifdef code='^ca_objects.dimensionsInfo.dimensions_lengte'>Lengte: ^ca_objects.dimensionsInfo.dimensions_lengte </ifdef><ifdef code='^ca_objects.dimensionsInfo.dimensions_type'>(^ca_objects.dimensionsInfo.dimensions_type)</ifdef>"),
            "ca_objects.dimensions_height" => array("template" =>
                "<ifdef code='^ca_objects.dimensionsInfo.dimensions_height'>Hoogte: ^ca_objects.dimensionsInfo.dimensions_height </ifdef><ifdef code='^ca_objects.dimensionsInfo.dimensions_type'>(^ca_objects.dimensionsInfo.dimensions_type)</ifdef>"),
            "ca_objects.dimensions_depth" => array("template" =>
                "<ifdef code='^ca_objects.dimensionsInfo.dimensions_depth'>Diepte: ^ca_objects.dimensionsInfo.dimensions_depth </ifdef><ifdef code='^ca_objects.dimensionsInfo.dimensions_type'>(^ca_objects.dimensionsInfo.dimensions_type)</ifdef>"),

            "ca_objects.digitoolUrl" => array(
                "returnAsArray" => "true",
                "convertCodesToDisplayText" => "true"
            )
        )
    );

    $set_names = array();
    $set_info = array();

    foreach($_POST['selected_sets'] as $set){

        switch($set['record_type']){
            case 'objecten':
            case 'objects':
                $mapping_file = "mappingrulesobjects.csv";
                break;

            case 'collecties':
            case 'collections':
                $mapping_file = "mappingrulescollections.csv";
                break;

            case 'entiteiten':
            case 'entities':
                $mapping_file = "mappingrulesentities.csv";
                break;


        }

        $mapping_rules =  file_get_contents(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/".$mapping_file);
        $set_names[] = $set['set_name'];
        $set_info[] = array(
            'set_name'  => $set['set_name'],
            'set_id'    => $set['set_id'],
            'record_type'    => $set['record_type'],
            'bundle'    => json_encode($bundle),
            'mapping'   => $mapping_rules
        );

    }

    $msg_body = array(
        'set_info' => $set_info,
        'user_info' => array('name' => $user->getName(), 'email' => $user->get('email'))
    );


    $queuing_server->queuingRequest($msg_body);

    echo 'Selected sets ('. implode(',' , $set_names).') are being processed, soon you will receive an email (at '.$user->get('email').') with results.<br>';
}
else
    echo 'No set selected.'

?>

