# CloudPrint API

## Requirements

To use our CloudPrint API, please refer first to the top README file, as it provides information about mandatory setup stuff (such as creating google developper credentials).

Our API requires the Google PHP API (mainly used to authenticate the requests), and it will be included automatically through Composer.



## API reference ##

It's strongly recommanded to have a basic knowledge of the CloudPrint API reference before going further : https://developers.google.com/cloud-print/docs/appInterfaces.

You may also test the raw API with https://www.google.com/cloudprint/simulate.html .



## Printers ##

Dealing with printers is done through the `printers` ressource and its methods :

```php
// if using our way of getting the service (through the \Nettools\GoogleAPI\Clients\GoogleClient interface) :
$service = $ginterface->getService('CloudPrint');

// or, if you prefer using a Google_Client object you have already created through the Google API library :
$service = new \Nettools\GoogleAPI\Services\CloudPrint_Service($gclient);

// then :
$list = $service->printers->search(); 
$service->printers->get($printerid);
```

The `$ginterface` var is a `\Nettools\GoogleAPI\Clients\GoogleClient` object you have to create with any required credentials. Please refer to samples or the top README.md file for more information.



### Getting a list of printers

The code is very similar to any other Calendar or Gmail call to list events or messages ; by using the `q` parameter, you may search only for printer whose name match some value.

```php
// listing printer with brand name 'Brother'
foreach ( $service->printers->search(array('q'=>'Brother')) as $printer )
   echo $printer->id;
```

The `$printer` variable is of `\Nettools\GoogleAPI\Services\CloudPrint\Printer` class, and holds all data associated to the printer. Please refer to our API reference (see below) to further information.



### Getting info about a printer refered by its id

If you have a printer ID, you can look for its CloudPrint details :

```php
$printer = $service->printers->get($printerid);
echo $printer->displayName;
```



## Jobs 

Managing jobs is done through the `jobs` ressource and its methods :

```php
$service = $ginterface->getService('CloudPrint');

// then :
$jobs = $service->jobs->search();
$job = $service->jobs->get($jobid);
$service->jobs->delete($jobid);
$job = $service->jobs->submit($printerid, 'Test job', file_get_contents('invoice.pdf'), 'application/pdf');
$job = $service->jobs->submitUrl($printerid, 'Test job', 'http://www.myweb.com/invoice.pdf');
```


### Searching for jobs

You may search for jobs whose title match a specific string (`q` parameter) or with a given status :

```php
foreach ( $service->jobs->search(array('status'=>'DONE')) as $job )
    echo "Job $job->title has been printed on $job->printerName";
```


### Looking for a job

To check for a job with its ID (for example to check for its updated status), you use the `get` method :

```php
$job = $service->jobs->get($jobid);
```


### Deleting a job

To delete a job from the job list :

```php
$service->jobs->delete($jobid);
```


### Submitting jobs

There are two ways of sending a print job to CloudPrint :

- you send the content to print along with the request (upload)
- you send a URI to a file to print along with the request ; CloudPrint will download this file to send it to the printer


```php
// sending content with the request (may take some time, depending on the file content and your host upload bandwidth)
$job = $service->jobs->submit($printerid, 'Test job', file_get_contents('invoice.pdf'), 'application/pdf');
echo "Job uploaded with id $job->id";

// or, you ask CloudPrint to download a file from your host :
$job = $service->jobs->submitUrl($printerid, 'Test job', 'http://www.myweb.com/invoice.pdf');
echo "Job submitted through URI with id $job->id";
```



## Our API Reference

Please refer to the phpdoc repository : http://net-tools.ovh/api-reference/net-tools/Nettools/GoogleAPI.html

In particular, have a look at the printers and jobs resources which hold all methods to create/read/update/delete items : http://net-tools.ovh/api-reference/net-tools/Nettools/GoogleAPI/Services/CloudPrint/Res.html .

