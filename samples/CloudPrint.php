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



// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(\Nettools\GoogleAPI\Services\CloudPrint_Service::CLOUDPRINT));


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
}

    
    
    
try
{
    // getting CloudPrint service from our library ; returns a \Nettools\GoogleAPI\Services\CloudPrint object
    $service = $gint->getService('CloudPrint');
    
    
    try
    {
        ?>
        <html>
            <head>
                <title>CloudPrint sample</title>
            </head>
        <body>
        <?php


        
        /* 
        ============
        PRINTERS
        ============
        */
        $printers=[];
        $lastprinterid = NULL;
        foreach ( $service->printers->search() as $printer )
        {
            $printers[] = $printer->displayName . " ($printer->description) - $printer->id";
            
            // remember last real printer found (excluding "drive" printer to "print" to pdf)
            if ( $printer->type == 'GOOGLE' )
                $lastprinterid = $printer->id;
        }

        // formatting output with a list of printers
        $html = "<pre>Printers list :\n====================\n\n" . implode("\n",$printers)  . '</pre>';


        if ( $lastprinterid )
        {
            $printer = $service->printers->get($lastprinterid);
            $prn = [];
            $prn[] = "Manufacturer : " . $printer->manufacturer;
            $prn[] = "Model : " . $printer->model;
            $papersizes = [];
            
            try
            {
                foreach ( $printer->capabilities->printer->media_size->option as $format )
                    $papersizes[] = $format->custom_display_name;
                
            }
            catch(\Throwable $e)
            {
                $papersizes = ['Unkown capabilities'];
            }
            
            
            $prn[] = "Paper sizes : " . implode(' - ', $papersizes);
                
            $html .= "<pre>\n\nLast printer details :\n====================\n\n" . implode("\n",$prn)  . '</pre>';
        }

        
        
        /* 
        ============
        JOBS
        ============
        */
        $jobs=[];
        $lastid = NULL;
        foreach ( $service->jobs->search() as $job )
        {
            $jobs[] = "Job $job->id printed at " . date('H:i:s Y/m/d', ((int) substr($job->updateTime, 0, -3))) . " with title '$job->title' ($job->numberOfPages pages) ; status '$job->status'";
            if ( $job->status == 'DONE' )
                $lastid = $job->id;
        }

        // formatting output with a list of printers
        $html .= "<pre>\n\nJobs list :\n====================\n\n" . implode("\n",$jobs)  . '</pre>';
        
        
        // getting details about last DONE job 
        if ( $lastid )
        {
            // get proxy property of printer associated with this print job
            $job = $service->jobs->get($lastid);
            $html .= "<pre>\n\nJob with id '$lastid' has the title '$job->title' ; job is '$job->status'.</pre>";
        }

        
        
        // deleting last job with status DONE
        if ( $lastid )
        {
            $service->jobs->delete($lastid);
            $html .= "<pre>\n\n'DONE' job with id '$lastid' has been deleted.</pre>";
        }

        
        
        // if one GOOGLE CLOUD PRINT enabled printer is found
        if ( $lastprinterid )
        {
            // submitting a PDF file to print through an URL
            $url = 'http://' . $_SERVER['SERVER_NAME'] . '/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'],'', rtrim(__DIR__,'/')),'/') . '/test.pdf';
            $job = $service->jobs->submitUrl($lastprinterid, 'Sample downloaded by CloudPrint to print on ' . date('Ymd H-i-s'), $url);

            $html .= "<pre>\n\nTest job downloaded by CloudPrint and sent to printer with id '$job->printerid' ; current status : '$job->status'.</pre>";


            // submitting a PDF file to print through direct upload to CloudPrint
            $job = $service->jobs->submit($lastprinterid, 'Sample uploaded to CloudPrint to print on ' . date('Ymd H-i-s'), file_get_contents(__DIR__ . '/test.pdf'), 'application/pdf');

            $html .= "<pre>\n\nTest job uploaded to CloudPrint and sent to printer with id '$job->printerid' ; current status : '$job->status'.</pre>";
        }


        
        // output
        print_r("<div style=\"padding:5px; background-color:lightgray;\">" . $html . "</div>");
    }
    // catch auth errors 
    catch (Google_Service_Exception $e)
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

<p><a href="CloudPrint.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>

<?php
if ( $token = json_decode($_GET['token'], true) )
    echo "<p style=\"color:red; font-weight:bold; \">Using previously obtained token, which expires at " . date('Y-m-d H:i:s', $token['created'] + $token['expires_in']) . "</p>";
