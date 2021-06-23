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
use \Nettools\GoogleAPI\ServiceWrappers\PeopleService;



?>
<html>
    <head>
        <title>PeopleService sample</title>
    </head>
<body>
<?php

		
// creating the interface to Google APIs 
$gint = new Serverside_InlineCredentials(CLIENT_ID, CLIENT_SECRET, array(\Google\Service\PeopleService::CONTACTS));


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
    // getting PeopleService service wrapper ; returns a \Nettools\GoogleAPI\ServiceWrappers\PeopleService object
    $service = $gint->getService('PeopleService');
    
    
    
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
		$johndoe = NULL;
        //foreach ( $service->people_connections->listPeopleConnections('people/me', ['personFields'=>'names,emailAddresses,photos,metadata,biographies']) as $contact )
		foreach ( $service->getAllContacts('people/me', ['personFields'=>'names,emailAddresses,photos,metadata,biographies']) as $contact )
        {
			$name = count($contact->getNames()) ? $contact->getNames()[0] : (object)['displayName' => '<no name>'];
						
			if ( $contact->getMetadata()->deleted )
				$c = $name->displayName . ' **DELETED**';
			else
            	$c = $name->displayName . ' (' . (count($contact->getEmailAddresses())?$contact->getEmailAddresses()[0]->value:'') . ")";
			
			
			// testing for photo
			if ( !$contact->getPhotos()[0]->{'default'} )
            {
                $c .= ' - PHOTO AVAILABLE';
                $lastphotoid = $contact->getPhotos()[0]->url;
            }
			
			
			// if john doe contact
			if ( $name->displayName == 'John Doe' )
				$johndoe = $contact->resourceName;

            $contacts[] = $c;
        }

        // formatting output with a list of contacts
        $html = "<pre>Contacts list :\n====================\n\n" . implode("\n",$contacts)  . '</pre>';



        // if at least one contact has a photo, displaying the photo
        if ( $lastphotoid )
        {
            $photo = file_get_contents($lastphotoid);
            $html .= "<pre>\n\nLast photo link found : <img src=\"data:image/jpeg;base64,". base64_encode($photo) . "\"></pre>";
        }
		


		
        // creating another contact
        /*$c = new Google_Service_PeopleService_Person([
			'names'		=> array([
				'familyName'	=> 'Smith',
				'givenName'		=> 'John'
			]),
			'emailAddresses' => array([
				'value'			=> 'john.smith@test.com',
				'type'			=> PeopleService::TYPE_HOME,
				'metadata'		=> ['primary' => true]
			])
		]);*/
        $c = new Google\Service\PeopleService\Person([
			'emailAddresses' => array([
				'value'			=> 'john.smith@test.com',
				'type'			=> PeopleService::TYPE_HOME,
				'metadata'		=> ['primary' => true]
			])
		]);
		$c->setNames([new \Google\Service\PeopleService\Name(['familyName' => 'Smith', 'givenName' => 'John'])]);
		

		$newc = $service->people->createContact($c, ['personFields' => '']);
        $html .= "<pre>New contact 'John Smith' created with id '" . $newc->resourceName . "'</pre>";


		
		
		
        // modifying the last john doe contact found
        if ( $johndoe )
        {
			// get contact
			$johndoe = $service->people->get($johndoe, ['personFields' => 'biographies']);
			
			
            $bios = $johndoe->getBiographies();
			if ( !count($bios) )
			{
				$johndoe->setBiographies([new Google\Service\PeopleService\Biography()]);
				$bios = $johndoe->getBiographies();
			}
			
			$bios[0]->value .= '-Updated on ' . date('Y/m/d H:i:s') . '-';

            // updating contact
            $new_johndoe = $service->people->updateContact($johndoe->resourceName, $johndoe, 
														   	[
																'updatePersonFields'	=> 'biographies',
																'personFields'			=> ''
															]
														  );
            $html .= "<pre>\n\n====================\n\nContact 'John Doe' with resourceName $johndoe->resourceName has been updated</pre>";
        }


		
		
        // deleting the contact just created
        $service->people->deleteContact($newc->resourceName);
        $html .= "<pre>New contact 'John Smith' deleted with id '" . $newc->resourceName . "'</pre>";


	

        /* 
        ============
        TESTING GROUPS
        ============
        */


        // listing groups
        $groups = [];
        $johndoefriends = NULL;
        $mycontacts = NULL;
        foreach ( $service->listAllContactGroups() as $group )
        {
            $groups[] = $group->name . ' - group type : ' . $group->groupType;
            
            // remember the id of system group 'Contacts'
            if ( ($group->groupType == PeopleService::SYSTEM_CONTACT_GROUP_TYPE) && ($group->resourceName == PeopleService::SYSTEM_CONTACT_GROUP_MYCONTACTS) )
                $mycontacts = $group->id;

            if ( is_int(strpos(strtolower($group->name), 'john doe')) )
                $johndoefriends = $group;
        }

        // formatting output with a list of groups
        $html .= "<pre>\n\nGroups list :\n====================\n\n" . implode("\n",$groups)  . '</pre>';

		
		
		
        // modifying group 'john doe friends'
		if ( $johndoefriends )
		{
			$old = $johndoefriends->name;
			$johndoefriends->name = 'john doe friends - ' . date('Ymd His');
			$service->contactGroups->update($johndoefriends->resourceName, new Google\Service\PeopleService\UpdateContactGroupRequest(
					[
						'contactGroup' => $johndoefriends
					]
				));
			
			$html .= "<pre>\nGroup '$old' has been renamed to '$johndoefriends->name'</pre>";
		}

		
		
        // creating a group 'john doe family'
        $johndoefamily = new Google\Service\PeopleService\ContactGroup(['name'	=> 'john doe family ' . date('Ymd His')]);
		$johndoefamily = $service->contactGroups->create(new Google\Service\PeopleService\CreateContactGroupRequest(
				[
					'contactGroup' => $johndoefamily
				]
			));
				
        $html .= "<pre>\nGroup '$johndoefamily->name' has been created with resourceName '$johndoefamily->resourceName'</pre>";

		
		
		// sleeping, as it has been seen that newly created group is in fact not created immediately		
		sleep(10);
				
		
		// adding 'john doe' contact to 'john doe family' group
		$service->contactGroups_members->modify($johndoefamily->resourceName, new Google\Service\PeopleService\ModifyContactGroupMembersRequest(
				[
					'resourceNamesToAdd' => [$johndoe->resourceName]
				]
			));


		
		// fetching current johndoe contact, with up-to-date etag value
		sleep(6);
		$johndoe = $service->people->get($johndoe->resourceName, ['personFields'=>'memberships']);
		
		
		// if john doe is not part of Starred group yet
		if ( !PeopleService::isContactMemberOfGroup($johndoe, PeopleService::SYSTEM_CONTACT_GROUP_STARRED) )
		{
			PeopleService::addContactGroupMembership($johndoe, PeopleService::SYSTEM_CONTACT_GROUP_STARRED);
            $service->people->updateContact($johndoe->resourceName, $johndoe, 
														   	[
																'updatePersonFields'	=> 'memberships',
																'personFields'			=> ''
															]
														  );
            $html .= "<pre>\n\n====================\n\nContact 'John Doe' with resourceName $johndoe->resourceName has been updated with Starred membership</pre>";
		}
		
		// else toggle starred group membership
		else
		{
			PeopleService::removeContactGroupMembership($johndoe, PeopleService::SYSTEM_CONTACT_GROUP_STARRED);
            $service->people->updateContact($johndoe->resourceName, $johndoe, 
														   	[
																'updatePersonFields'	=> 'memberships',
																'personFields'			=> ''
															]
														  );
            $html .= "<pre>\n\n====================\n\nContact 'John Doe' with resourceName $johndoe->resourceName has been removed from Starred group membership</pre>";
		}
		
				

        /* 
        ============
        TESTING PHOTO UPLOAD
        ============
        */

        if ( $lastphotoid )
        {
	 		// creating another contact
			$c = new Google\Service\PeopleService\Person([
				'names'		=> array([
					'familyName'	=> 'Smith' . uniqid(),
					'givenName'		=> 'Jason'
				]),
				'emailAddresses' => array([
					'value'			=> 'jason.smith@test.com',
					'type'			=> PeopleService::TYPE_HOME,
					'metadata'		=> ['primary' => true]
				]),
				'nicknames'		=> array([
					'value'		=> 'Jay'
				]),
				'birthdays'		=> array([
					'date'		=> ['year'=>1978, 'month'=>4, 'day'=>27],
					'text'		=> '27/04/1978'
				]),
				'urls'			=> array([
					'value'		=> 'http://nettools.ovh',
					'type'		=> 'profile'
				]),
				'userDefined'	=> array([
					'key'		=> 'my key',
					'value'		=> 'my value'
				])
			]);


			// send request
			$newc = $service->people->createContact($c, ['personFields' => ['names']]);
			$html .= "<pre>New contact 'Jason Smith' created with id '" . $newc->resourceName . "'</pre>";
			
            // send the picture
            $service->people->updateContactPhoto($newc->resourceName, new Google\Service\PeopleService\UpdateContactPhotoRequest(
					[
						'photoBytes'	=> base64_encode(file_get_contents($lastphotoid))
					]
				));

            $html .= "<pre>\nPhoto above uploaded for contact '" . $newc->getNames()[0]->displayName . "'</pre>";
        }




        // output
        print_r("<div style=\"padding:5px; background-color:lightgray;\">" . $html . "</div>");
        
    }
    // catch errors 
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

<p><a href="PeopleService.php?token=<?php echo urlencode(json_encode($gint->client->getAccessToken())); ?>">Refresh page</a></p>
