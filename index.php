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
        $ussd_text = "CON SwahiliPot Code Challenge\n1: Checkout With MPesa\n2: Send Airtime\n\nBy: Brian Okinyi\nbrianokinyi.bo@gmail.com";
        ussd_proceed($ussd_text);
    }

    function sendMpesa($details, $phonenumber) {
        if (count($details) == 1 ) {
            $ussd_text = "CON Reply with amount to send via Mpesa.\nMinimum is 10";
            ussd_proceed($ussd_text);
        }else if (count($details) == 2 ){
            $amount = $details[1];

            $gateway  = new AfricasTalkingGateway(username, apikey, "sandbox");

            $productName  = "swahilipot";

            $currencyCode = "KES";

            $recipient1   = array("phoneNumber" => $phonenumber,
                            "currencyCode" => "KES",
                            "amount"       => $amount,
                            'provider'     => "Mpesa",
                            "metadata"     => array("name"   => "Brian",
                                                    "reason" => "Code Challenge"
                                                    )
            );
            
            $recipients = array($recipient1);

            try {
                $responses = $gateway->mobilePaymentB2CRequest($productName, $recipients);
                
                $ussd_text = "You have sent Ksh. ".$amount.". You will receive confirmation message shortly";
                ussd_proceed($ussd_text);
            }
            catch(AfricasTalkingGatewayException $e){
                echo "END Received error response: ".$e->getMessage();
            }

        }

    }



    function sendAirtime($details, $phonenumber) {
        if (count($details) == 1 ) {
            $ussd_text = "CON Reply with amount of artime to send.\nMinimum is 10";
            ussd_proceed($ussd_text);
        }else if (count($details) == 2 ){
            $amount = $details[1];

            $recipients = array(
                array("phoneNumber"=>$phonenumber, "amount"=>"KES ".$amount),
            );

            $recipientStringFormat = json_encode($recipients);

            $gateway  = new AfricasTalkingGateway(username, apikey, "sandbox");


            try{
                $results = $gateway->sendAirtime($recipientStringFormat);


                // Now send sms notification
                $message = "You have been sent an airtime of ".$amount.".";

                $results = $gateway->sendMessage($phonenumber, $message);

                $ussd_text = "END You have sent airtime of Ksh. ".$amount." to +254723953897. \n You will receive confirmation message shortly";  
                ussd_proceed($ussd_text);
            }
            catch(AfricasTalkingGatewayException $e) {
                $ussd_text = "Encountered an error while sending: ".$e->getMessage();
                ussd_proceed($ussd_text);
            }
        }
    }

?>
