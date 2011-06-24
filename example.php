<?php

require_once('opensrf/client.php');

// config sets $endpoint - see config-example.inc.php
require_once('config.inc.php');

$req = new OpensrfClientRequest($endpoint, "open-ils.circ", "opensrf.open-ils.system.ils_version");

$result = $req->execute();

if ($req->success()) {
    print_r($result);
} else {
    print "Request failed.\n";
    print_r($result);
    print_r($req->error);
}
