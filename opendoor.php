<?php
/**
 * Created by PhpStorm.
 * User: lukin
 * Date: 21/03/2018
 * Time: 10:40
 */
require __DIR__ . '/vendor/autoload.php';

use \sskaje\mqtt\MQTT;
use \sskaje\mqtt\Debug;
use \sskaje\mqtt\MessageHandler;

require __DIR__ . '/config.php';

$mqtt = new MQTT($MQTT_SERVER);

$context = stream_context_create();
$mqtt->setSocketContext($context);

//Debug::Enable();

$mqtt->setAuth($AUTH_USERNAME, $AUTH_PASSWORD);
$mqtt->setKeepalive(10);
$connected = $mqtt->connect();
if (!$connected) {
    die("Not connected\n");
}

$mqtt->subscribe(['opendoor' => 0]);

class MySubscribeCallback extends MessageHandler
{

    public function publish(MQTT $mqtt, sskaje\mqtt\Message\PUBLISH $publish_object)
    {
        printf(
            "\e[32mI got a message\e[0m:(msgid=%d, QoS=%d, dup=%d, topic=%s) \e[32m%s\e[0m\n",
            $publish_object->getMsgID(),
            $publish_object->getQoS(),
            $publish_object->getDup(),
            $publish_object->getTopic(),
            $publish_object->getMessage()
        );

        shell_exec('ssh pi@hass.pi -- "echo 1 > /var/run/menling.dat"');

    }
}

$callback = new MySubscribeCallback();

$mqtt->setHandler($callback);

$mqtt->loop();

