<?php

/**
 *  Facebook Chatbot Demo
 *  ==============================================================
 *  Written by Prince Wong
 * 
 *  Copyright 2017 Prince Development Studio. All rights reserved.
 *  ==============================================================
 **/

date_default_timezone_set("Asia/Hong_Kong");
mb_internal_encoding("UTF-8");

class fb_chatbot_api {

    public $config = array(
        'access_token' => '<access_token>',
        'api' => array(
            'messenger' => 'https://graph.facebook.com/v2.10/me/messages?access_token='
        )
    );

    public function __construct()
    {
        // $challenge = $_REQUEST['hub_challenge'];
        // $verify_token = $_REQUEST['hub_verify_token'];

        // Set this Verify Token Value on your Facebook App 
        /* if ($verify_token === 'testtoken') {
            echo $challenge;
        } */

        $input = json_decode(file_get_contents('php://input'), true);

        $this->logToFile('=============================== Request ===============================');
        $this->logToFile($input);
        $this->logToFile('=============================== Request ===============================');

        $this->webhook($input);
    }

    public function webhook($input)
    {
        
        if( isset($input['entry'][0]['messaging'][0]) ){

            if( isset($input['entry'][0]['messaging'][0]['delivery']) )
                exit();
            
            $msg = $input['entry'][0]['messaging'][0];
            $sender = $msg['sender']['id'];
            
            $response_type = false;
            $request = '';

            $this->getTemplate('mark_seen', $sender, $request);

            if( isset($msg['message']) ){

                if( isset($msg['message']['quick_reply']['payload'])){

                    $option = explode('_',$msg['message']['quick_reply']['payload']);

                    if( in_array('choose-iphone8-color', $option) ){
                        
                        $info = array(
                            'order_id' => $option[0],
                            'color' => $option[2]
                        );

                        $this->order('update_color', $info);
                        $response_type = 'storage';
                        $request = $info['order_id'];

                    } else if( in_array('choose-iphone8-storage', $option) ){
                        
                        $info = array(
                            'order_id' => $option[0],
                            'storage' => $option[2]
                        );

                        $this->order('update_storage', $info);

                        $current_order = $this->order('get_order', $option);
                        $response_type = 'normal';
                        $request = $current_order['product'].' - '.$current_order['color'].' - '.$current_order['storage'];

                    }

                } else if( isset($msg['message']['text']) ){

                    $response_type = 'normal';
                    $request = $msg['message']['text'];
    
                    if( strtolower($request) == 'reset')
                        $response_type = 'get_started';
                    else if( strtolower($request) == 'airline_checkin' )
                        $response_type = 'airline_checkin';

                }

            } else if( isset($msg['postback']['payload']) and $msg['postback']['payload'] == 'started' ){
                $response_type = 'get_started';
            } else if( isset($msg['postback']['payload']) and $msg['postback']['payload'] == 'buy_iphone8' ){
                $order_id = $this->order('create');
                $response_type = 'color';
                $request = array(
                    'order_id' => $order_id
                );
            } else if( isset($msg['postback']['payload']) and $msg['postback']['payload'] == 'airline_checkin' ){
                $response_type = 'airline_checkin';
            }

            $this->logToFile($response_type);

            if( $response_type != false )
                $this->getTemplate($response_type, $sender, $request);

        }

    }

    public function getTemplate($type, $sender, $content = '' )
    {
        // choose template
        switch ($type) {
            case 'get_started':
                $this->logToFile('template get_started');

                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "message" => array(
                        "attachment" => array(
                            "type" => "template",
                            "payload" => array(
                                "template_type" => "button",
                                "text" => "主目錄",
                                "buttons" => array([
                                    "type" => "web_url",
                                    "url" => "https://hk.yahoo.com",
                                    "title" => "瀏覽官方網頁"
                                ],[
                                    "type" => "postback",
                                    "title" => "購買 iPhone 8",
                                    "payload" => "buy_iphone8"
                                ],[
                                    "type" => "postback",
                                    "title" => "查詢機票",
                                    "payload" => "booking"
                                ])
                            )
                        )
                    )
                );
                break;

            case 'normal':

                $this->logToFile('template normal');

                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "message" => array(
                        "text" => $content
                    )
                );

                break;

            /**
             *  Quick reply
             */
            case 'color':

                $prefix = $content['order_id'].'_choose-iphone8-color_';

