<?php 

class Hanze
{
    private $hanzeUser;
    private $hanzePasswd;

    public function __construct($username, $password) 
    {
        $this->hanzeUser = $username;
        $this->hanzePasswd = $password;
    }

    public function getToken()
    {
        // The request should atleast contain the following fields
        // curl:Z2F_layoutsZ2F15Z2FAuthenticate.aspxZ3FSourceZ3DZ2Fnld
        // flags:0
        // forcedownlevel:0
        // formdir:3
        // username:<xxx>
        // password:<password>
        // SubmitCreds:Aanmelden
        // trusted:0

        $client = new \GuzzleHttp\Client(['cookies' => true]);
        $client->request('POST', $_ENV['HANZE_LOGON_URL'], [
            'headers' => [
                'Origin' => 'https://www.hanze.nl',
                'Refer' => 'https://www.hanze.nl/CookieAuth.dll?GetLogon?curl=Z2F_layoutsZ2F15Z2FAuthenticate.aspxZ3FSourceZ3DZ2Fnld&reason=0&formdir=3',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
            ],
            'allow_redirects' => false,
            'form_params' => [
                'curl' => 'Z2F_layoutsZ2F15Z2FAuthenticate.aspxZ3FSourceZ3DZ2Fnld',
                'flags' => 0,
                'forcedownlevel' => 0,
                'formdir' => 3,
                'username' => $_ENV['HANZE_USERNAME'],
                'password' => $_ENV['HANZE_PASSWORD'],
                'SubmitCreds' => 'Aanmelden',
                'trusted' => 0
            ]
        ]);
        return $client;  
    }
}