<?php

namespace Nettools\GoogleAPI\Tests;





use \Nettools\GoogleAPI\Services\Contacts\Photo;



class PhotoTest extends \PHPUnit\Framework\TestCase
{

	public function testPhoto()
	{
		$img = 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAACnVBMVEUAAAC3NyeCSxQAAAAAAADSZiMAAAB4v+7MWCcEAAC+jTx+xPHOcChVpt6iJSNsuuxsuuzXXydsuuzGTieLIyB4GhpuHxxxvOxqGRdRExK4OicLAgItCgrxxx3edijghieJzfV8yPbc1L2bgUWTPy+wMCd8t+LbZifGmC/w311teZvJTyiXMCfGYjPOVCjG19lWl9OEHB3CSijntyvmmSe1NieBIBy0QCW+TCifPS3HkWWZIyJoreGezu3cayfZrUmqKya/RCj75BK9m4NLDxHUaijggCj86Q7gfyqzYCnDVSq9hiw2DgsuDAuzQSnfcCeTMh6IwOlgFxbVWyfXYifISifRUSfBQSjSVSctVp7YbCjYZifUVyfMTCcdG027OyevLSZYsOhQrOMuf8QydLo0arP875MoRIYlOXgqndolj88xX6glRY0iM28fJlwqHEXIaS6VOC7glSjdgijadCjbbijGRijJVidetOlCqOJJqOE5pN4kmtk2jc1DickzYq1XcqIqS5AlQIOvroAzQXb56W777GYiLWMfIVU2Mk+8qkkbFkVdNz+kRzOORjHHXi+xUi702y3RbCvbeingnyjfjijjgCi9RijjmibmqiTvxR1Dodq8yNWQss5Nks6Mpc0qhskwk8dCnMVfkMX788NchsA0iryxv7pirLnd3LVKj6/z661SeKw7Y6dGaqZMeaJ8hqAoUZ3r4pvfx5paf5ppj5EsTZCtk4yBqYr/8XxcZHd7cXY7SXJLUm2PdGV3dV1LSFvRf1lqTln87VT861CNXkj01kP540GfYEAkG0Ctdz3NdD19bTxTHjy/lTtsODq3eTjNiDbMdjVpWzVwJzWUVjH00DCqVS+rhC3UjizVgirehynpvCLqsiL12RyCIu6zAAAAU3RSTlMA/gQcERAIRy8m/TL++vbz69nRxMKrqJmXc09CPDErHRcJ/ff08/Ls6ufm5ubl5dzb0c7KxcG5uLe2sqyqoZqTjo2LioiGfGVWVFJRTEw/PTQpHrmhBvoAAAIFSURBVCjPYoADBW52dm4FBjSgyC4uJMDPLyAkzq6IEGVi4LbhrUpKrKhITOJVsw2Eict5sBvU1S5akZSYmFQ1e95cfWdWiISrrlbD+q1dO6orK6sza+sWL1eykgcbZBFacOnilK6FNZmZNVnJKUvnrN5szwSUYBUOKS0pnsG1ZdWyrKwFySmp85ekacoCJfzVo6NKiot4+k/vTgYKp2avW5l22B0oYRcSElUyeeqtg9vXpKRmR0ZGbmve2ebEwCAvGBwSHTX59ox9a+vrsyMb09MbmlvTjDkY5AQjgkOiogpO7doYCRRuysjdsKktjcuTgUMvLDgkZHpvS3pOTg5QuD1v/5HjrcregDFwaMdGBJeH9h/KyGjZuwco3nHm2MSJGrJA15aFxUQER086cfTCTa4DeXGd567dm2UC9LxjeGhYTDDQBSHlhT2dHXGX7866M8UN6FwpRrBMeeGVs/lxcXEnb8ycWcTjBfK5KWN4LFBmwvn87u78nqtFU4sLVPwYQFpUQXoigoMn9PVdnzRtWlR0ryUrOBQdEhjDy0CaQkKiS0tDQiKMOCDhHmSdwBgfHhobFgF0RHBEqHkANAJZZET5QFJloaGxsdMLRWRYmCAybMzSLoZ8CUC5+HhGHTFfZjZY5LKxcEpLSoiKmIlJSPpwskDEocaxsTAzc3IyM7OwQY0BANeqq70ox7BgAAAAAElFTkSuQmCC';
		
		$p = Photo::fromData('image/png', $img);
		
		$this->assertEquals($p->body, $img);
		$this->assertEquals($p->contentType, 'image/png');
		
		
		// setting a property is not allowed
		$this->expectException(\Nettools\GoogleAPI\Exceptions\ServiceException::class);
		$p->body = "empty";
	}
	
    
        
}

?>