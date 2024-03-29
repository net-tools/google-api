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
use \Nettools\GoogleAPI\Exceptions\ExceptionHelper;



?>
<html>
    <head>
        <title>Drive sample</title>
    </head>
<body>
<?php



// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(Google\Service\Drive::DRIVE));


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
    // getting a service wrapper for Drive service ; returns a \Nettools\GoogleAPI\ServiceWrappers\Drive object
    $drive = $gint->getService('Drive');
    
    
    // catching no authorization issues
    try
    {
        // listing files
        $files = $drive->listAllFiles(array('q'=>'trashed=false'));
        
        // formatting output with a list of download (export) links
        $html = '<div>List of files in the user Google Drive (click for file preview) :</div>';
        foreach ( $files as $file )
            $html .= '<a href="'. $drive->previewLink($file) . '" target="_blank">' . $file->name . '</a><br>';
            

        // if we arrive here, the authorization is good
        print_r("<div style=\"padding:5px; background-color:lightgray;\">" . $html . "</div>");
    }
    // catch auth errors 
    catch (Google\Service\Exception $e)
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

<p><a href="Drive.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>
