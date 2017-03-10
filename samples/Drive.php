<?php

// ============ TO BE UPDATED WITH YOUR GOOGLE DEV ACCOUNT DATA ==============

// for CLIENT_ID and CLIENT_SECRET, refer to the values seen on the Google Developper console
const CLIENT_ID = '622710147925-9v22ibp3rb3hnqrgh53jsr5aq33l0h1f.apps.googleusercontent.com';
const CLIENT_SECRET = 'pZBelCqjMVCFbyjPFTaBtWMG';

// ============ /TO BE UPDATED WITH YOUR GOOGLE DEV ACCOUNT DATA ==============



// composer autoload
if ( file_exists(__DIR__ . '/../../../autoload.php') )
    include_once __DIR__ . '/../../../autoload.php';
else
    include_once $_SERVER['DOCUMENT_ROOT'] . '/libc-test/vendor/autoload.php';
    //die('Composer autoload is not found in ' . realpath(__DIR__ . '/../../../'));




use \Nettools\GoogleAPI\Clients\Serverside_InlineCredentials;



?>
<html>
    <head>
        <title>Drive sample</title>
    </head>
<body>
<?php



// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(Google_Service_Drive::DRIVE));


// if we come back from authorization process, we achieve the process by exchanging the auth code for an access token (set automatically 
// in the google library for further use)
if ( $gint->isAuthorizationProcessLive() )
    $gint->endAuthorizationProcess();
else
// if using an already obtained token, we have 1 hour to use it, setting it in the Google library
if ( isset($_GET['token']) )
{
    $gint->setAccessToken($_GET['token'], true);
    $token = json_decode($_GET['token'], true);

    echo "<p style=\"color:red; font-weight:bold; \">Using previously obtained token, which expires at " . date('Y-m-d H:i:s', $token['created'] + $token['expires_in']) . "</p>";
}



try
{
    // getting a service helper for Drive service ; returns a \Nettools\GoogleAPI\Services\Drive object
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
    catch (Google_Service_Exception $e)
    {
        // get the url to begin the authorization process (by redirecting the user to Google login)
        $url = $gint->beginAuthorizationProcess(true);
        echo "<pre style=\"padding:5px; background-color:lightgray;\">Not authorized / API not enabled in console ; please <a href=\"$url\">click here</a> to go to Google login<br><br>{$e->getMessage()}</pre>";
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
