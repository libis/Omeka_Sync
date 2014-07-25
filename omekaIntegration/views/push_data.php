<?php

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/integrationQueue.php");

if(isset($_POST['selected_sets']) && is_array($_POST['selected_sets']))
{
    $queuing_server = new integrationQueue();
    $user = $this->request->getUser();

    $msg_body = array(
        'sets' => $_POST['selected_sets'],
        'user_info' => array('name' => $user->getName(), 'email' => $user->get('email'))
    );

    $queuing_server->queuingRequest($msg_body);

    echo 'Selected sets ('. implode(',' , $_POST['selected_sets']).') are being processed, soon you will receive an email (at '.$user->get('email').') with results.<br>';
}
else
    echo 'No set selected.'

?>

