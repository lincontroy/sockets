<?php
// Set file mime type event-stream and subscribe to this 
$post = file_get_contents('php://input');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$num="254704800563";

$amount=90;

$balance=50;

$new_balance=$amount+$balance;

$data=array('num'=>$num,'amount'=>$new_balance);

$data=json_encode($data);

echo "data: $data\n\n";

?>