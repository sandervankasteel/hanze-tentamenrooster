<?php
function dd($arg) {
    var_dump($arg);
    die();
}

function getGoogleClient() {
    $client = new Google_Client();
    $client->setApplicationName('Hanze Tentamen Rooster');
    $client->setScopes([Google_Service_Calendar::CALENDAR]);
    $client->setAuthConfig('./client_secret.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    if ($_ENV['GOOGLE_ACCESS_TOKEN'] !== "") {
        $accessToken = $_ENV['GOOGLE_ACCESS_TOKEN'];
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        var_dump($accessToken);
    }

    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($_ENV['GOOGLE_ACCESS_REFRESH_TOKEN']);
    }

    return $client;
}

function clearGoogleCalender($calender_id, $googleClient) {
    $calender = new Google_Service_Calendar($googleClient);
    $toClearCalender = $calender->calendarList->get($calender_id);

    if($toClearCalender) {
        $items = $calender->events->listEvents($calender_id);
        foreach($items as $item) {
            $calender->events->delete($calender_id, $item->id);
        }
    }
}