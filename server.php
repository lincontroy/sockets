<?php

$payments = file_get_contents('php://input');
define('HOST_NAME',"localhost"); 
define('PORT',"8090");
$null = NULL;
require_once("handler.php");

$payments=json_decode($payments,true);

$chatHandler = new ChatHandler();
// $new_balance=0;
if(isset($payments)){

    
    $mobile= $payments['mssidn'];

    $amount=$payments['amount'];
    
    $transaction=$payments['transaction'];

    $balance=50;

    $new_balance=$amount+$balance;


        $socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socketResource, 0, PORT);
        socket_listen($socketResource);

        $clientSocketArray = array($socketResource);

        socket_close($socketResource);
}else{
  

$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socketResource, 0, PORT);
socket_listen($socketResource);

$clientSocketArray = array($socketResource);
while (true) {
	$newSocketArray = $clientSocketArray;
	socket_select($newSocketArray, $null, $null, 0, 10);
	
	if (in_array($socketResource, $newSocketArray)) {
		$newSocket = socket_accept($socketResource);
		$clientSocketArray[] = $newSocket;
		
		$header = socket_read($newSocket, 1024);
		$chatHandler->doHandshake($header, $newSocket, HOST_NAME, PORT);
		
		socket_getpeername($newSocket, $client_ip_address);
		$connectionACK = $chatHandler->newConnectionACK($client_ip_address);
		
		$chatHandler->send($connectionACK);
		
		$newSocketIndex = array_search($socketResource, $newSocketArray);
		unset($newSocketArray[$newSocketIndex]);
	}
	
	foreach ($newSocketArray as $newSocketArrayResource) {	
		while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){
			$socketMessage = $chatHandler->unseal($socketData);
			$messageObj = json_decode($socketMessage);
			
			
            // $chat_box_message = $chatHandler->createChatBoxMessage($messageObj->chat_user, $messageObj->whenever);
			//check the message sender

            

            if(isset($messageObj->client)){
                //send message back with client balance

                $sendbalance=$chatHandler->sendbalance($messageObj->client,$new_balance);

                $chatHandler->send($sendbalance);


            }else{

                echo $sendbalance;
               
                break 2;
                
            }
            
            
		}
		
		$socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
		if ($socketData === false) { 
			socket_getpeername($newSocketArrayResource, $client_ip_address);
			$connectionACK = $chatHandler->connectionDisconnectACK($client_ip_address);
			$chatHandler->send($connectionACK);
			$newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
			unset($clientSocketArray[$newSocketIndex]);			
		}
	}
}
socket_close($socketResource);
}