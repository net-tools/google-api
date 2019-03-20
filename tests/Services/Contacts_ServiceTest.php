<?php

namespace Nettools\GoogleAPI\Tests;



use \Nettools\GoogleAPI\Services\Contacts_Service;




class Contacts_ServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testReadOnlyProperty1()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);

		
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->contacts = null;
    }
	
	

	public function testReadOnlyProperty2()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);

		
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->contacts_photos = null;
    }
	
	

    public function testReadOnlyProperty3()
    {
		$this->expectException(\Nettools\GoogleAPI\Exceptions\Exception::class);

		
		// creating stub for google client
        $stub_client = $this->createMock(\Google_Client::class);
		$service = new Contacts_Service($stub_client);
				
        $service->groups = null;
    }
		

	
	public function testSendRequestRaw()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('plain text here');
		$stub_guzzle_response->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn(['text/plain']);

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'X-header-test'=>1234]
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->sendRequestRaw('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Misc\Payload::class, $resp);
		$this->assertEquals('plain text here', $resp->body);
		$this->assertEquals('text/plain', $resp->contentType);
	}       
		

	
	public function testSendRequest()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('<entry><item>123</item></entry>');

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'X-header-test'=>1234]
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->sendRequest('get', 'my.url.com', ['headers'=>['X-header-test'=>1234]]);
		$this->assertInstanceOf(\SimpleXMLElement::class, $resp);
		$this->assertEquals('123', (string)$resp->item);
	}       
	

	
	public function testSendRequestXmlException()
	{
		// creating stub for guzzle response ; response is KO (http 500)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(500);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<errors xmlns='http://schemas.google.com/g/2005'>
    <error>
        <internalReason>Error occured</internalReason>
    </error>
</errors>
XML
            );

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
        
        try
        {
            $resp = $service->sendRequest('get', 'my.url.com');
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
        }
        catch(\Exception $e)
        {
            $this->assertInstanceOf(\Google_Service_Exception::class, $e);
            $json = json_decode($e->getMessage());
            
            $this->assertNotNull($json);
            $this->assertEquals(500, $json->error->code);
            $this->assertEquals("Error occured", $json->error->message);
        }
	}       
	
	
	
	public function testSendRequestHtmlException()
	{
		// creating stub for guzzle response ; response is KO (HTTP code 403)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(403);
		$stub_guzzle_response->method('getBody')->willReturn(<<<HEREDOC
<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Error
401
(Client Error)!!1</title>
<style type="text/css">
        *{margin:0;padding:0}html,code{font:15px/22px arial,sans-serif}html{background:#fff;color:#222;padding:15px}body{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKsAAADVCAMAAAAfHvCaAAAAGFBMVEVYn%2BH%2F%2F%2F%2Bex%2B3U5vd7s%2Bfq8%2Fs0itq72PMLUPvtAAASvklEQVR4AbXBC0JqCQxEwT5Jd7L%2FHc8FdR4g%2BEGtEr8u%2FBHxu7otdzd%2FQPyqlmRp1Pw%2B8aukDfRa1fw28ZtWy4sa89vEb7LCi0zx28RvqgkvouW3id%2FU8pbtWmv5beJXRWNrRmp%2BnfhlHXZm%2BQPi95Vk%2FoD4fZbMHxC%2FryTzB8Tva435A%2BL3rcb8AfH7VjJ%2FQPy%2BHYk%2FIH5facwfEL8iaZcrnKyn%2BAPi57K2VL2WF1hJ%2FAHxQ2tJrg6HteXVjPkD4ge6V3J1%2BF97zhx%2BnXhWb8nacKXlnYPErxNPyfqw4ZYKVuUZdfhd4hmxunY73NICgfWMOvwm8ZQ1pMvlDZdaCic98kjV4beIp8ScpLvsSvhflzqQmqVLB281v0E8pc2bdNne8EayNTPNSbt02PBj4intcKltb%2FNibY%2BLf9aSO%2FyMeMo6XMva3g0vwrWsxvyMeEoc3knZ2g53ZaXa8DzxlHa4J23Jae5aycXTxFPa4WRdXAtdsivckZXG4TniKWtOSlre6y7LG651Wxq5OzxDPGUVIKNwX6ekCv%2B0ddglVPMM8ZQ10FJ4LGVvOEuXRl7OqnmGeEor4Ck%2BtnI1ZEvjDa%2FcPEM8ZQVY4RO9VqUlN%2F84PEM8JQ50cUgXH2mrKlyq5RniOQ4vVjPLHdu86OKGi2eIr%2BgNV6JwljmYO6zlbJsbWp4hPtVrjYpLLV7UHIp7rOVkixtaniE%2BU5I2Nc2FKJytZhTuiac5rLnh4hniEzUbDjXhn3g5W0nNA1aAKm7YPEN8bMecrZYLWl70hkcyBay5YfMM8aHI4aR7xAUVHyirOdhAmRsqniE%2BtOKsRjIXtDzmmRGHVmDFDRfPEB%2BJzMmO01xScdYnVRs6vPHMFG9W4ZrMM8RHouWw43DNhlDWiSVZY3nDoWYc3qzDNZlniPe6w4uoOFjcKhPXuJNWyG6VqjSuhm7%2BiZorUfEM8U5J8nKyMw0tcZLwPxdRtTlUcUgVdGlml0uZ4pqKZ4hr5VUnpSXdUgVa4hA5vHERV1Tp9XhdJTWHksYd%2Ftdarql4hrjQiaPiYLclNSeebVYz5o0W7Ghsa9blmlFtx01rxP8yy5XIPEP8L1W7bjWHlbzhRTwjzXrCK1f3qqSEyBysLVtayKp40yqurcITxJtUgavVHNob%2FinZTWt5VVvWVKvJSttQCkRjb%2FA4vLK5thOeIN6sm9ai5cTFhYRDy%2FyTGpdU0hxkaZvWUrZluTmLims14QniVbywClqgeouT9IZXNWoupGzNqHa3y5LGVYBnipbCSVxcq1meIN54oRXsbEk26S3NmBcZ807K3gon2ZLcxF5tPMVJprlWE54g3nihtbRHm7WjkbxTHSCWwj1r2U4HSMmdQEmWwonNtah4gnhjA9ZSaohmpnpDjWRptDwS25LcQGsc2Bla5sTFtZV4gnixpWmIVWpgRuVwsiV5q7kv0JJcNVIFapydUrHTQKa5IfMEcRKrurSQ0qhsmVR4kea%2B7pIr9NqSrRltWlaxomUgVVyLxBPEYeUGygtszew2KfOBclVpVN2ctCXNidZaaKWmONhc6rKaJwi6xuGkRmWpAkRa7outF9XN%2F7LlmbJmpiCyvBxk%2FtnSqHmGWGk5i2ZcaWBLau5KKHt3Ce%2FsaLMz46VG4cTFm%2FaMOzxFUYWztjzhkNI43JPyYvPAegPxzFRpOYmWF1WywrPUag5xjRapqqxxubijvYFVaC%2Fv7YSDpzxjzlbhpKXxhqcpWshqtECk0Yys6m5utZdD1LCuCifhfyVOapqsxhyiQMmSm58QNdZheZGV5FqwueXiZBUga28DvRte1NQCpQVSUkFqPbIr%2FIxg7arwJqqEg6e5Vuas1Zytyw1ka5uT9ajKI87WbksaLT8mbkXFyWqaa2rOVuFVStUNpGrDoSTPmDfWdlby8kPiHQtoa0vLpXU4WzX%2FS5W2gWxtOHQ24U3CSUmu8BPinR2XVSFyuNAOZ9Fyae1qDu2qcF8suRKeJt7pcW1zaE9xwcVZq7nWtpeTrQ0PrEeq8CTxnsWrlbThELra5ixqbsXWNoeq6nBft6TlOeK9VnG2lfb4TKOOlpOouKPsWg4pb3Nf1uMGusP3iDtKDaTcgMuWvL1FmZOouCtlbwJs1Yb7SuN2Nd8k7mgvXV4OKWALiGkVJ14eyPqQQG9Vc0dWGnn5LnFPTW1z1gW0OdSyag5aHsvaroVs1YZL2dKMt1nzXeKulas52QLanGy3xq4a87Eu2yHZ2uZNWzPjDbDmu8R9a8m7iQNscbKyy%2BWS%2BUzWtqp7qzpA1jPj8KKK7xIPZG2NVWTTSbpKbs5cfEF6y64qV6ctqcKbdvgm8VhSlnWwJbuaV3LzRb11onFt%2BKcVvkl8one7u3bD%2FzJuXnRt%2BFTXVHOWqubQ4rvEEyI1L1Z2h8%2B0eRHLKiBqvkk8IePmxZq1lk%2B0w0nJUHKIlm8ST8ioeVEFtFwbPhA3h8gcdpZV803iCRkVL7Y42bK2w0NlDqXlpJRV803iGZYrnFRxlqwO3eEuN4dSOGlVme8Sz7C37QZqeZPekl0b3nMBreKsp1bNN4lnWIEtF1Vc6i1bVZtwxQX0NC9UrfBN4hk7zaHLNrey1kgVLljATnO2rmj5JvEMqzlrF%2B%2BFXitcsAArnFkdLd8knrFqPmFzyQq0xUm0tJZvEs8oAR0eix0u1ARSqg70NNHyTeIZUqgZ85gdLlgcMjOSRlBqvkk8wwOSp3moJlyoCYfeKkmBVvgm8YyaUJJ5zOJSTXMWSgus%2BC7xjJpA%2BMiquVATXiUcSuGbxDNqmk%2BUxtW82WmurMI3iWd4wifaHo1rNxx2miul8E3iGTXhc4nH0lQ1O80VK3yTeEYNX5SspbEnXFmFbxLPqGm%2BrsvWFFdK4ZvEM2rCt6RmzCWL7xLP2Anfs2M3Fyy%2BSzyjpvmqDoed5YrFd4ln7DRftHI19BRXSuGbxDN6wtdEqjF4lisS3yWeEYUvWlkDNeZKTfgm8ZFu7mqFr%2FKMYae4lFH4JvGBVLgraghf09uQMZdabr5JfKC2q1zV3IgarOLLPMWllptvEo%2B1e7dkq5ZrLkip%2BKqa4lLk5ZvEY15INay9XIqXVGS%2BqsdcirzclYVa7hAPbQFVnJSaC9HCapavqjGXIjXvbNmSxi7eE4%2BsA21OumwuSQUJX1ZjLsVabqR6t7tUlrThhnjEC%2FFy6AKbCy45zdftmEutKm5UcSgHspY7XBEPVAFVHLoCUXPFkr3hi2wutba44QDr5iyeqQ3%2FiAccqOLQDhAV17pG0jZfUuZS5OJaGYiWF%2B2ypOV%2F4q5UQZtDu4G2xK10aeTlC1bhUslciQpYh7PSQtau8ErcVYZ4gXYDcUXLe1lrvBU%2B0VoutFRcWQWo4qwdTlYSr8Q9caDMwc3BDgl3xZpRb%2FORnuVCJHNlla2oOYmLQ8q7Ll6Ie6pgDaQKSCl8IF3WqAgPrbgU2VxpV1kje2EdoOWGlsOJuKMd1g14OdjNp1YjNY%2B0m0s15kYgJVlaFxBVOETuAOK9eEELrDmUli%2Fo8oy94S4Xl2LzQGukEFU46RptQLy3BWWgHSBTvEp32eGRtjTjSriQBKLlShUPrSRcnK2qtIB4Zw3tQNRAbF5FB0vhoS57JFXzZmUtuLiy5gNlTTixlkgB8Y4byhAX0HJ4Y%2FcmWkjz0NrSaMNJ5EiNi3%2FSpPlIayqA3UBcIG5tQTuwBcQOJx3AsrSzxHJ4bKs9U5xoqWnK4U17%2BUzPFLQ4iQ3iRtxQC3gBK5xZJjOutcaSpeYjsUZqKFmGOLxIaflU1jI2ZzuLuLGuLe2yBlrLC1tdWg7ZmWal8KHeGtXG0gLLSdZyha%2BoKYdDl7WIGxpbI7lSicyLqFkH2rVZF%2BwUnymNXNu8WUkVLqSaB6IpIGWXF3Ft1UC6rRq3mhc7TRXgLS2lrKb5VEoz6nCSrtE2V6p4aMeQ8tJaxLU4nGU9o%2BXVTrMF%2BLBgjYqvSNkjL%2BDxhmut5tDb3CF1uwJoEdday6vMTHjVs7GA3g3QU8tXxZJc6Q23yhxWckPCtZW1nLgQ12KFF5Ed3pQ0U7yKp%2Fi6YM%2FI4dZOA3FRRdvhSmaWMxtxI3JzVlP8k9qsVFWdbVvTfENCjcytUoBW46XscE3DizLi1o6KQ4%2FDlZRsWSfBCt%2BSdHGrzGHFOtjFtUgNNJQR78Qjr%2BVwzV4I65SazPJzrQbKq6bl5kapU7bbRryXLo3c3LATYIfMEs3yc1bA44bScqumvJ21jLgrhHdSktNWkONR%2BLmULMnbpQm3pOWkZxHf0R7NKKykDr9iq3ptuexOuJQRZ5lCfE96K5Ct5iNpe118WQKxVeGCxnYDmUL8iUjb2%2BXmexIu9Di9XtgpxJ9wcehuOzwt1gJx4ynEM9K9tS5X7fLempP2dmnDczwjTlLYi%2FiCnHXSe9LWic9k3qvlRTltLU%2Bp2lE1sKUG8bm2DiNpNBpJu5vwwuEdLa%2FWy6p4JL27Dg%2B0pUBsQHxu67C1Vb2dpLlU5h3bG87aS0vNXWtJtip0bbjDhqgB8TkvH1g115qttnfDoW0oNe%2B1Rs0hlqVRc8cSmYP4XBUfUXHNlQ5tqzkpNaXmHVV4lVpq1NxjhYP43JqP2FwracOh7OZQDuXmRmu5sjMO75SWE%2FE5F4%2F09s5wI5abQ0rFoVxZNTes7e7wvy053NpwJj7n4kVCDt29teWypJHFOy0VJ6sN0CrK4dpakmv5pxQeEZ8rQ%2B9alnU2knyo2k64Ix4vh5I5sVNarqW3u8z%2F4mkeEZ8LrCxXtbfWu9t8qqQK0DKHVtEubrWm%2BZ9VPCS%2BJN1828oB4gqwalrFtUjNP3bzkPg7sdXAyhyssF4upWb5Z8c8Jv5QWmpgVRxUsGoulMw%2FPQqPiZ%2Fp8JGVOLQWKAW6%2BCcyF2qGD4gfibe2ead5lXEDpQAu0rv8r2WgtZxl1Twm%2Ftls1HxHK7HDjZV51VIgWmBlSeMKr%2BxseZYXq%2BUx8aY0MxrvVnUC4XNxgYtrJY15taMmNlAztd0lhxfW6MChC1rFY%2BLVjlwzKutVdfhE7xjKXEiX3CuHFzWG0lLycogUXnTtxuaws6DiMfFK09kZQ9K1VSvJ3oRHslIFWuGftdzQUoWzlYONinBILRdaC8TTYPO%2F3nBFnKxLG2um%2BKfXOrg6vBdLrvJSCm9SJpy0RtucrMRq1Zy1woUy0B4HbN60ex0uiEN0KLk1xZXs2paKW9FIqrJrzP%2Fs5k17tJz0GE%2FxohwulGElOUTmTRWl5oI4lKRRsTPhVpIdc6sl10IsFW9WXNpROPH0TkGAVnFpx5a63WSKN5HVXBKwc1btEffsNO8kvBObS5lZTnaUMXFYqbnUltwg75h%2FusMVATXleWW7qk1Xb8KLVfiKlsIlj9Sc1FhFtjITboSTlSp8QMCO5JU11bb1ZlQdIHL4iprmktWROclqGlaWmvsSPiRAIy3lcAhk05vsWgfbU3xFVFyRWTUvSqqa2S7zHEFmRikt7yS18kxxFj6yY67UbNu86U6qIApPEUSasZb7Ek0DqXh5LHa4lDFWc6kd4uUpgsiaKR6pKQ61uHmsZrmyk1ZxpQ1oeYoAzaG4ry1zsuXisVJxpeQdc60N2DxFgGckc1ePixdpHkjVjrnS0kpc6u5SwMtTBKxkybUJN3bUfCaulsMVTVvNP%2BmyNQVe7tjlE%2BJFb1mSLVfV9jaHHS2fiao15sqOd4pL29ArbxXvldV8TPwv6XVV6YXtGTefiiqaMRei2TFXKpzUONxKFWo%2BJt5J0ltlzQxfsCqimSpv86KmrHApBbXA2s2NuKPwMfFQvOELWgvsnEjVQMYtc2UXqjm0xI0yq%2FAx8T0JtyJz8DiekWpjxWoupRqqOamp5VJPsXJt9256wz3iW8oOt1xNaWah3NZJZK7UAg6HLo%2B5tFPgke2SreUe8R1rO9xayTpALFaa2Z3mUhyo4qQ6I67MbLlsyyfFPeI71m7ey0orw2pL256WuFILVHOI41mu1IyK3u0q28094nvCXQHLtqyF9Gq5tA7E4bAViRsrNW%2FCXeK3lDTVVoBI4ZIDVHFYpbTcyIbPiF%2FTSbPT3SUtl6qAuDl4W8UzxC%2Fz6CRciALUcijT4inil%2FV2p4pLtUDcwCol8xTxF8KlKg5VQGtb4jniz7UbWAcox%2BJJ4s%2B5OLiAVnuKJ4m%2FtuawBURbszxL%2FLF4OXgh9s7yNPHHqjisgVLLPE%2F8rXYD7UCrVsXzxN%2Bq4uAGrFj8gPhTXRzKwGprmh8Qf2rlot2AvSp%2BQvyl1nikAlprh58Qf0lqolGBarX8iPhLZWBVqnVsfkb8pTaHcru61PyM%2BEtrDq2UW8sPib%2FUChBvbIcfEn%2FKxWGrpeWnxJ9qVYDyVPgp8bfa2qRmmh8Tf21lq5qfE38uveE3%2FAdr385%2FSVd%2FMAAAAABJRU5ErkJggg%3D%3D) 100% 5px no-repeat;margin:7% auto 0;max-width:390px;min-height:180px;padding:30px 0 15px}* > body{padding-right:205px}p{margin:22px 0 0;overflow:hidden}ins,#g{text-decoration:none}ins{color:#777}a img{border:0}#g{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAA3CAMAAADZn0ObAAABgFBMVEX%2F%2F%2F%2Fy1pHuLjfMm2W4yfA1TrPukgZDats3V8fwTEypjWy8u7oPsSWVsOoBdwwClhI%2FZdlasGjpOUOVlJjx0c20CyZpea36pAdIdebyu7COlqpYaql2hLH40Gn59vayuMlZiOunpqjYGy9wiNLd5PRqlOzzcmz%2Fwi4FpRrqsDisbm7O2O%2FKycrGfHmdo7CFmdPs7Ozz8%2FPIDSvcFC%2BTR0vCw8Xj5OTc3Nynvu7l6%2FfVjyw7Xc%2FSMT%2FZbgbT09NDbuHKz9BKf1Hr5ubiHTLt8vz%2Btxj1%2BOrT1txYedhLcNfwjYjTES6yq6r27Oqnq7NJZsiykpDNEiyxUVOTptiys7LNs6%2FhegO3p5L46MRveZiXcHBFXbY5dkLUysj0Pj9OfOjRS1JVZZzMrZGoXWCzKjn%2BrgpcdsflfIKh26frpKHJGzIqxDe4cC2AoOnOXl%2FYaWx204D4XVWsPEjRXwXqoRqYFSnN5dBhfMwZjipRaLvUmpk3x0SualZBXcJIV5A9nk5h34NHAAAJe0lEQVRo3u1YC1fa2BYGBBJA2lSeRjzyUKmQQgJNAoYhlJciiKJYHx2hYm2rre2140zHmem9f%2F3uE16JOpVZpcuuu%2B5eLpeSsPOdb3%2Fn2%2FtEp%2Ft%2FdAOV3%2F4L4tGj39DI3ynOp%2BznEPaUcZ77LqjKb%2F989%2Be7d5OPreKIDygaz9teWSYIgpJlb9u%2FnUqj8cP6jX87OTn58Pd6dqTkabvfS6z66FgsEKN9rymi4l31x6NjB1YWeODqwTNbfpTURgDlpgNNPrciilmGj7kpQEZQIaE4bmDcJIbFsyPUD6iq%2BGK2rMAhWARCnJCNbVAUJZlCwrgJ44Cth8%2F4u6U1f%2B4nrhwBRs0rYvkr0mQ6CqyMvY5KEe%2BGVTz3y18czetbgxM3TEcO29jZQiD5EYqIoILSfl3UPB7%2FEzW5AdX4nWtyJLZSfi81E8jdvM04Z%2BOF7%2BBdI0l%2BfsFPbDhuuwsJYn78zgVsvRuBLVzCmYB42%2FPRjQ%2B%2FFSUatYhpv18m923sSDmd02uLb9ZOnTdSoj19GMesfq94DXoxajSmcNjt9nP7vALroaaI65B1cXFtWl2a1IKX%2BuIYpROg6YtWy2IxtOD3mra6e7Ph8FJnYqlUAmR76ksoeu5vt%2F0QbS9BUXGoyjW21tcsEDiz5XS4XOiD1FXgbmUj52Lrw2ataatZpyAs00P3L8%2BGS52mzcbYdjGwkp4bAIv65VA8vkrIXmgibndgDlyoxxbXS3thmKjVms0nsNypjyzq17AN0mreXUOg6knNlhUFYWXnpWFqqvAH1xfgbHhpN8CLLMsKzNJJqXTSYfvW027XAe8cTcEA8Dlg40Xc%2FcHkB7CmLwyb8F3I2pkyGApP2P5yvLLkvltagOqDg1esFbGHUwVDIbnDDriy1rPdf7js5UnpxNNBvd0k082sAHDj0Mgo3wrbk%2FwAlvOiFQkcYkUgdsKQDJ71koJpEZL7rt0KFbQYHAC%2B%2Bzjuj0KhkMxklW%2FthcPLjqHrdU5OTjyXilah%2FbsDue5KQhRBSXPdZw7ZWsdpe%2BJHzmDD5Yp0LcHexmzd5W1o7cKwpTIR9iXAqkbymKxw%2BNLazA%2BHzyWAZe4Ar8UFv%2BzuXUE5SqYkd7bL1gAW1ODlIO1p8sz160GuD4ugru6C5bywGI5VlUanAKtxxkBqPYbFDNlGsxjWMnxi9Lep%2FX4dUFumKLKbogsL%2FkSLF4bjZrey05ag6%2BmBw9Zjy%2BuVqas7tAVktYIHTHn4SR6zldgUMFklszWn8oQ9DMvcyWOfptxM%2F4odpiQyhnc8etyHBcpq%2FYWfjU5bhUwk0MSTVVdbMCZLG%2FWvw1oHsgCWeuyxFJIJ1%2FEKAmWVLq3q4QctAaznVlG30Maw%2BjwaoYgmWtBoaxrybvHrzkVD4cNms8mIfX%2FQGTFbEv1133KCiQYjakp0bzCspwyCGp6YrZpTzCyGtZxDAIvYGMgjKlMkGRCHbAGsNUvLsLVrmZqaAKIYYWh3unlZhqK7v%2B7yzlbLEPxLM2NMK7B4Ftzh0mxVf72sx7DeM9w5WChVFwYPosgjxbaHkl%2BDvJkafzhnUxHVlyKwtTF3rYqoiFuY3b5tj8ejnLNlMAS3NC7iTCYbrqc2Vh8uAVu8SnY6DOuX9zkuRcjYq1CfLcnUbb0qtiBvdXdFEPLsdVq2KVmpItIegwh8wvD7VwnpNZN3GqYMyVeafeEsJF2YLQXWshoW2jsxm3%2BCXZCGnkMR%2FQWnMFnXDOLUAMuN3DqVRzEsqsJwmhOjfTtG03SlQpAmmmed0BaCZ7uCBlbV5TrguT3oNR5zTY14D2r4C9QVzcHSiNcrSvNkocU5ugsbsqXkfZm7zcu5z7gvSNoDDhKyh7ksT0gSaXKs4H1XKFQj6pnMmay6foXBQ3FPj1V9Se8x%2F%2FQei3t9W5IkahUAoKifUgZwFVsAcR23weDmrTaQkzAuStutOY5D3DxBwdmnBiSBexaCGX5dJXmseGxDeo%2Fn0rOsJnvWA2QpxLD1K1KiZP%2F5gp%2BC415v5ejxwx5baDEDiTPMbVUshiQMrHKTyzQBZJnw7lnPBIPBs01VFd9gsvCK2UsPdk%2FVpRLsQ4fSLxGbi%2FncvpAfj%2BW2QYOa7MMCKeD1frjtcIXmK11cN6SXhu7aZQvtVBvVaoYf3IEymCxcOqQ3m82eS35gOnuYrL7QubzI8CFvBY90%2FRvQ48F0yj2B5RYKH2%2FFlfsCVMPGuz5HpCu4iIrzlJ80Go3qh4GEFmEbOhjl2cXOcwC21L9ULsE2rA21hrhiCDbVl9iQz2GrRvlMo5pMBl86UX8Czg%2BFZCNJDEze1tpHmiB7RYRaRRKJBOBSJnUEJXw6OCyxHTMAW4LMCI85gCqmFcS5TBDgFP0hH7T14MHPTcVT0OGrRqKRTBY%2BnjoF5%2FSbzI4KAj7XY2BESGOraShhjy1Y1%2BYrVyKRWXM6naeZ6quDAN8f58vpnWXAdbKk1%2BvDnucTjmZWS3u0QmAb3O7l%2Fs8DJX5%2F9hZr4HDL5Uo0EkkgrZo429G8cOCyPpMJkFHE6rZxXllWMWqvAFemf%2FfmfMTyB5%2FOgLFktfoJmj0zfPmF2OyudRkUZjYvd3abvPZwl45GV7F4ZSqkMCE8evazEo%2B68hI3P7m68eo4YBM5rVHZ3BiYRMKyVldDqxUJCni0f%2BQIMOzgncRO5HhraysSqfGMoCEE5W2d5YnliYldGNdVZUinFioUQW68UGpBdM2RzfI2HoLpQkACv3n8FCLiqDE3tM%2BKPO1%2B8UIhjXxxdeWj6Rr09UNB9RROELM5hsmKLHf962VWWFkRRSFfVo0B522KCtGxer1Ov8DileJKq%2BZYTvnp38mxYpaBEIXbjvasyMRot3vmCE5ONA2TBhwOytcP1kiJrxyZ1e82CMrnCEAeURB5NwmWT%2F7NvIkQ%2BPffnicQmxdEMZuFVcNxBX3byR5QSfv4nMbhLYpEH%2BAifeI3vCYYx%2FuQ6IIXUDGDSqE8dBPTDMP9YzTjjDToinSoa4aEDRDtSC87vl8Y2xXCRGtKhuLA1j3DssPJRYppHcxOmvbvGxb4OqllSxeH4Y1B9woriseSDV4Nolgh92Pi%2FcLSKZPpa%2FWwFCdnHDx3v6h07GdoOFJlLq1TbEs3HyJn6PG%2FTf%2FH9ie6cY%2BViJA9lUqdQ7f2Be4fFfap2IzSYLG7k1c%2BGDp%2BAFTKJF%2FzufdnZtz7PrrevDZ03GPAaJDjbRA8dGsW6X6cgNmAhSEG%2FUiY%2Fsfiv02O7iVu1LunAAAAAElFTkSuQmCC);display:block;height:55px;margin:0 0 -7px;width:150px}* > #g{margin-left:-2px}#g img{visibility:hidden}* html #g img{visibility:visible}*+html #g img{visibility:visible}
      </style></head>
<body><a href="//www.google.com/" id="g"><img src="//www.google.com/images/logo_sm_2.gif" alt="Google" width="150" height="55"></a>
<p><b>401.</b>
<ins>That&#39;s an error.</ins></p>
<p>There was an error in your request.
<ins>That&#39;s all we know.</ins></p></body></html>
HEREDOC
            );

		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('my.url.com'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
        
        try
        {
            $resp = $service->sendRequest('get', 'my.url.com');
            $this->assertTrue(false, "Exception not thrown, that's unexpected");
        }
        catch(\Exception $e)
        {
            $this->assertInstanceOf(\Google_Service_Exception::class, $e);
            $json = json_decode($e->getMessage());
            
            $this->assertNotNull($json);
            $this->assertEquals(401, $json->error->code);
            $this->assertEquals("There was an error in your request.
That&#39;s all we know.", $json->error->message);
        }
	}       
	


	public function testGroupsGetList()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<feed xmlns='http://www.w3.org/2005/Atom'
    xmlns:openSearch='http://a9.com/-/spec/opensearch/1.1/'
    xmlns:gContact='http://schemas.google.com/contact/2008'
    xmlns:batch='http://schemas.google.com/gdata/batch'
    xmlns:gd='http://schemas.google.com/g/2005'
    gd:etag='feedEtag'>
  <id>jo@gmail.com</id>
  <updated>2008-12-10T10:44:43.955Z</updated>
  <category scheme='http://schemas.google.com/g/2005#kind'
    term='http://schemas.google.com/contact/2008#group'/>
  <title>Jo March's Contact Groups</title>
  <link rel='alternate' type='text/html'
    href='http://www.google.com/'/>
  <link rel='http://schemas.google.com/g/2005#feed'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full'/>
  <link rel='http://schemas.google.com/g/2005#post'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full'/>
  <link rel='http://schemas.google.com/g/2005#batch'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full/batch'/>
  <link rel='self'
    type='application/atom+xml'
    href='https://www.google.com/m8/feeds/groups/userEmail/full?max-results=25'/>
  <author>
    <name>Jo March</name>
    <email>jo@gmail.com</email>
  </author>
  <generator version='1.0'
    uri='http://www.google.com/m8/feeds'>Contacts</generator>
  <openSearch:totalResults>5</openSearch:totalResults>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <entry>
    <id>http://www.google.com/m8/feeds/groups/userEmail/base/6</id>
    <updated>1970-01-01T00:00:00.000Z</updated>
    <category scheme='http://schemas.google.com/g/2005#kind'
      term='http://schemas.google.com/contact/2008#group'/>
    <title>System Group: My Contacts</title>
    <content>System Group: My Contacts</content>
    <link rel='self' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/6'/>
    <gContact:systemGroup id='Contacts'/>
  </entry>
  <entry gd:etag='Etag'>
    <id>http://www.google.com/m8/feeds/groups/userEmail/base/groupId</id>
    <updated>2008-12-10T04:44:37.324Z</updated>
    <category scheme='http://schemas.google.com/g/2005#kind'
      term='http://schemas.google.com/contact/2008#group'/>
    <title>joggers</title>
    <content>joggers</content>
    <link rel='self' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/groupId'/>
    <link rel='edit' type='application/atom+xml'
      href='https://www.google.com/m8/feeds/groups/userEmail/full/groupId'/>
  </entry>
</feed>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/me%40gmail.com/full'), 
						$this->equalTo(
								array(
									'query'=> ['q'=>'john'],
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->getList('me@gmail.com', ['q'=>'john']);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\ListGroups::class, $resp);
		
		// checking 2 groups
		$it = $resp->getIterator();
        $it->rewind();
		$group1 = $it->current();
		$it->next();
		$group2 = $it->current();
		
		$this->assertEquals('System Group: My Contacts', $group1->title);
		$this->assertEquals('joggers', $group2->title);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
	}
	

	
	public function testGroupsGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my group</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->get('http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('my group', $resp->title);
		$this->assertEquals('"my etag"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
		return $resp;
	}       
	

	
	public function testGroupsCreate()
	{
		// creating a group
		$group = new \Nettools\GoogleAPI\Services\Contacts\Group();
		$group->title = "group title";
				
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>group title</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/default/full'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml'],
									'body'=>$group->asXml()
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->create($group);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('group title', $resp->title);
		$this->assertEquals('"my etag"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
	}       
		

	
	/**
	 * @depends testGroupsGet
	 */
	public function testGroupsUpdate($group)
	{
		// modifying group
		$group->title = "updated title";
		
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<entry gd:etag='"my etag2"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>updated title</title>
    <id>http://www.google.com/m8/feeds/groups/me%40gmail.com/base/groupId</id>
    <updated>2017-05-01</updated>
    <content>notes</content>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/groups/userEmail/full/groupId"/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/userEmail/full/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml', 'If-Match'=>'"my etag"'],
									'body'=>$group->asXml()
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->update($group);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Group::class, $resp);
		
		$this->assertEquals('updated title', $resp->title);
		$this->assertEquals('"my etag2"', $resp->etag);
		
		
		// other properties from XML have already been tested thouroughly in ListGroupsTest
		return $resp;
	}       
	

	
	/**
	 * @depends testGroupsUpdate
	 */
	public function testGroupsDelete($group)
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/groups/userEmail/full/groupId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag2"']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->groups->delete($group->linkRel('edit')->href, $group->etag);
		$this->assertEquals(true, $resp);
		
	}       
	

	
	public function testPhotoGet()
	{
		$img = 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAACnVBMVEUAAAC3NyeCSxQAAAAAAADSZiMAAAB4v+7MWCcEAAC+jTx+xPHOcChVpt6iJSNsuuxsuuzXXydsuuzGTieLIyB4GhpuHxxxvOxqGRdRExK4OicLAgItCgrxxx3edijghieJzfV8yPbc1L2bgUWTPy+wMCd8t+LbZifGmC/w311teZvJTyiXMCfGYjPOVCjG19lWl9OEHB3CSijntyvmmSe1NieBIBy0QCW+TCifPS3HkWWZIyJoreGezu3cayfZrUmqKya/RCj75BK9m4NLDxHUaijggCj86Q7gfyqzYCnDVSq9hiw2DgsuDAuzQSnfcCeTMh6IwOlgFxbVWyfXYifISifRUSfBQSjSVSctVp7YbCjYZifUVyfMTCcdG027OyevLSZYsOhQrOMuf8QydLo0arP875MoRIYlOXgqndolj88xX6glRY0iM28fJlwqHEXIaS6VOC7glSjdgijadCjbbijGRijJVidetOlCqOJJqOE5pN4kmtk2jc1DickzYq1XcqIqS5AlQIOvroAzQXb56W777GYiLWMfIVU2Mk+8qkkbFkVdNz+kRzOORjHHXi+xUi702y3RbCvbeingnyjfjijjgCi9RijjmibmqiTvxR1Dodq8yNWQss5Nks6Mpc0qhskwk8dCnMVfkMX788NchsA0iryxv7pirLnd3LVKj6/z661SeKw7Y6dGaqZMeaJ8hqAoUZ3r4pvfx5paf5ppj5EsTZCtk4yBqYr/8XxcZHd7cXY7SXJLUm2PdGV3dV1LSFvRf1lqTln87VT861CNXkj01kP540GfYEAkG0Ctdz3NdD19bTxTHjy/lTtsODq3eTjNiDbMdjVpWzVwJzWUVjH00DCqVS+rhC3UjizVgirehynpvCLqsiL12RyCIu6zAAAAU3RSTlMA/gQcERAIRy8m/TL++vbz69nRxMKrqJmXc09CPDErHRcJ/ff08/Ls6ufm5ubl5dzb0c7KxcG5uLe2sqyqoZqTjo2LioiGfGVWVFJRTEw/PTQpHrmhBvoAAAIFSURBVCjPYoADBW52dm4FBjSgyC4uJMDPLyAkzq6IEGVi4LbhrUpKrKhITOJVsw2Eict5sBvU1S5akZSYmFQ1e95cfWdWiISrrlbD+q1dO6orK6sza+sWL1eykgcbZBFacOnilK6FNZmZNVnJKUvnrN5szwSUYBUOKS0pnsG1ZdWyrKwFySmp85ekacoCJfzVo6NKiot4+k/vTgYKp2avW5l22B0oYRcSElUyeeqtg9vXpKRmR0ZGbmve2ebEwCAvGBwSHTX59ox9a+vrsyMb09MbmlvTjDkY5AQjgkOiogpO7doYCRRuysjdsKktjcuTgUMvLDgkZHpvS3pOTg5QuD1v/5HjrcregDFwaMdGBJeH9h/KyGjZuwco3nHm2MSJGrJA15aFxUQER086cfTCTa4DeXGd567dm2UC9LxjeGhYTDDQBSHlhT2dHXGX7866M8UN6FwpRrBMeeGVs/lxcXEnb8ycWcTjBfK5KWN4LFBmwvn87u78nqtFU4sLVPwYQFpUQXoigoMn9PVdnzRtWlR0ryUrOBQdEhjDy0CaQkKiS0tDQiKMOCDhHmSdwBgfHhobFgF0RHBEqHkANAJZZET5QFJloaGxsdMLRWRYmCAybMzSLoZ8CUC5+HhGHTFfZjZY5LKxcEpLSoiKmIlJSPpwskDEocaxsTAzc3IyM7OwQY0BANeqq70ox7BgAAAAAElFTkSuQmCC';
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn($img);
		$stub_guzzle_response->method('getHeader')->willReturn(['image/png']);	
		$stub_guzzle_response->expects($this->once())->method('getHeader')->with('Content-Type');		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts_photos->get('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Photo::class, $resp);
		
		$this->assertEquals($img, $resp->body);
		$this->assertEquals('image/png', $resp->contentType);
		

		return $resp;
	}       
	

	
	public function testPhotoUpdate()
	{
		$img = 'xxxxxxx';
		
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'body' => $img,
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"etag-photo"', 'Content-Type'=>'image/jpeg']
								)
							)
					)
					->willReturn($stub_guzzle_response);
				
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$photo = \Nettools\GoogleAPI\Services\Contacts\Photo::fromData('image/jpeg', $img);
		$resp = $service->contacts_photos->update($photo, 'https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId', '"etag-photo"');
		$this->assertEquals(true, $resp);
	}       
	

	
	public function testPhotoDelete()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('');

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'*']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts_photos->delete('https://www.google.com/m8/feeds/photos/media/me%40gmail.com/contactId');	// using generic etag *
		$this->assertEquals(true, $resp);
	}       
	

	
	public function testContactsGetList()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:openSearch="http://a9.com/-/spec/opensearch/1.1/"
    xmlns:gContact="http://schemas.google.com/contact/2008"
    xmlns:batch="http://schemas.google.com/gdata/batch"
    xmlns:gd="http://schemas.google.com/g/2005"
    gd:etag="feedEtag">
  <id>userEmail</id>
  <updated>2008-12-10T10:04:15.446Z</updated>
  <category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact"/>
  <link rel="http://schemas.google.com/g/2005#feed" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full"/>
  <link rel="http://schemas.google.com/g/2005#post" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full"/>
  <link rel="http://schemas.google.com/g/2005#batch" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full/batch"/>
  <link rel="self" type="application/atom+xml"
      href="https://www.google.com/m8/feeds/contacts/userEmail/full?max-results=25"/>
  <author>
    <name>User</name>
    <email>userEmail</email>
  </author>
  <generator version="1.0" uri="http://www.google.com/m8/feeds">
    Contacts
  </generator>
  <openSearch:totalResults>1</openSearch:totalResults>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <entry gd:etag="contactEtag">
    <id>http://www.google.com/m8/feeds/contacts/userEmail/base/contactId</id>
    <updated>2008-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2008-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>Fitzwilliam Darcy</title>
    <gd:name>
      <gd:fullName>Fitzwilliam Darcy</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">456</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="hamster"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/userEmail/base/groupId"/>
  </entry>
  <entry gd:etag="contactEtag2">
    <id>http://www.google.com/m8/feeds/contacts/userEmail/base/contactId2</id>
    <updated>2009-12-10T04:45:03.331Z</updated>
    <app:edited xmlns:app="http://www.w3.org/2007/app">2009-12-10T04:45:03.331Z</app:edited>
    <category scheme="http://schemas.google.com/g/2005#kind"
        term="http://schemas.google.com/contact/2008#contact"/>
    <title>John Darcy</title>
    <gd:name>
      <gd:fullName>John Darcy</gd:fullName>
    </gd:name>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId2"
        gd:etag="photoEtag2"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId2"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId2"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#home"
        primary="true">123</gd:phoneNumber>
    <gd:extendedProperty name="pet" value="dog"/>
    <gContact:groupMembershipInfo deleted="false"
        href="http://www.google.com/m8/feeds/groups/userEmail/base/groupId2"/>
  </entry>
