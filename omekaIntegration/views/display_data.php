<?php
require_once(__CA_MODELS_DIR__."/ca_bundle_displays.php");

$user = $this->request->getUser();
$t_display = new ca_bundle_displays();
$va_displays = $t_display->getBundleDisplays(array('user_id' => $user->getUserId()));

$display_bundles = array();
foreach($va_displays as $vn_i => $va_display_by_locale) {
    $va_locales = array_keys($va_display_by_locale);
    $va_info = $va_display_by_locale[$va_locales[0]];
    if(!empty($va_info['name']) && !empty($va_info['display_id']))
        $display_bundles[$va_info['name']] = $va_info['display_id'];
}
ksort($display_bundles,	SORT_STRING|SORT_FLAG_CASE); //sort display bundles names alphabetically
echo json_encode($display_bundles);