<?php

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/integrationQueue.php");
require_once(__CA_MODELS_DIR__."/ca_bundle_displays.php");

//$conf_file_path = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/config/displaytemplates.conf";

if(isset($_POST['selected_sets']) && is_array($_POST['selected_sets']))
{

    //$o_config = Configuration::load($conf_file_path);

    $queuing_server = new integrationQueue();
    $user = $this->request->getUser();

    $set_names = array();
    $set_info = array();
    $errors = array();

    foreach($_POST['selected_sets'] as $set){
        $display_bundle = $set['display_bundle'];
        if(strtolower($display_bundle) === "select template"){
            $errors[] = "Display bundle for set '". $set['set_code']."' of type '".$set['record_type']."' was not selected.";
            continue;
        }

        $dbundle = getDisplays($display_bundle);
        if(!empty($dbundle['displaybundleerrors'])){
            $errors[] = '<br>'.$display_bundle. ':<br>'.implode('<br>' , $dbundle['displaybundleerrors']);
            continue;
        }

        $libisin_display_bundles = $dbundle['displaly_bundle'];

        $mapping_file = $set['mapping_file'];
        if(strtolower($mapping_file) === "select mapping"){
            $errors[] = "Mapping file for set '". $set['set_code']."' of type '".$set['record_type']."' was not selected.";
            continue;
        }

        $mappingFilePath = __CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/mappings/".$mapping_file.".csv";
        if(!file_exists($mappingFilePath)) {
            $errors[] = "Mapping file '".$mapping_file."' selected for set '". $set['set_code']."' could not be found.";
            continue;
        }

        $mapping_rules =  file_get_contents($mappingFilePath);
        
        $set_names[] = $set['set_code'];
        $set_info[] = array(
            'set_name'  => $set['set_code'],
            'set_id'    => $set['set_id'],
            'record_type'    => $set['record_type'],
            'bundle'    => json_encode($libisin_display_bundles),
            'mapping'   => $mapping_rules
        );

    }

    if(!empty($set_info)){
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
        echo 'Errors with '.sizeof($errors).' display bundles. Processing has been skipped for display bundles with errors.<br>'. implode('<br>' , $errors);
}
else
    echo 'No set selected.';


/**
 *
 * @param $display_name
 * @return array
 */
function getDisplays($display_name){
    $errors = array();

    $t_display = new ca_bundle_displays();
    $va_displays = $t_display->getBundleDisplays();

    $t_element = new ca_metadata_elements();

    $bundles = array();
    foreach($va_displays as $vn_i => $va_display_by_locale) {
        $va_locales = array_keys($va_display_by_locale);
        $va_info = $va_display_by_locale[$va_locales[0]];

        if($va_info['name'] === $display_name){
            if (!$t_display->load($va_info['display_id'])) { continue; }
            $va_placements = $t_display->getPlacements();

            /*
             * step1: Collect information from all placements of the display bundle
             * */
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

    /*
     * step2: Processed the collected information. The processing includes:
     * getting template key, which by default is the name of the element. However, if Display Format and Labels are given,
     * template key will be the Label.
     * If multiple template keys present an error message is generated.
     * */

    /* list of placement items that need to be ignored */
    $ignore_template_elements_list = array('remove_first_items', 'hierarchy_order', 'hierarchy_limit', 'show_hierarchy',
        'hierarchical_delimiter', 'sense', 'delimiter');
    /* list of elements that need returnAsArray flag true */
    $asarray_template_elements_list = array(
        'ca_objects.digitoolUrl', 'ca_entities.digitoolUrl', 'ca_collections.digitoolUrl', 'ca_occurrences.digitoolUrl',
        'ca_entities.georeference', 'ca_objects.georeference', 'ca_places.georeference'
    );

    $libisin_displays = array();
    /*
     * loop through each of the bundle item (collected in step1) and prepare its template key and template.
     * */
    foreach($bundles as $bundle_item){
        $template_values = array();
        $template_key = "";

        // if bundle name is missing, it is an invalid bundle
        if(!array_key_exists('bundle_name', $bundle_item))
            continue;

        foreach($bundle_item as $item => $value){
            $value = str_replace (array("\r\n", "\n", "\r"), ' ', $value);
            if(in_array($item, $ignore_template_elements_list))
                continue;

            switch($item){
                case 'bundle_name':
                    $template_key = $value;
                    break;

                case 'format':
                    /*
                     * for bundles where template is given use 'label' as key, if it has a value. In this way similar
                     * elements with different labels can be used
                    */
                    if(!empty($bundle_item['label'])){
                        $str_tmp = $bundle_item['label'];
                        $str_label = (is_array($str_tmp)) ? current($str_tmp) : $str_tmp;
                        // if label has a value and if template(format) has a value
                        if(strlen($str_label) > 0 && strlen($value) > 0){
                            $template_key = $str_label;
                        }
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

                    // return as array does not return data when used with restrictions, therefore exclude it
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
            }
        }

        if(strlen($template_key) > 0){

            // key with same name already exists, add to errors list
            if(array_key_exists($template_key, $libisin_displays))
            {
                unset($template_values);
                $errors[] = 'Display bundle key '.$template_key.' used multiple times';
                continue;
            }

            // return as array settings for specific elements
            if(in_array($template_key, $asarray_template_elements_list))
                $template_values['returnAsArray'] = true;


            /*
             * for ca_objects.georeference we return coordinates and
             * return as array (already specified in $asarray_template_elements_list)
            */
            if($template_key === 'ca_objects.georeference' || $template_key == 'ca_places.georeference')
                $template_values['coordinates'] = true;

            $temp_key = array_pop(explode(".",$template_key));
            $template_key_type = $t_element->_getElementDatatype($temp_key);

            /*
             * By default convertCodesToDisplayText is false. However for 'list items' it should be true, therefore
             * we check if the type of the current element (template key) is same as of the 'list items', if yes, set
             * convertCodesToDisplayText to true;
             * */
            $dataTypes = $t_element->getFieldInfo("datatype");
            $dataTypeCodes = $dataTypes['BOUNDS_CHOICE_LIST'];
            $key = array_search($template_key_type, $dataTypeCodes); // $key = 3;
            if(strcasecmp('list', $key) == 0)
               $template_values['convertCodesToDisplayText'] = true;

            $libisin_displays[$template_key] = $template_values;
        }
    }

    $libisin_display_bundles ["bundles"] = $libisin_displays;
    return array('displaly_bundle' => $libisin_display_bundles, 'displaybundleerrors' => $errors );
}

