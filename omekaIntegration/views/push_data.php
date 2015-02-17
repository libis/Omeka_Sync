<?php

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/integrationQueue.php");
require_once(__CA_MODELS_DIR__."/ca_bundle_displays.php");

$conf_file_path = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/config/displaytemplates.conf";

if(isset($_POST['selected_sets']) && is_array($_POST['selected_sets']))
{

    $o_config = Configuration::load($conf_file_path);

    $queuing_server = new integrationQueue();
    $user = $this->request->getUser();

    $set_names = array();
    $set_info = array();
    $errors = array();

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

        $display_bundle = $o_config->get($set['record_type']);
        if(!isset($display_bundle)){
            $errors[] = "Displaly bundle not found for set '". $set['set_code']."' of type '".$set['record_type']."'";
            continue;

        }

        $libisin_display_bundles = getDisplays($display_bundle);

        $mapping_rules =  file_get_contents(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/".$mapping_file);
        $set_names[] = $set['set_code'];
        $set_info[] = array(
            'set_name'  => $set['set_code'],
            'set_id'    => $set['set_id'],
            'record_type'    => $set['record_type'],
            'bundle'    => json_encode($libisin_display_bundles),
            'mapping'   => $mapping_rules
        );

    }

    $msg_body = array(
        'set_info' => $set_info,
        'user_info' => array('name' => $user->getName(), 'email' => $user->get('email'))
    );

    $queuing_server->queuingRequest($msg_body);

    echo 'Selected sets ('. implode(',' , $set_names).') are being processed, soon you will receive an email (at '.$user->get('email').') with results.<br>'.
               'Errors:('.sizeof($errors).')<br>'.
               implode('<br>' , $errors);

}
else
    echo 'No set selected.';

