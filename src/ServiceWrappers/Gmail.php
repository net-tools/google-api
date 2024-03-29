<?php
/**
 * Gmail
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers;



/**
 * Gmail helper
 *
 * Provides helper functions to Gmail API
 */
class Gmail extends ServiceWrapper
{
	const GMAIL_INBOX = 'INBOX';
	const GMAIL_DRAFT = 'DRAFT';
	const GMAIL_UNREAD = 'UNREAD';
	const GMAIL_IMPORTANT = 'IMPORTANT';
	const GMAIL_STARRED = 'STARRED';
	const GMAIL_SPAM = 'SPAM';
	const GMAIL_TRASH = 'TRASH';
	const GMAIL_SENT = 'SENT';

    

	/**
     * Get a list of messages IDs, with optional parameters (query)
     * 
     * Don't make a mistake by thinking 'listAllUsersMessages' means listing all email with no filtering options. 'All' means we want to
     * fetch the entire list in one call, and not bother with page tokens. To select emails to include, use any filter available 
     * through the $optparams parameter (see API reference for a list of filters and syntax).
     *
     * Moreover, this method only returns messages IDs, not actual content. You have to fetch each the email content by calling $service->users_messages->get($userid, $messageid).
     *
     * @param string $userid User ID concerned (email) or 'me' to indicate the authenticated user
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return \Google\Service\Gmail\Message[] An array of emails entries with messages IDs only
     */
	public function listAllUsersMessages($userid, $optparams = array())
	{
		// prise en compte de la pagination
		$pageToken = NULL;
		
		// tableau résultant
		$messages = array();
		
		
		do
		{
			// if received a token for next page, include it in the $optparams
			if ($pageToken)
				$optparams['pageToken'] = $pageToken;
			
			// request
			$messagesResponse = $this->_service->users_messages->listUsersMessages($userid, $optparams);
			
			// if request ok
			if ( $messagesResponse->getMessages() )
			{
				$messages = array_merge($messages, $messagesResponse->getMessages());
				$pageToken = $messagesResponse->getNextPageToken();
			}
		} 
		while ($pageToken);
		
		
		return $messages;
	}
    

	/**
     * Get a message attachment and decode it (as Gmail encodes it with base64)
     *
     * @param string $userid User ID concerned (email) or 'me' to indicate the authenticated user
     * @param string $mid Message ID
     * @param string $aid Attachment ID
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return string The decoded attachment as a string
     */
	public function getMessageAttachmentDecoded($userid, $mid, $aid, $optparams = array())
	{
		return $this->base64RFC4648Decode($this->_service->users_messages_attachments->get($userid, $mid, $aid, $optparams)->data);
	}
	
	
	/**
     * Get a message attachment (encoded by Gmail with base64)
     *
     * @param string $userid User ID concerned (email) or 'me' to indicate the authenticated user
     * @param string $mid Message ID
     * @param string $aid Attachment ID
     * @param string[] $optparams Array of parameters for request, as defined in the API protocol reference
     * @return string The attachment as a base64-encoded string
     */
	public function getMessageAttachment($userid, $mid, $aid, $optparams = array())
	{
		return str_replace('_', '/', str_replace('-', '+', $this->_service->users_messages_attachments->get($userid, $mid, $aid, $optparams)->data));
	}
	
	
	
	/**
	 * Search a label and get its ID.
	 *
	 * No cache mechanism is used, please implement something more efficient in your own code if many calls are to be done.
	 *
	 * @param string $label
	 * @return string Return label ID with $label as title
	 * @throws \Nettools\GoogleAPI\Exceptions\Exception If no label found with $label as title, an exception is thrown
	 */
	public function getLabel($userid, $label)
	{
		// get a list of labels
		$labels = $this->_service->users_labels->listUsersLabels($userid);
		
		// search for label $label
		foreach ( $labels as $g )
		{
			if ( $g->name == $label )
				return $g->id;
		}
			
		throw new \Nettools\GoogleAPI\Exceptions\Exception("Label '$label' not found");
	}
	
	
		
	/**
     * Helper method to decode quoted printable rfc4648.
     *
     * Before decoding from base64 we have to replace _ by / and - by +, as Google API does those suspicious replacements after encoding to base64
     *
     * @param string $data Base64 encoded string
     * @return string Decoded string
     */
	public function base64RFC4648Decode($data)
	{
		return base64_decode(str_replace('_', '/', str_replace('-', '+', $data)));
	}
	

