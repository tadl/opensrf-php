<?php

require_once('opensrf/client.php');

// config sets $endpoint - see config-example.inc.php
require_once('config.inc.php');

$client = new OpensrfClient($endpoint);

print_r($client);

$response = $client->request("open-ils.circ", "opensrf.open-ils.system.ils_version");

print_r($response);

if ($response->success) {
    print_r($response->payload);
} else {
    print "Request failed with error " . $response->status . " - " . $response->debug . "\n";
    print_r($response->payload);
}
