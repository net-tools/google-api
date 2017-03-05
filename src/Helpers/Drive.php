<?php
/**
 * Drive
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Helpers;



/**
 * Drive helper
 *
 * Provides helper functions to Drive API, especially for listing files, exporting file or obtaining a preview link.
 */
class Drive extends Service
{
	const MIMETYPE_FOLDER = 'application/vnd.google-apps.folder';
	const MIMETYPE_SPREADSHEET = 'application/vnd.google-apps.spreadsheet';
	const MIMETYPE_DOCUMENT = 'application/vnd.google-apps.document';
	const MIMETYPE_PRESENTATION = 'application/vnd.google-apps.presentation';
	const MIMETYPE_DRAWING = 'application/vnd.google-apps.drawing';
	const MIMETYPE_PHOTO = 'application/vnd.google-apps.photo';
	const MIMETYPE_VIDEO = 'application/vnd.google-apps.video';
	const MIMETYPE_AUDIO = 'application/vnd.google-apps.audio';

    
    
	/**
     * List Drive files
     *
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Google_Service_Drive_FileList Collection of files
     */
	public function listFiles($optparams = array()) 
	{
		$files = array();
		$pageToken = NULL;
		
		
		do
		{
			// if received a token for next page, include it in the $optparams
			if ($pageToken)
				$optparams['pageToken'] = $pageToken;
			
			// request
			$filesResponse = $this->_service->files->listFiles($optparams);
			
			// if request ok
			if ( $filesResponse->files )
			{
				$files = array_merge($files, $filesResponse->files);
				$pageToken = $filesResponse->getNextPageToken();
			}
		} 
		while ($pageToken);
		

		return $files;
	}

    
	
	/** 
     * Get binary data for a file content (export)
     *
     * @param string $id File ID
     * @param string|null $mimeType Mime type for file export or NULL if an error occured
     */
	public function exportFile($id, $mimeType)
	{
		$psr7_response = $this->service->files->export($id, $mimeType);
		if ( $psr7_response->getStatusCode() == 200 )
			return (string) $psr7_response->getBody();
		else
			return NULL;
	}
	
	
	/** 
     * Get a preview link for a document
     *
     * @param \Google_Service_Drive_DriveFile $doc File entry
     * @return string URI for document preview
     */
	public function previewLink(\Google_Service_Drive_DriveFile $doc)
	{
		switch ( $doc->mimeType )
		{
			case 'application/vnd.google-apps.spreadsheet':
				return 'https://docs.google.com/spreadsheets/d/' . $doc->id .'/preview';
			
			case 'application/vnd.google-apps.document':
				return 'https://docs.google.com/document/d/' . $doc->id .'/preview';
				
			default:
				//https://docs.google.com/uc?id=0B2dWYTPrxxcVZ3JFazFKSlo4RW8&export=download
				//return str_replace('&export=download', '', $doc->webContentLink);
				return 'https://drive.google.com/file/d/' . $doc->id . '/view';
		}
	}
    
}

?>