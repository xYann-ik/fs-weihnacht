<?php

class PostCardApi {
    protected $api;
    protected $token;
    protected $card;
    
    function __construct() {
        if (true) {
            $this->api = [
                'base' => 'https://apiint.post.ch/pcc/',
                'url' => 'https://apiint.post.ch/pcc/api/v1/postcards',
                'cardUrl' => '',
                'auth' => 'https://apiint.post.ch/OAuth/authorization',
                'token' => 'https://apiint.post.ch/OAuth/token',
                'clientId' => '923ae486e7c09d2cf9b2bbd9825c0470',
                'clientSecret' => '4ca10bd4e6d3dc8a0f1f46299ed97ef9',
                'scope' => 'PCCAPI',
                'campaignKey' => '1852a97c-1055-4e13-a22d-fcef44733d73',
                'grant_type' => 'client_credentials'
            ];
        }
        $this->card = [];
    }

    function auth () {
        //The url you wish to send the POST request to
        //The data you want to send via POST
        $fields = [
            'client_id' => $this->api['clientId'],
            'client_secret' => $this->api['clientSecret'],
            'scope' => $this->api['scope'],
            'grant_type' => $this->api['grant_type']
        ];

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->api['auth']);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

        //execute post
        $result = curl_exec($ch);
        if ($result) {
            $result = json_decode($result, true);
            $this->token = $result['access_token'];

            $this->create();

            return true;
        }
        echo 'Something went wrong, please refresh the page and try again.';
        exit;
    }

    function create () {
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->api['url'] . '?campaignKey=' . $this->api['campaignKey']);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer " . $this->token ));
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

        //execute post
        $result = curl_exec($ch);
        
        if ($result) {
            $result = json_decode($result, true);
            $this->api['cardUrl'] = $this->api['url'] . '/' . $result['cardKey'];
            
            $this->cardCurl('/addresses/sender', 'put',
                [
                    'lastname' => 'Borter',
                    'firstname' => 'Yann',
                    'street' => 'Lampertji',
                    'houseNr' => '2',
                    'zip' => '3945',
                    'city' => 'Gampel'
                ]
            );
            
            $this->cardCurl('/addresses/recipient', 'put',
                [
                    'title' => 'Herr',
                    'lastname' => 'Borter',
                    'firstname' => 'Yannik',
                    'street' => 'Überhengertstrasse',
                    'houseNr' => '21',
                    'zip' => '3983',
                    'city' => 'Mörel',
                    'country' => 'Schweiz'
                ]
            );

            $this->cardCurl('/image', 'put',
                [
                    //'image' => new CURLFile('fs-weihnacht/cards/card_533e99eee8702f82c1b89a201f5a5f0fab7a.jpg', 'multipart/form-data', 'image')
                    'image' => 'cards/card_533e99eee8702f82c1b89a201f5a5f0fab7a.jpg'
                ], true
            );
    
            // $preview = $this->cardCurl('/previews/back');
            $preview = $this->cardCurl('/state');
   
            print_r($preview);
            exit;
        }
    }

    function cardCurl ($url, $type = 'get', $data = [], $file = null) {
        $ch = curl_init();

        if ($type != 'get') {
            //url-ify the data for the POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if ($file) {
            /*
            $image = fopen($file, "rb");
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_INFILE, $image);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
            */
        }

        curl_setopt($ch, CURLOPT_URL, $this->api['cardUrl'] . $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($type));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: ' . ($file ? 'multipart/form-data' : 'application/json'), 'Authorization: Bearer ' . $this->token ));
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //$result = curl_exec($ch);
        
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $streamVerboseHandle = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $streamVerboseHandle);

        //execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            printf("cUrl error (#%d): %s<br>\n",
                   curl_errno($ch),
                   htmlspecialchars(curl_error($ch)))
                   ;
        }
        
        rewind($streamVerboseHandle);
        $verboseLog = stream_get_contents($streamVerboseHandle);
        
        echo "cUrl verbose information " . $url . ":\n", 
             "<pre>", htmlspecialchars($verboseLog), "</pre>\n";

        return json_decode($result, true);
    }
}