# Contacts API

## Requirements

To use our Contacts API, please refer first to the top README file, as it provides information about mandatory setup stuff (such as creating google developper credentials).

Our API requires the Google PHP API (mainly used to authenticate the requests), and it will be included automatically through Composer.



## API reference ##

It's strongly recommanded to have a basic knowledge of the Contacts API reference before going further : https://developers.google.com/google-apps/contacts/v3/reference



## Contacts ##

Dealing with contacts is done through the `contacts` ressource and its methods :

```php
// if using our way of getting the service (through the \Nettools\GoogleAPI\Clients\GoogleClient interface) :
$service = $ginterface->getService('Contacts');

// or, if you prefer using a Google_Client object you have already created through the Google API library :
$service = new \Nettools\GoogleAPI\Services\Contacts_Service($gclient);

// then :
$list = $service->contacts->getList();
$service->contacts->get($contact_selflink);
$service->contacts->create($contact_object);
$service->contacts->update($contact_object);
$service->contacts->delete($contact_editlink);
```

The `$ginterface` var is a `\Nettools\GoogleAPI\Clients\GoogleClient` object you have to create with any required credentials. Please refer to samples or the top README.md file for more information.



### Getting a list of contacts

The code is very similar to any other Calendar or Gmail call to list events or messages :

```php
// listing contacts with name + email address
// this simple test implies all contacts DO have an email address ; otherwise 'emails[0]->address' will throw an exception, as [0] will be undefined
foreach ( $service->contacts->getList() as $contact )
   echo $contact->title . ' ' . $contact->emails[0]->address;  
```
You can see that properties with unique values are refered to with their name (eg. `->familyName`), whereas properties which may have multiple values (such as email) are refered to with the plural property name (eg. `->emails` or `->websites`) and must be enumerated with a `foreach` loop or through a array index.

The `$contact` variable is of `\Nettools\GoogleAPI\Services\Contacts\Contact` class, and holds all data associated to the contact. Please refer to our API reference (see below) to further information.

Please note that the `ListContacts` object returned by `getList()` is a collection that can be iterated once. If you have to iterate several times, you must cache the first iteration or use `CachedCollection` class :

```php
$cache = new \Nettools\GoogleAPI\Services\Misc\CachedCollection($service->contacts->getList());

// first loop
foreach ( $cache as $contact )
   echo $contact->title . ' ' . $contact->emails[0]->address;  

// second loop
foreach ( $cache as $contact )
   echo $contact->title . ' ' . $contact->emails[0]->address;  
```



### Creating a contact


```php
$c = new \Nettools\GoogleAPI\Services\Contacts\Contact();
$c->familyName = 'Smith';
$c->givenName = 'John';
$c->title = $c->givenName . ' ' . $c->familyName;
$c->emails = array((object)array(
   'address' => 'john.smith@test.com',
   'primary' => true,
   'rel' => \Nettools\GoogleAPI\Services\Contacts\Contact::TYPE_HOME
));

// send the request to create the contact and get an updated Contact object with etag, links and other api-related stuff
$newc = $service->contacts->create($c);
```



### Updating a contact

Simply get the contact first, apply updates to the `Contact` object, and send modifications :

```php
$service->contacts->update($c);
```



### Deleting a contact

You have to know the contact edit-link in order to delete it through the API. Assuming you have a `$c` `Contact` object previously fetched :

```php
$service->contacts->delete($c->linkRel('edit')->href, $c->etag);
```

We use the `linkRel()` method of `Contact` object to get the link whose `rel` attribute is `edit` (this is in fact an URI) and we send the delete request, along with the contact `etag` property (so that we do not erase a contact that has been modified since we fetch its edit link). This mechanism is explained in the Google API Contacts reference.



### Getting a contact

You have to know the contact self-link in order to it through the API. For example, you may have stored a contact self-link and its etag in a database, and wish to have a look at the contact to detect updates (thanks to the current etag value) :

```php
$c = $service->contacts->get($selflink);

if ( $c->etag != $etag )
   echo "Etag in contact does not match etag in database ; contact has been updated !";
```





## Groups ##

Dealing with groups is done through the `groups` ressource and its methods :

```php
$service = $ginterface->getService('Contacts');

$list = $service->groups->getList();
$service->groups->create($group_object);
$service->groups->update($group_object);
$service->groups->delete($group_editlink);
```



### Getting a list of groups

The code is very similar to any other Calendar or Gmail call to list events or messages :

```php
// listing groups
foreach ( $service->groups->getList() as $group )
   echo $group->title;
```

The `$group` variable is of `\Nettools\GoogleAPI\Services\Contacts\Group` class, and hold all data associated to the group. Please refer to our API reference (see below) to further information.

Please note that the `ListGroups` object returned by `getList()` is a collection that can be iterated once. If you have to iterate several times, you must cache the first iteration or use `CachedCollection` class :

```php
$cache = new \Nettools\GoogleAPI\Services\Misc\CachedCollection($service->groups->getList());

// first loop
foreach ( $cache as $group )
   echo $group->title;

// second loop
foreach ( $cache as $group )
   echo $group->title;
```


### Creating a group