</feed>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/me%40gmail.com/full'), 
						$this->equalTo(
								array(
									'query'=> ['q'=>'john', 'max-results' => '10000'],
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->getList('me@gmail.com', ['q'=>'john']);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\ListContacts::class, $resp);
		
		// checking 2 contacts
		$it = $resp->getIterator();
        $it->rewind();
		$contact1 = $it->current();
		$it->next();
		$contact2 = $it->current();

		$this->assertEquals('Fitzwilliam Darcy', $contact1->title);
		$this->assertEquals('John Darcy', $contact2->title);
		
		
		// other properties from XML have already been tested thouroughly in ListContactsTest
	}
	

	
	public function testContactsGet()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my contact</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content>notes</content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="liz@gmail.com" displayName="E. Bennet"/>
    <gd:email label="sweet home"
        address="liz@example.org"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#work" primary="true">(206)555-1212</gd:phoneNumber>
    <gd:phoneNumber label="sweet home">(206)555-1213</gd:phoneNumber>
    <gd:im address="liz@gmail.com"
        protocol="http://schemas.google.com/g/2005#GOOGLE_TALK"
        primary="true"
        rel="http://schemas.google.com/g/2005#home"/>
    <gd:im address="liz.work@gmail.com"
        protocol="http://schemas.google.com/g/2005#SKYPE"
        rel="http://schemas.google.com/g/2005#work"/>
    <gd:structuredPostalAddress
          rel="http://schemas.google.com/g/2005#work"
          primary="true">
        <gd:city>Mountain View</gd:city>
        <gd:street>1600 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>1600 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:structuredPostalAddress
          label="sweet home">
        <gd:city>Mountain View</gd:city>
        <gd:street>100 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>100 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:extendedProperty name="pet" value="hamster" />
    <gContact:event rel="anniversary">
        <gd:when startTime="2000-01-01" />
    </gContact:event>
    <gContact:relation rel="assistant">Suzy</gContact:relation>
    <gContact:relation rel="manager">Boss</gContact:relation>
    <gContact:website href="http://blog.user.com" primary="true" rel="blog" />
    <gContact:website href="http://me.homepage.com" label="Testing site" />
    <gContact:userDefinedField key="key1" value="value1" />
    <gContact:userDefinedField key="key2" value="value2" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