function getDisplays($display_name){
    $t_display = new ca_bundle_displays();
    $va_displays = $t_display->getBundleDisplays();

    $bundles = array();
    foreach($va_displays as $vn_i => $va_display_by_locale) {
        $va_locales = array_keys($va_display_by_locale);
        $va_info = $va_display_by_locale[$va_locales[0]];

        if($va_info['name'] === $display_name){
            if (!$t_display->load($va_info['display_id'])) { continue; }
            $va_placements = $t_display->getPlacements();

            foreach($va_placements as $vn_placement_id => $va_placement_info) {
                $bundle_settings = array();
                $bundle_settings['bundle_name'] = $va_placement_info['bundle_name'];
                $va_settings = caUnserializeForDatabase($va_placement_info['settings']);
                if(is_array($va_settings)) {
                    foreach($va_settings as $vs_setting => $vm_value) {
                        switch($vs_setting) {
                            case 'label':
                                $labels = array();
                                if(is_array($vm_value)) {
                                    foreach($vm_value as $vn_locale_id => $vm_locale_specific_value) {
                                        if(isset($vm_locale_specific_value) && strlen($vm_locale_specific_value) > 0)
                                            $labels[] = $vm_locale_specific_value;
                                    }
                                    if(sizeof($labels) > 0)
                                        $bundle_settings['label'] = $labels;
                                }
                                break;
                            default:
                                $values = array();
                                if (is_array($vm_value)) {
                                    foreach($vm_value as $vn_i => $vn_val) {
                                        if(isset($vn_val) && strlen($vn_val))
                                            $values [] = $vn_val;
                                    }
                                    if(sizeof($values) > 0)
                                        $bundle_settings[$vs_setting] = $values;
                                } else {
                                    if(isset($vm_value) && strlen($vm_value) > 0)
                                        $bundle_settings[$vs_setting] = $vm_value;
                                }
                                break;
                        }
                    }
                    $bundles[] = $bundle_settings;
                }
            }

        }


    }

    $ignore_template_elements_list = array('remove_first_items', 'hierarchy_order', 'hierarchy_limit', 'show_hierarchy',
        'hierarchical_delimiter', 'sense', 'delimiter', 'label', 'bundle_name');
    $asarray_template_elements_list = array(
        'ca_objects.digitoolUrl', 'ca_entities.digitoolUrl', 'ca_collections.digitoolUrl', 'ca_occurrences.digitoolUrl',
        'ca_places.georeference', 'ca_entities.georeference', 'ca_objects.georeference'
    );

    $libisin_displays = array();
    foreach($bundles as $bundle_item){
        $template_values = array();

        $template_key = "";
        if(array_key_exists('bundle_name', $bundle_item)){

            if(array_key_exists($bundle_item['bundle_name'], $libisin_displays) && array_key_exists('format', $bundle_item))
            {
                if(strpos($bundle_item['bundle_name'],'.') !== false){
                    $alternative_template_key = explode(".", $bundle_item['bundle_name']);
                    $element_type = $alternative_template_key[0];
                }
                else
                    $element_type = $bundle_item['bundle_name'];

                $template_str = str_replace("^","",$bundle_item['format']);


                if(strpos($template_str,$element_type.'.') !== false)
                    $template_key = $template_str;
                else
                    $template_key = $element_type.".".$template_str;
            }
            else
                $template_key = $bundle_item['bundle_name'];
        }
        else
            continue;


        $template_values['convertCodesToDisplayText'] = true;

        // return as array settings for specific elements
        if(in_array($template_key, $asarray_template_elements_list))
            $template_values['returnAsArray'] = true;

        if (strpos($template_key,'georeference') !== false) {
            if($template_key === 'ca_objects.georeference') //for ca_objects.georeference we return coordinates and as array
                $template_values['coordinates'] = true;
            else
                $template_values['returnAsArray'] = false; // for other fields with georeferences we do not return coordinates, and output is not an array
        }		

        foreach($bundle_item as $item => $value){
            if(in_array($item, $ignore_template_elements_list))
                continue;

            switch($item){
                case 'format':
                    if($template_key === 'ca_places' && $value === "^ca_places.georeference"){
                        if(array_key_exists($template_key, $libisin_displays))
                            unset($libisin_displays[$template_key]);
                        $template_key = "ca_places.georeference";
                        $template_values['coordinates'] = true;
                        $template_values['returnAsArray'] = true;
                    }
                    $template_values['template'] = $value;
                    break;

                case 'restrict_to_types':
                case 'restrict_to_relationship_types':
                    $relation_template = "";
                    $relation_delimiter = "";

                    if(is_array($value)){
                        $value = implode('|', $value);
                    }

                    if(array_key_exists('format', $bundle_item) && strlen($bundle_item['format']) > 0)
                        $relation_template = $bundle_item['format'];
                    else
                        $relation_template = '^'.$bundle_item['bundle_name'].'.preferred_labels.name';

                    if(array_key_exists('delimiter', $bundle_item) && strlen($bundle_item['delimiter']) > 0)
                        $relation_delimiter = $bundle_item['delimiter'];
                    else
                        $relation_delimiter = ";";

                    if($item === 'restrict_to_relationship_types'){
                        if(isset($template_values['template']) && strpos($template_values['template'],'_%') !== false){
                            $replace_str_rel_types ="_%restrictToRelationshipTypes=".$value."%";
                            $template_values['template'] = str_replace("_%", $replace_str_rel_types, $template_values['template']);
                        }
                        else
                            $template_values['template'] = $relation_template."%delimiter=".$relation_delimiter."_%restrictToRelationshipTypes=".$value;
                    }

                    if($item === 'restrict_to_types'){
                        if(isset($template_values['template']) && strpos($template_values['template'],'_%') !== false){
                            $replace_str_types ="_%restrictToTypes=".$value."%";
                            $template_values['template'] = str_replace("_%", $replace_str_types, $template_values['template']);
                        }
                        else
                            $template_values['template'] = $relation_template."%delimiter=".$relation_delimiter."_%restrictToTypes=".$value;
                    }

                    unset($template_values['convertCodesToDisplayText']);

                    if(array_key_exists('label', $bundle_item)){

                        if(array_key_exists($template_key, $template_values))
                            unset($template_values[$template_key]);

                        $display_template_label = $bundle_item['label'];
                        if(is_array($display_template_label) && sizeof($display_template_label) > 0)
                            $template_key = $display_template_label[0];
                        else
                            $template_key = $bundle_item['label'];
                    }

                    if(array_key_exists('returnAsArray', $template_values))
                        unset($template_values['returnAsArray']);

                    break;

                case 'maximum_length':
                    if($value > 0)
                        $template_values[$item] = $value;
                    break;

                case 'delimiter':
                    $template_values[$item] = $value;
                    break;

                default:
                    $template_values[$item] = $value;
            }
            if(strlen($template_key) > 0){
                $libisin_displays[$template_key] = $template_values;
            }
        }
    }

    $libisin_display_bundles ["bundles"] = $libisin_displays;
    return $libisin_display_bundles;
}