```php
$g = new \Nettools\GoogleAPI\Services\Contacts\Group();
$g->title = 'new group here!';

// send the request to create the group and get an updated Group object with etag, links and other api-related stuff
$newg = $service->groups->create($g);
```



### Updating a group

Simply get the group first, apply updates to the `Group` object, and send modifications :

```php
$service->groups->update($g);
```



### Deleting a group

You have to know the group edit-link in order to delete it through the API. Assuming you have a `$g` `Group` object previously fetched :

```php
$service->groups->delete($g->linkRel('edit')->href, $g->etag);
```

We use the `linkRel()` method of `Group` object to get the link whose `rel` attribute is `edit` (this is in fact an URI) and we send the delete request, along with the group `etag` property (so that we do not erase a group that has been modified since we fetch its edit link). This mechanism is explained in the Google API Contacts reference.





## Photos ##

### Getting a photo 

To get a contact photo, you have to know the contact photo link by querying the `Contact` object and its links. Then you can send the request :

```php
$photolink = $contactwithphoto->linkRel(\Nettools\GoogleAPI\Services\Contacts\Contact::TYPE_PHOTO));
$photo = $service->contacts_photos->get($photolink);
```

We have the binary data of photo in `$photo->body` and its content-type in `$photo->contentType`.



### Creating/updating a photo 

To update a contact photo, you have to know the contact photo link by querying the `Contact` object and its links. Then you can send the request :

```php
$photolink = $contactwithphoto->linkRel(\Nettools\GoogleAPI\Services\Contacts\Contact::TYPE_PHOTO));
$photo = \Nettools\GoogleAPI\Services\Contacts\Photo::fromData('image/jpeg', file_get_contents('image.jpeg'));
$photo = $service->contacts_photos->update($photo, $photolink);
```

We create a `Photo` object to store the binary data of photo and its content-type. Then we send the request to the photo link.



### Deleting a photo 

To delete a contact photo, you have to know the contact photo link by querying the `Contact` object and its links. Then you can send the request :

```php
$photolink = $contactwithphoto->linkRel(\Nettools\GoogleAPI\Services\Contacts\Contact::TYPE_PHOTO));
$service->contacts_photos->delete($photolink);
```

Here, we omitted the etag second parameter of `delete()` method. It defaults to `'*'` meaning the photo will be deleted even if it has been modified recently. If we had passed an etag value, the photo won't have been deleted if etag values didn't match.



### Syncing contacts 

We have a contacts syncing tool which implements all syncing stuff needed to sync the Google contacts with your own contacts repository (database, mailing-list, etc.).

First, create an object of `\Nettools\GoogleAPI\Tools\ContactsSyncManager\Manager` class, with required parameters : `Google_Client` object, your own implementation of `\Nettools\GoogleAPI\Tools\ContactsSyncManager\ClientInterface` interface tailored to your contacts repository and environment, and syncing constant(s).

```php
$cintf = new MyInterface(); // MyInterface is a user-class implementing ClientInterface
$m = new Manager($g_client, $cintf, Manager::ONE_WAY_FROM_GOOGLE | Manager::ONE_WAY_DELETE_FROM_GOOGLE);
```

Then call the `sync` method with a `Psr\Log` object and the timestamp of last sync (or 0 if unknown) :

```php 
$b = $m->sync(new \Psr\Log\NullLogger(), 0);
// $b equals True if success
```

The `Manager` class handles the sync process but you DO have to implement some concrete methods (so that the sync manager knows contacts to be updated from your repository, or to commit Google-side updates to your repository), such as writing updates to your database.

This is done through the `ClientInterface` interface you have to implement :

method            | description
------------------|---------------
`getLogContext`     | The manager logs every sync or exception and associates a context record from the Contact object being synced (by default, familyName, givenName and ID) ; you may override the default behavior and provide more information
`getContactInfoClientSide` | From your contacts repository, return a litteral object with `etag` and `clientsideUpdateFlag` properties (`etag` must contain the last etag known from Google side, set during the last sync ; `clientsideUpdateFlag` should be set with TRUE if the contact has been updated on your repository, and thus needs to be updated Google-side.
`updateContactClientside` | Commit Google-side updates for a contact on your repository
`getUpdatedContactsClientside` | Get a list of updated contacts from your repository (an array of object litterals with `contact` and `etag` properties for each updated contact ; the `contact` property must be set with a `Contact` object)
`acknowledgeContactUpdatedGoogleside` | When an updated has been committed Google-side, use this callback to grab the new etag values and keep it in your repository
`getDeletedContactsClientside` | Get a list of contacts deleted client-side (refered by their Google IDs)
`acknowledgeContactDeletedGoogleside` | When a deletion has been executed Google-side, use this callback to remove the contact from your repository
`deleteContactClientside` | Commit Google-side deletions to your repository



## Our API Reference

Please refer to the phpdoc repository : http://net-tools.ovh/api-reference/net-tools/Nettools/GoogleAPI.html

In particular, have a look at the contacts, groups and photos resources which hold all methods to create/read/update/delete items : http://net-tools.ovh/api-reference/net-tools/Nettools/GoogleAPI/Services/Contacts/Res.html .

