<?php 
    // NGROK and sandbox
	$phonenumber = $_GET['MSISDN'];  
    $sessionID = $_GET['sessionId'];  
    $servicecode = $_GET['serviceCode'];  
    $ussdString = $_GET['text'];

    // $phonenumber = $_POST['phoneNumber'];
    // $sessionID = $_POST['sessionId'];  
    // $servicecode = $_POST['serviceCode'];  
    // $ussdString = $_POST['text'];

    // Sandbox Settings, Database Settings
    require_once('settings.php');
    require_once('resources/AfricasTalkingGateway.php');

	$level =0; 

	if($ussdString != ""){  
	    $ussdString=  str_replace("#", "*", $ussdString);  
	    $ussdString_explode = explode("*", $ussdString);
	    $level = count($ussdString_explode);  
    }

    //echo ussd_text
    function ussd_proceed ($ussd_text){  
    	echo $ussd_text;  
    }

    if($level == 0){
        // Display main menu
        displayMainMenu();
    }

    if ($level > 0 ) {
        switch ( $ussdString_explode[0] ) {
            case 1: // 
                sendMpesa($ussdString_explode, $phonenumber);
                break;
            case 2: //  
                sendAirtime($ussdString_explode, $phonenumber);
                break;
            default:						
                $ussd_text = "CON Oops! Invalid choice.";
                ussd_proceed($ussd_text);
                break;
        }   //  End switch
    }


    /* Functions   */
    function displayMainMenu () {
        $ussd_text = "CON SwahiliPot Code Challenge\n1: Checkout With MPesa\n2: Send Airtime\nBy Brian Okinyi<brianokinyi.bo@gmail.com>";
        ussd_proceed($ussd_text);
    }

    function sendMpesa($details, $phonenumber) {
        if (count($details) == 1 ) {
            $ussd_text = "CON Reply with amount to send via Mpesa";
            ussd_proceed($ussd_text);
        }else if (count($details) == 2 ){
            $amount = $details[1];

            $phonenumber = str_replace("+", "", $phonenumber);

            require_once('config/Constant.php');
            require_once('lib/MpesaAPI.php');

            $PAYBILL_NO = "898998";
            $MERCHENTS_ID = $PAYBILL_NO;
            function generateRandomString() {
                $length = 10;
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                return $randomString;
            }
            $MERCHANT_TRANSACTION_ID = generateRandomString();

            //Get the server address for callback
            $host=gethostname();
            $ip = gethostbyname($host);

            //$Password=Constant::generateHash();
            $Password='ZmRmZDYwYzIzZDQxZDc5ODYwMTIzYjUxNzNkZDMwMDRjNGRkZTY2ZDQ3ZTI0YjVjODc4ZTExNTNjMDA1YTcwNw==';
            $mpesaclient=new MpesaAPI;

            $mpesaclient->processCheckOutRequest($Password,$MERCHENTS_ID,$MERCHANT_TRANSACTION_ID,"Sokoni Deposit",$amount,$phonenumber,$ip);
        }

    }



    function sendAirtime($details, $phonenumber) {
        if (count($details) == 1 ) {
            $ussd_text = "CON How much to send?";
            ussd_proceed($ussd_text);
        }else if (count($details) == 2 ){
            $amount = $details[1];

            $recipients = array(
                array("phoneNumber"=>$phonenumber, "amount"=>$amount),
            );

            $recipientStringFormat = json_encode($recipients);

            $gateway  = new AfricasTalkingGateway(username, apikey, "sandbox");

            try {
                $results = $gateway->sendAirtime($recipientStringFormat);
                //Error message is important when the status is not Success
                // echo $result->errorMessage;
            }
            catch(AfricasTalkingGatewayException $e){
                echo $e->getMessage();
            }

            // Now send sms notification
        }
    }

?>
