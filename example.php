<?php

require_once('opensrf/client.php');

$endpoint = "https://evergreen.example.org/osrf-gateway-v1";

$req = new OpensrfClientRequest($endpoint, "open-ils.circ", "opensrf.open-ils.system.ils_version");

$result = $req->execute();

if ($req->success()) {
    print_r($result);
} else {
    print "Request failed.\n";
    print_r($result);
    print_r($req->error);
}
