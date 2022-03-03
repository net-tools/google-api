<?php

// ============ UPDATE credentials.php WITH YOUR GOOGLE DEV ACCOUNT DATA ==============

include "Credentials.php";

// ============ /UPDATE credentials.php WITH YOUR GOOGLE DEV ACCOUNT DATA ==============




// composer autoload
if ( !class_exists('\Nettools\GoogleAPI\Clients\Serverside_InlineCredentials') )
    if ( file_exists(__DIR__ . '/../../../autoload.php') )
        include_once __DIR__ . '/../../../autoload.php';
    else
        die('Composer autoload is not found in ' . realpath(__DIR__ . '/../../../'));




use \Nettools\GoogleAPI\Clients\Serverside_InlineCredentials;
//use \Nettools\GoogleAPI\Clients\Serverside_JsonCredentials;
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;



?>
<html>
    <head>
        <title>Calendar sample</title>
    </head>
<body>
<?php



// creating the interface to Google APIs (this creates a underlying \Google\Client object)
// the following lines are the raw code, mentionning most used parameters. However, we will use
// another way of creating the interface, so that the code looks cleaner (see some lines below)
/*$gint = new GoogleInterface(
            array(
                // setting google dev account credentials
                'clientId'      => CLIENT_ID,
                'clientSecret'  => CLIENT_SECRET,

                // the redirect URI is the URI where Google will point the user after successful authorization ; for this sample this is this file
                'redirectUri'   => 'http://' . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['PHP_SELF'], '/'),

                // setting scopes ; for this sample, we just read calendar events so a read-only access is enough
                'scopes'        => array(Google\Service\Calendar::CALENDAR_READONLY),

                // force the API to return a refresh token : it automatically refreshes itself so that the token doesn't expire 1h later
                //'accessType' => 'offline',

                // the user will see a screen informing him about exactly which access rights he is granting your application
                'prompt'=> 'force'
            )
        );*/


// creating the interface to Google APIs in a more simple way, using default values fot redirectUri, accessType and approvalPrompt
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(Google\Service\Calendar::CALENDAR_READONLY));
//$gint = new Serverside_JsonCredentials(__DIR__ . '/client_secret_622710147925-9v22ibp3rb3hnqrgh53jsr5aq33l0h1f.apps.googleusercontent.com.json', array(Google\Service\Calendar::CALENDAR_READONLY));



/* OR, IF YOU PREFER PROGAMMATIC SETUP WITH SETxxx :
// setting google dev account data
$gint->client->setClientId(CLIENT_ID);
$gint->client->setClientSecret(CLIENT_SECRET);

// the redirect URI is the URI where Google will point the user after successful authorization ; for this sample this is this file
$gint->client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['PHP_SELF'], '/'));

// setting scopes ; for this sample, we just read calendar events so a read-only access is enough
$gint->client->setScopes(array(Google\Service\Calendar::CALENDAR_READONLY));

// the user will see a screen informing him about exactly which access rights he is granting your application
$gint->client->setPrompt('force');
*/

// if we come back from authorization process, we achieve the process by exchanging the auth code for an access token (set automatically 
// in the google library for further use)
if ( $gint->isAuthorizationProcessLive() )
    $gint->endAuthorizationProcess();
else
// if using an already obtained token, we have 1 hour to use it, setting it in the Google library
if ( !empty($_GET['token']) )
{
    $gint->setAccessToken($_GET['token'], true);
    $token = json_decode($_GET['token'], true);

    echo "<p style=\"color:red; font-weight:bold; \">Using previously obtained token, which expires at " . date('Y-m-d H:i:s', $token['created'] + $token['expires_in']) . "</p>";
}



try
{
    // getting a service wrapper for Calendar service ; returns a \Nettools\GoogleAPI\ServiceWrappers\Calendar object
    $cal = $gint->getService('Calendar');
    
    
    // catching no authorization issues
    try
    {
        // listing events ; if we are not authorized, an exception will be thrown and we will redirect the user to the Google authorization process (see first catch below)
        // listing events from primary calendar (= main authorized user calendar) ; to use any other calendar belonging to the authorized user, type the calendar ID
        $response = $cal->events->listEvents('primary');

        // if we arrive here, the authorization is good
        print_r("<pre style=\"padding:5px; background-color:lightgray;\">" . print_r($response, true) . "</pre>");
    }
    // catch auth errors 
    catch (\Google\Service\Exception $e)
    {
        // get the url to begin the authorization process (by redirecting the user to Google login)
        $url = $gint->beginAuthorizationProcess(true);
        echo "<pre style=\"padding:5px; background-color:lightgray;\">API error / Not authorized / API not enabled in console ; please <a href=\"$url\">click here</a> to go to Google login or correct error in code<br><br>" . ExceptionHelper::getMessageFor($e) . "</pre>";
    }
    
    
}


// catching other exceptions
catch (Throwable $e)
{
    echo "<h1 style=\"color:red; font-weight:bold\">" . get_class($e) . "</h1>";
    echo "<pre style=\"padding:5px; background-color:lightgray;\">" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    die();
}
?>

<p><a href="Calendar.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>
    
</body>
</html>