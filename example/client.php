<?php
include 'jsonrpc.php';

$client = new JsonRPC("127.0.0.1", 8080, "/rpc");
//$r = $client->Call("Arith.Multiply", array('A'=>7, 'B'=>8));

//var_export($r);

$r = $client->Call("Person.GetPerson", "11");

var_export($r);