                $this->logToFile($prefix);

                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "message" => array(
                        "text" => "請選擇顏色",
                        "quick_replies" => array([
                            "content_type" => "text",
                            "title" => "亮黑色",
                            "payload" => $prefix."sharpblack"
                        ],[
                            "content_type" => "text",
                            "title" => "黑色",
                            "payload" => $prefix."black"
                        ],[
                            "content_type" => "text",
                            "title" => "銀色",
                            "payload" => $prefix."silver"
                        ],[
                            "content_type" => "text",
                            "title" => "金色",
                            "payload" => $prefix."gold"
                        ])
                    )
                );

                break;

                case 'storage':
                    
                $prefix = $content.'_choose-iphone8-storage_';
    
                    $jsonData = array(
                        "recipient" => array(
                            "id" => $sender
                        ),
                        "message" => array(
                            "text" => "請選擇容量",
                            "quick_replies" => array([
                                "content_type" => "text",
                                "title" => "64GB",
                                "payload" => $prefix."64GB"
                            ],[
                                "content_type" => "text",
                                "title" => "128GB",
                                "payload" => $prefix."128GB"
                            ],[
                                "content_type" => "text",
                                "title" => "256GB",
                                "payload" => $prefix."256GB"
                            ],[
                                "content_type" => "text",
                                "title" => "512GB",
                                "payload" => $prefix."512GB"
                            ])
                        )
                    );
    
                    break;

            /**
             *  Sender Actions
             * 
             *  [mark_seen] => Mark last message as read
             *  [typing_on] => Turn typing indicators on
             *  [typing_off] => Turn typing indicators off
             */
            case 'mark_seen':
                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "sender_action" => "mark_seen"
                );
                break;

            case 'typing_on':
                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "sender_action" => "typing_on"
                );
                break;

            case 'typing_off':
                $jsonData = array(
                    "recipient" => array(
                        "id" => $sender
                    ),
                    "sender_action" => "typing_off"
                );
                break;
            /**
             *  Airline Template
             */
            case 'airline_checkin':
            $jsonData = array(
                "recipient" => array(
                    "id" => $sender
                ),
                "message" => array(
                    "attachment" => array(
                        "type" => "template",
                        "payload" => array(
                            "template_type" => "airline_checkin",
                            "intro_message" => "Check-in is available now.",
                            "locale" => "en_US",        
                            "pnr_number" => "A12345",
                            "checkin_url" => "https://www.cathaypacific.com/cx/zh_HK.html",  
                            "flight_info" => [array(
                                "flight_number" => "CX787",
                                "departure_airport" => array(
                                    "airport_code" => "HKG",
                                    "city" => "Hong Kong",
                                    "terminal" => "T1",
                                    "gate" => "G8"
                                ),
                                "arrival_airport" => array(
                                    "airport_code" => "SGP",
                                    "city" => "Singapore",
                                    "terminal" => "T4",
                                    "gate" => "G8"
                                ),
                                "flight_schedule" => array(
                                    "boarding_time" =>"2016-01-05T15:05",
                                    "departure_time" => "2016-01-05T15:45",
                                    "arrival_time" => "2016-01-05T17:30"
                                )
                            )]
                        )
                    )
                )
            );
            break;
        }

        $jsonData = json_encode($jsonData);

        $this->logToFile($jsonData);

        $this->curl_post('default', $jsonData);
    }

    // Common
    public function order( $actions, $params = array() )
    {
        // Database
        $servername = "<ip_address>";
        $username = "<username>";
        $password = "<password>";

        $conn = new PDO("mysql:host=$servername;dbname=fb_chatbot", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if( $actions == 'create' ){
            
            $order_id = 'INV'.date('YmdHis');
            $create_at = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO `order_form_demo` (order_id,product,create_at) VALUES ('".$order_id."', 'iPhone8', '".$create_at."')";
            $result = $conn->exec($sql);

            return $order_id;

        } else if( $actions == 'update_color' ){

            $sql = "UPDATE `order_form_demo` SET `color` = '".$params['color']."' WHERE `order_id` = '".$params['order_id']."'";
            $result = $conn->exec($sql);

        } else if( $actions == 'update_storage' ){
            
            $sql = "UPDATE `order_form_demo` SET `storage` = '".$params['storage']."' WHERE `order_id` = '".$params['order_id']."'";
            $result = $conn->exec($sql);

        } else if( $actions == 'get_order' ){
            
            $sql = "SELECT `product`, `storage`, `color` FROM `order_form_demo` WHERE `order_id` = ?";
            $query = $conn->prepare($sql);
            $query->execute(array($params[0]));
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return $result[0];
        }
    }

    public function curl_post( $url = '', $data )
    {
        if( $url == 'default' )
            $url = $this->config['api']['messenger'].$this->config['access_token'];

        $ch = curl_init($url);
        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);
        //Attach our encoded JSON string to the POST fields.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        //Execute the request but first check if the message is not empty.
        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->logToFile($result);
    }

    public function var_dump($var = null)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

    public function logToFile( $content = 'empty' )
    {
        $fd = fopen('dev-api.log', "a");
        $str = '"'.date('Y-m-d H:i:s').'","'.print_r($content, true)."\n";
        fwrite($fd, $str . "\n\r");
        fclose($fd);
    }
}

$api = new fb_chatbot_api();

?>