</entry>
XML
			);
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->get('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId');
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		
		$this->assertEquals('my contact', $resp->title);
		
		
		// other properties from XML have already been tested thouroughly in ContactTest
		return $resp;		
	}
	

	
	/**
	 * @depends testContactsGet
	 */	
	public function testContactsUpdate($contact)
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>my contact renamed</title>
    <id>my id</id>
    <updated>2017-05-01</updated>
    <content>notes</content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="liz@gmail.com" displayName="E. Bennet"/>
    <gd:email label="sweet home"
        address="liz@example.org"/>
    <gd:phoneNumber rel="http://schemas.google.com/g/2005#work" primary="true">(206)555-1212</gd:phoneNumber>
    <gd:phoneNumber label="sweet home">(206)555-1213</gd:phoneNumber>
    <gd:im address="liz@gmail.com"
        protocol="http://schemas.google.com/g/2005#GOOGLE_TALK"
        primary="true"
        rel="http://schemas.google.com/g/2005#home"/>
    <gd:im address="liz.work@gmail.com"
        protocol="http://schemas.google.com/g/2005#SKYPE"
        rel="http://schemas.google.com/g/2005#work"/>
    <gd:structuredPostalAddress
          rel="http://schemas.google.com/g/2005#work"
          primary="true">
        <gd:city>Mountain View</gd:city>
        <gd:street>1600 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>1600 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:structuredPostalAddress
          label="sweet home">
        <gd:city>Mountain View</gd:city>
        <gd:street>100 Amphitheatre Pkwy</gd:street>
        <gd:region>CA</gd:region>
        <gd:postcode>94043</gd:postcode>
        <gd:country>United States</gd:country>
        <gd:formattedAddress>100 Amphitheatre Pkwy Mountain View</gd:formattedAddress>
    </gd:structuredPostalAddress>
    <gd:extendedProperty name="pet" value="hamster" />
    <gContact:event rel="anniversary">
        <gd:when startTime="2000-01-01" />
    </gContact:event>
    <gContact:userDefinedField key="key1" value="value1" />
    <gContact:userDefinedField key="key2" value="value2" />
    <gContact:groupMembershipInfo deleted='false'
        href='http://www.google.com/m8/feeds/groups/userEmail/base/groupId'/>