	/**
     * Look for a particular header in the message headers list
     *
     * @param \Google\Service\Gmail\Message $email Message object
     * @param string $header Header name to look for
     * @return string|string[]|false Return the header value or an array of header values if multiple headers with name $header are found ; if not found, returns FALSE
     */
	public function getMessageHeader(\Google\Service\Gmail\Message $email, $header)
	{
		return $this->getMessagePartHeader($email->payload, $header);
	}
	
	
	/**
     * Look for a particular header in a message part headers list
     *
     * @param \Google\Service\Gmail\MessagePart $part Message part object
     * @param string $header Header name to look for
     * @return string|string[]|false Return the header value or an array of header values if multiple headers with name $header are found ; if not found, returns FALSE
     */
	public function getMessagePartHeader(\Google\Service\Gmail\MessagePart $part, $header)
	{
        return $this->getHeader($part->headers, $header);
	}
	
	
	/**
     * Look for a particular header in a headers list
     *
     * @param \Google\Service\Gmail\MessagePartHeader[] $headers List of headers (as array of objects with name & value properties)
     * @param string $header Header name to look for
     * @return string|string[]|false Return the header value or an array of header values if multiple headers with name $header are found ; if not found, returns FALSE
     * @throws \Nettools\GoogleAPI\Exceptions\Exception Thrown if $headers is not an array of Google\Service\Gmail\MessagePartHeader objects 
     */
	public function getHeader(array $headers, $header)
	{
		$ret = array();
		foreach ( $headers as $h )
            if ( !($h instanceof \Google\Service\Gmail\MessagePartHeader) )
                throw new \Nettools\GoogleAPI\Exceptions\Exception("'headers' parameter for getHeader is not an array of Google\\Service\\Gmail\\MessagePartHeader objects");
            else
                if ( $h->name == $header )
                    $ret[] = $h->value;
        

        if ( count($ret) == 0 )
            return FALSE;
        elseif ( count($ret) == 1 )
            return $ret[0];
        else
            return $ret;
	}
	
	
	/**
     * Get a specific message part, identified by its Mime-type
     *
     * The message part is returned as provided by the API, that is to say it is still base64 encoded. To get a specific message part and
     * decode it, call getMessageBody() method instead with the appropriate suitable content-types.
     *
     * @param \Google\Service\Gmail\Message $email Message object 
     * @param string $searchFor Part Content-type to look for
     * @return \Google\Service\Gmail\MessagePart Returns the part object if found, FALSE otherwise
     */     
	public function getMessagePart(\Google\Service\Gmail\Message $email, $searchFor)
	{ 
		return $this->_getMessagePartRecursive($email->payload, $searchFor);
	}
	
    
	private function _getMessagePartRecursive(\Google\Service\Gmail\MessagePart $part, $searchFor)
	{ 
		// if found
		if ( $part->mimeType == $searchFor )
			return $part;
			
		// recursive calls on child parts
		else
			if ( $part->parts )
				foreach ( $part->parts as $p )
					if ( $ret = $this->_getMessagePartRecursive($p, $searchFor) )
						return $ret;
		
		
		// if we arrive here, not found
		return FALSE;
	}
	
	
	/**
     * Get a message body (usaually text/html or text/plain) and decode it (as Google always encodes it to base64)
     *
     * @param \Google\Service\Gmail\Message $email Message object 
     * @param string[] $contentTypes Array of suitable content-types. The first content-type found in the message is used. So you may set it to ['text/html', 'text/plain'] and not ['text/plain', 'text/html'].
     * @return Gmail\MessageBody|null Returns the part content as a Gmail\MessageBody object ; returns NULL if no suitable part is found
     */     
	public function getMessageBody(\Google\Service\Gmail\Message $email, $contentTypes = array('text/html', 'text/plain'))
	{
		// search the message for suitable content-types 
		foreach ( $contentTypes as $contentType )
			// if found a part with a suitable content-type
			if ( $part = $this->getMessagePart($email, $contentType) )
                return new Gmail\MessageBody(
                            $this->base64RFC4648Decode($part->body->data),
                            $contentType,
                            $part->headers
                        );
		
        // not found
        return NULL;
	}

	
	/**
     * Get message subject ; shortcut to Gmail::getMessageHeader($email, 'Subject')     
     * 
     * @param \Google\Service\Gmail\essage $email
     * @return string The email subject as a string
     */
	public function getMessageSubject(\Google\Service\Gmail\Message $email)
	{
		return $this->getMessageHeader($email, 'Subject');
	}


