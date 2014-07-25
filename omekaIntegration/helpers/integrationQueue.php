<?php
/**
 * User: NaeemM
 * Date: 24/07/14
 */

require_once(__CA_BASE_DIR__."/app/plugins/omekaIntegration/helpers/rabbitmq/vendor/autoload.php");
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class integrationQueue {

    private $rmq_server;
    private $rmq_port;
    private $rmq_uid;
    private $rmq_pwd;
    private $rmq_vhost;
    private $queue_name;


    # Constructor
    public function __construct()
    {
        $this->loadLibisInConfigurations(dirname(__FILE__).'/config/libisin.conf');
    }

    public function queuingRequest($msg_body) {
        $connection = new AMQPConnection($this->rmq_server, $this->rmq_port, $this->rmq_uid, $this->rmq_pwd, $this->rmq_vhost);

        $channel = $connection->channel();
        $channel->queue_declare($this->queue_name);

        $msg = new AMQPMessage(json_encode($msg_body));
        $channel->basic_publish($msg, '', $this->queue_name);

        $channel->close();
        $connection->close();
    }

    public function loadLibisInConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->rmq_server = $o_config->get('rmq_server');
        $this->rmq_port = $o_config->get('rmq_port');
        $this->rmq_uid = $o_config->get('rmq_uid');
        $this->rmq_pwd = $o_config->get('rmq_pwd');
        $this->rmq_vhost = $o_config->get('rmq_vhost');
        $this->queue_name = $o_config->get('queue_name');
    }

} 