</entry>
XML
			);
		
		
		// modifying contact
		$contact->title = 'my contact renamed';
		$contact->relations = [];
		$contact->websites = [];
		

		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('put'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'body' => $contact->asXml(),
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag"', 'Content-Type'=>'application/atom+xml']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->update($contact);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		
		$this->assertEquals('my contact renamed', $resp->title);
		
		
		// other properties from XML have already been tested thouroughly in ContactTest
		return $resp;		
	}
	

	
	public function testContactsCreate()
	{
		// creating a contact
		$contact = new \Nettools\GoogleAPI\Services\Contacts\Contact();
		$contact->title = 'john';
		$contact->emails[] = (object)['address'=>'john@mail.com', 'rel'=>'http://schemas.google.com/g/2005#work', 'primary'=>'true'];
		
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn(<<<XML
<entry gd:etag='"my etag"' xmlns:gd='http://schemas.google.com/g/2005' xmlns:gContact='http://schemas.google.com/contact/2008'>
    <title>john</title>
    <id>my id</id>
    <updated>2017-04-01</updated>
    <content></content>
    <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*"
        href="https://www.google.com/m8/feeds/photos/media/userEmail/contactId"
        gd:etag="photoEtag"/>
    <link rel="self" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <link rel="edit" type="application/atom+xml"
        href="https://www.google.com/m8/feeds/contacts/userEmail/full/contactId"/>
    <gd:email rel="http://schemas.google.com/g/2005#work"
        primary="true"
        address="john@mail.com" />

</entry>
XML
			);
		
		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('post'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/default/full'), 
						$this->equalTo(
								array(
									'body' => $contact->asXml(),
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'Content-Type'=>'application/atom+xml']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->create($contact);
		$this->assertInstanceOf(\Nettools\GoogleAPI\Services\Contacts\Contact::class, $resp);
		$this->assertEquals('john', $resp->title);
		$this->assertCount(1, $resp->emails);
		$this->assertEquals('my id', $resp->id);
		$this->assertCount(3, $resp->links);
	}
	

	
	public function testContactsDelete()
	{
		// creating stub for guzzle response ; response is OK (http 200)
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		
		
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particlar, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('delete'), 
						$this->equalTo('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId'), 
						$this->equalTo(
								array(
									'connect_timeout' => 10.0,
									'timeout' => 30,
									'headers' => ['GData-Version'=>'3.0', 'If-Match'=>'"my etag to delete"']
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		
		
		// creating stub for google client ; method authorize will return the guzzle client stub
        $stub_client = $this->createMock(\Google_Client::class);
		$stub_client->method('authorize')->willReturn($stub_guzzle);
		
		
		// sending request
		$service = new Contacts_Service($stub_client);
		$resp = $service->contacts->delete('https://www.google.com/m8/feeds/contacts/userEmail/full/contactId', '"my etag to delete"');
		$this->assertEquals(true, $resp);
	}
		
	
}

?>