	/** 
     * Get message send date/time
     *
     * @param \Google\Service\Gmail\Message $email
     * @return int|string|null Returns a UNIX timestamp with local timezone ; returns a string with Date header if the date format cannot be parsed ; returns NULL if no Date header
     */
	public function getMessageDate(\Google\Service\Gmail\Message $email)
	{
		// extract Date header
		if ( $dt = $this->getMessageHeader($email, 'Date') )
		{
			// Mon, 15 Jun 2015 13:56:43 +0000 (GMT+00:00)
			$d = date_create_from_format(\DateTime::RFC2822, trim(preg_replace('/\(.*\)/','', $dt)));
			if ( $d )
			{
                // if timezone configured in php
                $tz = ini_get('date.timezone');
                
                if ( $tz )
                {
				    $d->setTimeZone(new \DateTimeZone($tz));
				    return $d->getTimestamp();
                }
            }
            
                
            // if date format is wrong or timezone not configured, returning date as a string
            return $dt;
		}
		
		// no Date header
		else 
			return NULL;
	}
	
    
	/**
     * Get a list of attachments for a message
     *
     * @param \Google\Service\Gmail\Message $email
     * @return Gmail\MessageAttachment[]|null Returns an array of attachements objects or NULL if email has no attachments
     */
	public function getMessageAttachments(\Google\Service\Gmail\Message $email)
	{
		// check that we do have attachments
		if ( $email->payload->mimeType != 'multipart/mixed' )
			return NULL;
			
		
		$att = array();

		// look for attachments in all parts
		foreach ( $email->payload->parts as $part )
		{
			// read content-disposition
			$h = $this->getMessagePartHeader($part, 'Content-Disposition');
			
			// if no content-disposition, next part, no attachment here
			if ( !$h )
				continue;
			
			// check if content-disposition header begins with 'attachment'
			if ( strpos($h, 'attachment') === FALSE )
				continue;
			
			// if found, get attachment data
			$att[] = new Gmail\MessageAttachment(
                            $part->body->attachmentId,
                            $part->filename,
                            $part->mimeType,
							$part->headers
						);
		}
		
		return $att;
	}    


	/**
     * Get a list of inline attachments (usually embedded images such as logos or signatures) for a message
     *
     * @param \Google\Service\Gmail\Message $email
     * @return Gmail\MessageAttachment[]|null Returns an array of inline attachements objects or NULL if email has no attachments
     */
    public function getMessageInlineAttachments(\Google\Service\Gmail\Message $email)
	{
		// check that we do have inline attachments
		$relatedPart = $this->getMessagePart($email, 'multipart/related');
		if ( !$relatedPart )
			return NULL;	
		
		// check all parts
		$att = array();
		foreach ( $relatedPart->parts as $part )
		{
			// read content-disposition
			$h = $this->getMessagePartHeader($part, 'Content-Disposition');
			
			// if no content-disposition, next part, no attachment here
			if ( !$h )
				continue;
			
			// check if content-disposition header begins with 'inline'
			if ( strpos($h, 'inline') === FALSE )
				continue;
			
			// if found, get attachment data
			$att[] = new Gmail\MessageAttachment(
                            $part->body->attachmentId,
                            $this->getHeader($part->headers, 'Content-ID'),
                            $part->mimeType,
							$part->headers
						);
		}
		
		return $att;
	}


	/**
     * Get a message body with its inline attachments converted to inline images (HTML tags with IMG SRC attribute set to data:image/jpeg;base64,....)
     *
     * @param \Google\Service\Gmail\Message $email Message object 
     * @param string $userid User id or special value 'me' (required to fetch attachments : we have to send a request to the API)
     * @param string[] $contentTypes Array of suitable content-types. The first content-type found in the message is used. So you may set it to ['text/html', 'text/plain'] and not ['text/plain', 'text/html'].
     * @return Gmail\MessageBody Returns the body content with inline embeddings converted to IMG tags with base64 content in their SRC attribute
     */
	public function getMessageBodyWithInlineAttachments(\Google\Service\Gmail\Message $email, $userid, $contentTypes = array('text/html', 'text/plain'))
	{
        // parse email and extract body content (with mimeType and headers)
        $body = $this->getMessageBody($email, $contentTypes);
        $bodycontent = $body->body;
        
		// get inline attachments
		$atts = $this->getMessageInlineAttachments($email);
		if ( $atts )
			foreach ( $atts as $att )
			{
                // get Content-ID header ; if not found, skip
				$cid = $this->getHeader($att->headers, 'Content-ID');
				if ( !$cid )
					continue;
					
				// extract CID (found with <cid> pattern)
				$cid = substr($cid, 1, -1);
				
				// get mimeType
				$mime = $att->mimeType;
				
				// if CID found in body, fetching attachment content and set it as an inline IMG tag with base64 content
				if ( preg_match("/cid:$cid/", $bodycontent) )
					$bodycontent = preg_replace("/cid:$cid/", "data:$mime;base64," . $this->getMessageAttachment($userid, $email->id, $att->id), $bodycontent);
			}
		
		return new Gmail\MessageBody(
						$bodycontent,
						$body->mimeType,
						$body->headers
					);
	}

}


?>