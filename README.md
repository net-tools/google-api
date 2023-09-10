# net-tools/google-api

## PHP Interface to Google APIs

This packages contains :

- some service helpers to main Google products, such as Calendar, Drive, or Gmail, 




## Setup instructions ##

To install net-tools/google-api package, just require it through composer : require net-tools/google-api:^1.0.0.

In order to use the classes contained in this library, **please read CAREFULLY this document**, as **you have to setup your Google dev account first**. Basic knowledge about Google APIs is also required.




## Overview of Google app credentials and authorization process

### Note

The Google API can be a bit confusing at first, especially the initial setup for application credentials (**to do only once**) and authorization process (**to do for each API request**). 

I strongly advise you to read any reference about those topics from Google Developper reference (for example, https://developers.google.com/identity/protocols/OAuth2WebServer or https://developers.google.com/google-apps/calendar/auth for a Calendar service auth) before reading further topics here.



### Identifying your application (app credentials)

Making requests to Google APIs through PHP requires your application to be "identified" (so that Google may apply quotas). This way, users granting your application access to their personnal Google data can clearly identify your application before allowing access.

Before running any of the samples here or coding your application, you **MUST** setup your Google dev account (see below 'Setting up your Google dev account'). This is mandatory. Then, when dealing with the API, you will identify your application with credentials obtained during the Google dev account setup.



### Getting access to user data (authorization process)

Making requests to Google APIs through PHP requires your application to be allowed to create/read/update/delete data in the user's account. That's called the authorization process. Depending on the grants (called scopes in the API) you ask for, your application may be only allowed to read data, but not update stuff.

From your application, the user is redirected to Google login page and asked to allow access to it's personnal data for a service (Calendar, Gmail, etc.). If he accepts, he is redirected back to your application, which gets an access token, allowing free access for 1 hour (after that delay, the user should identify again). You may store the token in database or session to reuse it during that delay. Refresh tokens also exist, with no 1 hour limit (off-topic here).




## Setting up your Google dev account 

The setup is done by registering a new application in Google Developper Console :

1. open the link to Google Dev Console : https://console.developers.google.com/
1. on the left, click on `Credentials`
1. create a new project and type a name (a project may contain several products, so be general, for example type your name or company) ; confirm
1. on the left, click on `Library`, then choose the API to enable. On the API page resume, click on `Enable`
1. repeat previous step for any other required API
1. on the left, click on `Credentials`
1. don't click on `Create credentials` button yet, as we have to setup the OAuth consent screen first ; open the `OAuth consent screen` tab
1. type in a product name so that the users can identify your application (they are going to give your application access to their personnal data, so this is very important to clearly identify your app), and hit `Save`
1. now, you can click on `Create credentials`
1. select `Web application` ; enter a name for your web app ; enter the `Authorized redirect URIs` ; this is the URL that Google will redirect your users to when they have passed the Google authorization process. If you are running one of the samples here, type the full path to the sample file, including your domain.
1. click on `Create`


At this point, take note of the credentials displayed on screen :

- your client ID,
- your client secret.




## Running the samples

If you have not setup your Google dev account yet, please do it now (see chapter above).

To run the samples, modify the lines of `Credentials.php` by typing the client ID and secret available after the Google dev account setup on the developper console. Depending on which sample you are running, more config data may be required, and this will be mentionned in the sample file top comments.

Some steps must be taken for some samples to run successfuly (such as creating test events, contacts, emails), please refer to the according Readme file in the samples subdirectory.




## Using Google APIs

Here are some links to Google APIs reference ; you should read them before reading further here, since this package is only a frontend to Google APIs. You still have to manage with Google APIs :

- Calendar : https://developers.google.com/google-apps/calendar/v3/reference/
- Drive : https://developers.google.com/drive/v3/reference/
- Gmail : https://developers.google.com/gmail/api/v1/reference/
- Contacts : https://developers.google.com/people/api/rest

For each API, don't forget to read the `Guides` section on the top navigation bar.




## Using this packages classes

All calls to Google API require at least one object, called the *Client* ; many Google API also require a second object, called the *Service*. You may see the *Client* as the link or interface to the API ('link' is to understand as the communication medium), whereas the *Service* contains business methods (creating, deleting things).


### Creating a Client object

Creating the *Client* object is rather straightforward :

```php
$gclient = new Nettools\GoogleAPI\Clients\Serverside_InlineCredentials(
        CLIENT_ID, 
        CLIENT_SECRET, 
        array(\Google\Service\Calendar::CALENDAR_READONLY)
    );
``` 

We create an object of `Serverside_InlineCredentials` class, identifying the application with credentials from developper console, and requesting a readonly access to the user Calendar data. It creates a `\Google\Client` object behind the scenes (underlying object, the object we create is only a frontend). Some API mandatory parameters are set with default values (for example, *redirectUri* points to the script URL).

If you prefer identifying with Json credentials and not strings in code, use `Serverside_JsonCredentials`. If you are using a service account, use `ServiceAccount`. *Serverside* or *ServiceAccount* prefix in class names tell us the kind of application we are dealing with (please refer to Google API for further explanations about server-side or service accounts).

If you have an access token previously obtained, you can pass it to the constructor : 

```php
$gclient = new Nettools\GoogleAPI\Clients\Serverside_InlineCredentials(
        CLIENT_ID, 
        CLIENT_SECRET, 
        array(\Google\Service\Calendar::CALENDAR_READONLY),
        array(
            'accessToken' => $token
        )
    );
``` 

or call later `setAccessToken()` through the `client` accessor property to the underlying `\Google\Client` object :

```php
$gclient->client->setAccessToken($token);
```


### Creating a Service object

Now that we have a *Client* object, we can use it to get a *Service* object for a particular API :

```php
$cal = $gclient->getService('Calendar');
$response = $cal->events->listEvents('primary');
```

The `getService()` method is inherited from `Clients\GoogleClient` ; it creates a *Service* object making it possible to issue API calls to Google services. The kind of object created depends on several parameters, explained in the following chapter.



### Service wrappers, services API implemented here and Google_Service

Depending on whether our library has a service wrapper for the target service or not (such as Gmail or Calendar), whether our library implements a service API or not, `getService()` returns either a service wrapper (inheriting from `ServiceWrappers\ServiceWrapper`) or a service object from our library (inheriting from `Services\Service`) or a `\Google\Service` object directly created from Google API library.

The rule is that if the service asked is defined in the Google API library, and we have a service wrapper for it in our library, the service wrapper will be used (Gmail, Calendar, PeopleService, Drive). If no service wrapper available, the `\Google\Service` object is created from the Google API library. If the service asked is not implemented in the Google API library, we try to create the service object from our library. 

The service wrappers of our library provide some useful functionnalities and act as frontends (facade pattern) to the underlying Google APIs. This is clearly visible for the Gmail service wrapper (it implements methods to decode body parts and attachments).

If you are asking for a service for which we have a `ServiceWrappers\ServiceWrapper` object, the object returned by `getService()` is an instance of `ServiceWrappers\ServiceWrapper`. However, our wrappers implement a forward mechanism for properties and method calls : method calls for methods not defined in a wrapper are forwarded to the underlying `\Google\Service` object. Same thing for the properties. You may write : 

```php
$response = $cal->events->listEvents('primary');
// or explicitely invoke the underlying service object : 
$response = $cal->service->events->listEvents('primary');
```



## Handling errors

If the error comes from the Google API, a \Google\Exception or \Google\Service\Exception will be thrown. 

You have to intercept the exception with a try/catch block. The `message` property of the \Google\Exception object contains the error as a Json string. For example, here is the exception message for a request with no valid credentials :

```json

{
 "error": {
  "errors": [
   {
    "domain": "usageLimits",
    "reason": "dailyLimitExceededUnreg",
    "message": "Daily Limit for Unauthenticated Use Exceeded. Continued use requires signup.",
    "extendedHelp": "https://code.google.com/apis/console"
   }
  ],
  "code": 403,
  "message": "Daily Limit for Unauthenticated Use Exceeded. Continued use requires signup."
 }
}
```

You may extract the message and error code :

```php
try
{
   ...
}
catch( Google\Service\Exception $e )
{
    $json = json_decode($e->getMessage());
    if ( $json )
        echo "Error code $json->error->code with message $json->error->message";
}
```


