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
ini_set("memory_limit", "-1");
ini_set("default_socket_timeout", 10);
set_time_limit(0);

class fb_chatbot_api {

    public function webhook()
    {
        $challenge = $_REQUEST['hub_challenge'];
        $verify_token = $_REQUEST['hub_verify_token'];
        $access_token = '<page_access_token>';
        
        // Set this Verify Token Value on your Facebook App 
        if ($verify_token === '<your_test_token>') {
            echo $challenge;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        // Get the Senders Graph ID
        $sender = $input['entry'][0]['messaging'][0]['sender']['id'];

        $this->logToFile($input);        

        //API Url and Access Token, generate this token value on your Facebook App Page
        $url = 'https://graph.facebook.com/v2.10/me/messages?access_token='.$access_token;
        //Initiate cURL.
        $ch = curl_init($url);
        //The JSON data.
        $jsonData = '{
            "recipient":{
                "id": "' . $sender . '"
            }, 
            "message":{
                "text": "Demo"
            }
        }';

        $jsonData = '{
            "recipient":{
                "id": "' . $sender . '"
            },
            "message":{
                "attachment":{
                    "type":"template",
                    "payload":{
                        "template_type":"button",
                        "text":"What do you want to do next?",
                        "buttons":[{
                            "type":"web_url",
                            "url":"https://www.messenger.com",
                            "title":"Visit Messenger"
                        }]
                    }
                }
            }
        }';

        $jsonData = '{
            "recipient":{
                "id": "' . $sender . '"
            }, 
            "message":{
                "text": "Here\'s a quick reply!",
                "quick_replies":[
                  {
                    "content_type":"location"
                  },
                  {
                    "content_type":"text",
                    "title":"Something Else",
                    "payload":"something"
                  }
                ]
              }
        }';

        // airline check in template

        $jsonData = '{
            "recipient":{
                "id": "' . $sender . '"
            }, 
            "message": {
                "attachment": {
                   "type": "template",
                    "payload": {
                        "template_type": "airline_checkin",
                        "intro_message": "Check-in is available now.",
                        "locale": "en_US",        
                        "pnr_number": "ABCDEF",
                        "checkin_url": "https:\/\/www.airline.com\/check-in",  
                        "flight_info": [{
                            "flight_number": "f001",
                            "departure_airport": {
                                "airport_code": "SFO",
                                "city": "San Francisco",
                                "terminal": "T4",
                                "gate": "G8"
                            },
                            "arrival_airport": {
                                "airport_code": "SEA",
                                "city": "Seattle",
                                "terminal": "T4",
                                "gate": "G8"
                            },
                            "flight_schedule": {
                                "boarding_time": "2016-01-05T15:05",
                                "departure_time": "2016-01-05T15:45",
                                "arrival_time": "2016-01-05T17:30"
                            }
                        }]
                    }
                }
            }
        }';

        // Airline Itinerary Template
        $jsonData = '{
            "recipient":{
                "id": "' . $sender . '"
            }, 
            "message": {
                "attachment": {
                    "type": "template",
                    "payload": {
                        "template_type": "airline_itinerary",
                        "intro_message": "Here is your flight itinerary.",
                        "locale": "en_US",
                        "pnr_number": "ABCDEF",
                        "passenger_info": [
                            {
                                "name": "Prince Wong",
                                "ticket_number": "0741234567890",
                                "passenger_id": "p001"
                            },
                            {
                                "name": "Yung Yung",
                                "ticket_number": "0741234567891",
                                "passenger_id": "p002"
                            }
                        ],
                        "flight_info": [
                            {
                                "connection_id": "c001",
                                "segment_id": "s001",
                                "flight_number": "KL9123",
                                "aircraft_type": "Boeing 737",
                                "departure_airport": {
                                    "airport_code": "SFO",
                                    "city": "San Francisco",
                                    "terminal": "T4",
                                    "gate": "G8"
                                },
                                "arrival_airport": {
                                    "airport_code": "SLC",
                                    "city": "Salt Lake City",
                                    "terminal": "T4",
                                    "gate": "G8"
                                },
                                "flight_schedule": {
                                    "departure_time": "2016-01-02T19:45",
                                    "arrival_time": "2016-01-02T21:20"
                                },
                                "travel_class": "business"
                            },
                            {
                                "connection_id": "c002",
                                "segment_id": "s002",
                                "flight_number": "KL321",
                                "aircraft_type": "Boeing 747-200",
                                "travel_class": "business",
                                    "departure_airport": {
                                    "airport_code": "SLC",
                                    "city": "Salt Lake City",
                                    "terminal": "T1",
                                    "gate": "G33"
                                },
                                "arrival_airport": {
                                    "airport_code": "AMS",
                                    "city": "Amsterdam",
                                    "terminal": "T1",
                                    "gate": "G33"
                                },
                                "flight_schedule": {
                                    "departure_time": "2016-01-02T22:45",
                                    "arrival_time": "2016-01-03T17:20"
                                }
                            }
                        ],
                        "passenger_segment_info": [
                            {
                                "segment_id": "s001",
                                "passenger_id": "p001",
                                "seat": "12A",
                                "seat_type": "Business"
                            },
                            {
                                "segment_id": "s001",
                                "passenger_id": "p002",
                                "seat": "12B",
                                "seat_type": "Business"
                            },
                            {
                                "segment_id": "s002",
                                "passenger_id": "p001",
                                "seat": "73A",
                                "seat_type": "World Business",
                                "product_info": [
                                    {
                                        "title": "Lounge",
                                        "value": "Complimentary lounge access"
                                    },
                                    {
                                        "title": "Baggage",
                                        "value": "1 extra bag 50lbs"
                                    }
                                ]
                            },
                            {
                                "segment_id": "s002",
                                "passenger_id": "p002",
                                "seat": "73B",
                                "seat_type": "World Business",
                                "product_info": [
                                    {
                                        "title": "Lounge",
                                        "value": "Complimentary lounge access"
                                    },
                                    {
                                        "title": "Baggage",
                                        "value": "1 extra bag 50lbs"
                                    }
                                ]
                            }
                        ],
                        "price_info": [
                            {
                                "title": "Fuel surcharge",
                                "amount": "1597",
                                "currency": "HKD"
                            }
                        ],
                        "base_price": "12206",
                        "tax": "200",
                        "total_price": "14003",
                        "currency": "HKD"
                    }
                }
            }
        }';

        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);
        //Attach our encoded JSON string to the POST fields.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //Execute the request but first check if the message is not empty.
        if(!empty($input['entry'][0]['messaging'][0]['message']) || !empty($input['entry'][0]['messaging'][0]['postback'])){
            $result = curl_exec($ch);
            $this->logToFile($result);
        }
    }

    public function logToFile( $content = 'empty' )
    {
        $fd = fopen('api.log', "a");
        $str = '"'.date('Y-m-d H:i:s').'","'.print_r($content, true)."\n";
        fwrite($fd, $str . "\n\r");
        fclose($fd);
    }

}

$api = new fb_chatbot_api;

$api->webhook();

?>