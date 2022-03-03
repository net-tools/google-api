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
        <title>Gmail sample</title>
    </head>
<body>
<?php



// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(Google\Service\Gmail::GMAIL_READONLY));


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
    // getting a service wrapper for Gmail service ; returns a \Nettools\GoogleAPI\ServiceWrappers\Gmail object
    $gmail = $gint->getService('Gmail');
    
    
    // catching no authorization issues
    try
    {
        // listing emails ; we get an array of Google\Service\Gmail\Message with only messages IDs
        $emails = $gmail->listAllUsersMessages('me');
        
        // querying the first email content ; we get another Google_Service_Gmail_Message object with full content
        $mail = $gmail->users_messages->get('me', $emails[0]->id);

        // get message body (try to find first a text/html part, and if not present, fall back to text/plain)
        $body = $gmail->getMessageBody($mail, ['text/html', 'text/plain']);
        
        // get message date ; if date was parsed successfully, formating it nicely
        $dt = $gmail->getMessageDate($mail);
        if ( is_int($dt) )
            $dt = date("Y-m-d H:i:s", $dt);
        
        // get a list of attachments (as attachements objects, mainly containg IDs)
        $attachments = $gmail->getMessageAttachments($mail);
        
        // get a list of inline attachments (as attachements objects, mainly containg IDs)
        $inlineattachments = $gmail->getMessageInlineAttachments($mail);
        
        
        // formatting output with a list of download (export) links
        $html = "First email details :" .
                "\n  - From : " . $gmail->getMessageHeader($mail, 'From') .
                "\n  - Content-Type : " . $gmail->getMessageHeader($mail, 'Content-Type') .
                "\n  - Content-Transfer-Encoding : " . $gmail->getMessageHeader($mail, 'Content-Transfer-Encoding') .
                "\n  - To : " . $gmail->getMessageHeader($mail, 'To') .
                "\n  - Date : " . $gmail->getMessageHeader($mail, 'Date') . 
                "\n  - Date (parsed) : " . $dt . 
                "\n" .
                "\n  - Body headers : " . print_r($body->headers, true) .
                "\n  - Body Content-Type : " . $body->mimeType . 
                "\n  - Body content (first 100 characters) : " .
                "\n============\n" .
                "\n" . substr(htmlentities(print_r($body->body, true)), 0, 100) .
                "\n============\n" .
                "\n  - Attachments : " . (count($attachments)?'yes':'no') .
                (count($attachments)? "\n  - First attachment file-name and Content-Type : " . $attachments[0]->name . ' (' . $attachments[0]->mimeType . ')' :'') .
                "\n  - Inline attachments : " . (count($inlineattachments)?'yes':'no') .
                (count($inlineattachments)? "\n  - First inline attachment Content-ID and Content-Type : " . htmlentities($inlineattachments[0]->name) . ' (' . $inlineattachments[0]->mimeType . ')' :'');

            $html .= "\n\n********\n\n" .
                     "First email with (HTML content and inline attachments are displayed, if any) :\n" .
                     $gmail->getMessageBodyWithInlineAttachments($mail, 'me')->body;
            
        // if we arrive here, the authorization is good
        print_r("<pre style=\"padding:5px; background-color:lightgray;\">" . $html . "</pre>");
        
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

<p><a href="Gmail.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>
