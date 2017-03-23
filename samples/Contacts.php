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
use \Nettools\GoogleAPI\Services\Contacts\Contact;
use \Nettools\GoogleAPI\Services\Contacts\Group;



?>
<html>
    <head>
        <title>Contacts sample</title>
    </head>
<body>
<?php



// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(\Nettools\GoogleAPI\Services\Contacts_Service::CONTACTS));


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
    // getting Contacts service from our library ; returns a \Nettools\GoogleAPI\Services\Contacts object
    $service = $gint->getService('Contacts');
    
    
    
    // catching no authorization issues
    try
    {
        /* 
        ============
        TESTING CONTACTS    
        ============
        */

        // listing contacts (title + first email address provided by the API [may be personnal or professionnal])
        $contacts=[];
        $lastphotoid=NULL;
        foreach ( $service->contacts->getList() as $contact )
        {
            $c = $contact->title . ' (' . ($contact->emails[0]?$contact->emails[0]->address:'') . ')';
            if ( ($photolnk = $contact->linkRel(Contact::TYPE_PHOTO)) && $photolnk->etag )
            {
                $c .= ' - PHOTO AVAILABLE';
                $lastphotoid = $photolnk->href;
            }

            $contacts[] = $c;
        }

        // formatting output with a list of contacts
        $html = "<pre>Contacts list :\n====================\n\n" . implode("\n",$contacts)  . '</pre>';



        // if at least one contact has a photo, displaying the photo
        if ( $lastphotoid )
        {
            $photo = $service->contacts_photos->get($lastphotoid);
            $html .= "<pre>\n\nLast photo link found (content-type '$photo->contentType') : <img src=\"data:$photo->contentType;base64,". base64_encode($photo->body) . "\"></pre>";
        }


        // creating a contact
        $c = new Contact();
        $c->familyName = 'Smith';
        $c->givenName = 'John';
        $c->title = $c->givenName . ' ' . $c->familyName;
        $c->emails = array((object)array(
                                    'address' => 'john.smith@test.com',
                                    'primary' => true,
                                    'rel' => Contact::TYPE_HOME
                                ));

        // send the request to create the contact and get an updated Contact object with etag, links and other api-related stuff
        $newc = $service->contacts->create($c);
        $html .= "<pre>New contact 'John Smith' created with etag '$newc->etag' and id '" . $newc->linkRel('self')->href . "'</pre>";


        // listing contacts again but with a query (standard google API Q parameter, which searches in all text fields)
        $contacts=[];
        $johndoe = NULL;
        foreach ( $service->contacts->getList('default', array('q'=>'john doe')) as $contact )
        {
            $contacts[] = $contact->title . ' (' . ($contact->emails[0]?$contact->emails[0]->address:'') . ') - etag ' . $contact->etag;
            $johndoe = $contact;
        }

        // formatting output with a list of contacts
        $html .= "<pre>\n\nContacts list with any field matching 'john doe' :\n====================\n\n" . implode("\n",$contacts)  . '</pre>';


        // modifying the last john doe contact found
        if ( $johndoe )
        {
            $johndoe->content = $johndoe->content . '-Updated on ' . date('Y/m/d H:i:s') . '-';

            // updating contact
            $new_johndoe = $service->contacts->update($johndoe);
            $html .= "<pre>\n\n====================\n\nContact 'John Doe' (etag '$johndoe->etag') has been updated with the API timestamp '" . date("Y/m/d H:i:s", $new_johndoe->updated) . "' and etag '$new_johndoe->etag'</pre>";
        }


        // deleting the contact just created
        if ( $service->contacts->delete($newc->linkRel('edit')->href, $newc->etag) )
            $html .= "<pre>\nJust created contact 'John Smith' has been deleted !</pre>";




        /* 
        ============
        TESTING GROUPS
        ============
        */


        // listing groups
        $groups = [];
        $johndoefriends = NULL;
        $mycontacts = NULL;
        foreach ( $service->groups->getList() as $group )
        {
            $groups[] = $group->title . ($group->systemGroup ? ' (System group ' . $group->systemGroup . ')': '');
            
            // remember the id of system group 'Contacts'
            if ( $group->systemGroup == 'Contacts' )
                $mycontacts = $group->id;

            if ( is_int(strpos(strtolower($group->title), 'john doe')) )
                $johndoefriends = $group;
        }

        // formatting output with a list of groups
        $html .= "<pre>\n\nGroups list :\n====================\n\n" . implode("\n",$groups)  . '</pre>';


        // modifying group 'john doe friends'
        $johndoefriends->title = 'john doe friends - ' . date('Ymd His');
        $new_johndoefriends = $service->groups->update($johndoefriends);

        $html .= "<pre>\n\n====================\n\nGroup '$johndoefriends->title' has been updated with a new title reflecting current timestamp '$new_johndoefriends->title'</pre>";


        // creating a group 'john doe family'
        $g = new Group();
        $g->title = 'john doe family ' . date('Ymd His');
        $johndoefamily = $service->groups->create($g);

        $html .= "<pre>\nGroup '$johndoefamily->title' has been created with id '$johndoefamily->id'</pre>";


        // assign a group to contact john doe
        $new_johndoe->groupsMembershipInfo = array_merge($new_johndoe->groupsMembershipInfo, array($johndoefamily->id));
        $new_johndoe = $service->contacts->update($new_johndoe, true);


        



        /* 
        ============
        TESTING PHOTO UPLOAD
        ============
        */

        if ( $lastphotoid )
        {
            // creating a contact with a photo
            $c = new Contact();
            $c->familyName = 'Smith' . uniqid();
            $c->givenName = 'Jason';
            $c->title = $c->givenName . ' ' . $c->familyName;
            $c->emails = array((object)array(
                                        'address' => 'jason.smith@test.com',
                                        'primary' => true,
                                        'rel' => Contact::TYPE_HOME
                                    ));
            $c->nickName = 'Jay';
            $c->birthday = '1978-04-27';
            $c->userDefinedFields = array((object)['key'=>'my key', 'value'=>'my value']);
            $c->websites = array((object)['href'=>'http://nettools.ovh', 'rel'=>'profile']);
            $c->groupsMembershipInfo = array($mycontacts);
            

            // send the request to create the contact and get an updated Contact object with etag, links and other api-related stuff
            $newc = $service->contacts->create($c);
            $html .= "<pre>\n\n====================\n\nNew contact 'Jason Smith' created with etag '$newc->etag' and id '" . $newc->linkRel('self')->href . "'</pre>";

            // send the picture
            $service->contacts_photos->update($photo, $newc->linkRel(Contact::TYPE_PHOTO)->href);

            $html .= "<pre>\nPhoto above uploaded for contact 'Jason Smith'</pre>";
        }




        // output
        print_r("<div style=\"padding:5px; background-color:lightgray;\">" . $html . "</div>");
        
    }
    // catch auth errors 
    catch (Google_Service_Exception $e)
    {
        // get the url to begin the authorization process (by redirecting the user to Google login)
        $url = $gint->beginAuthorizationProcess(true);
        echo "<pre style=\"padding:5px; background-color:lightgray;\">API error / Not authorized / API not enabled in console ; please <a href=\"$url\">click here</a> to go to Google login<br><br>{$e->getMessage()}</pre>";
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

<p><a href="Contacts.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>
