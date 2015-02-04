<?php

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/integrationQueue.php");
if(isset($_POST['selected_sets']) && is_array($_POST['selected_sets']))
{
    $queuing_server = new integrationQueue();
    $user = $this->request->getUser();

	/* $bundle is sent to the worker. Where it is embedded into the request body of the rest call made to collective access*/
    $bundle = array(
        "bundles" => array(
            "ca_objects.idno" => "convertCodesToDisplayText:true",
            "ca_objects.object_id" => "delimiter:true",
            "ca_objects.preferred_labels.name" => "delimiter:true",
            "ca_objects.cagObjectnaamInfo.objectNaam" => array(
                "delimiter"=> true,
                "convertCodesToDisplayText" => true ),
            "ca_objects.inhoudBeschrijving" => "returnAsArray:true",
            "ca_collections.preferred_labels" => array("template" =>
                "^ca_collections.preferred_labels"),
            "ca_objects.objectVervaardigingDate" => array("template" => "^ca_objects.objectVervaardigingInfo.objectVervaardigingDate"),
            "ca_places.preferred_labels" => array("template" =>
                "^ca_places.preferred_labels.name"),
            "ca_objects.digitoolUrl" => array(
                "returnAsArray" => "true",
                "convertCodesToDisplayText" => "true"),
            "ca_objects.creativecommons" => array(
                "convertCodesToDisplayText" => "true"),
            "ca_places.georeference" => array("template" =>
                "^ca_places.georeference",
                "coordinates" =>   "true",
                "returnAsArray" => "true"),
            "ca_vervaardiger" => array("template" =>
                "^ca_entities.preferred_labels.displayname%delimiter=;_%restrictToRelationshipTypes=292|649|652|655|661|664|667|766|673|676|679|685|691|694|697|784|703|706|712|715|718|721|724|733|736|739|742|748|751|754|757|760|763|769|772|775|778|781|787|790|793|796|799|802|805|808|811|814|817|820|826|829|832|835|838|841|844"),
            "ca_provenance" => array("template" => "^ca_entities.preferred_labels.displayname%delimiter=;_%restrictToRelationshipTypes=295|304"),
            "ca_trefwoord" => array("template" =>
                "^ca_list_items.preferred_labels.name_singular%delimiter=;_%restrictToRelationshipTypes=457"),
            "ca_documentatie" => array("template" =>
                "^ca_occurrences.preferred_labels.name%delimiter=;_%restrictToRelationshipTypes=388"),
            "ca_objects.objectVervaardigingInfo.objectVervaardigingPlace" => array("template" =>
                "^ca_objects.objectVervaardigingInfo.objectVervaardigingPlace.preferred_labels"),
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
        $set_names[] = $set['set_code'];
        $set_info[] = array(
            'set_name'  => $set['set_code'],
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
    echo 'No set selected.';
