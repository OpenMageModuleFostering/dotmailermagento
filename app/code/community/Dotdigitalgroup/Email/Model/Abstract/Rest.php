<?php

abstract class Dotdigitalgroup_Email_Model_Abstract_Rest
{
    protected $url;
    protected $verb;
    protected $requestBody;
    protected $requestLength;
    protected $_apiUsername;
    protected $_apiPassword;
    protected $acceptType;
    protected $responseBody;
    protected $responseInfo;

    public function __construct($website = 0) // ($url = null, $verb = 'GET', $requestBody = null)
    {
        $this->url				= null; //$url;
        $this->verb				= 'GET'; //$verb;
        $this->requestBody		= null; //$requestBody;
        $this->requestLength	= 0;
        $this->_apiUsername     = (string)Mage::helper('connector')->getApiUsername($website);
        $this->_apiPassword 	= (string)Mage::helper('connector')->getApiPassword($website);
        $this->acceptType		= 'application/json';
        $this->responseBody		= null;
        $this->responseInfo		= null;

        if ($this->requestBody !== null)
        {
            $this->buildPostBody();
        }
    }

    private function prettyPrint($json)
    {
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if( $char === '"' && $prev_char != '\\' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
            $prev_char = $char;
        }

        return $result;
    }

    // returns the object as JSON
    public function toJSON($pretty=false){

        if (!$pretty) {
            return json_encode($this->expose());
        }
        else {
            return $this->prettyPrint(json_encode($this->expose()));
        }
    }

    // exposes the class as an array of objects
    public function expose() {

        return get_object_vars($this);

    }


    public function flush ()
    {
        $this->_apiUsername = '';
        $this->_apiPassword = '';
        $this->requestBody		= null;
        $this->requestLength	= 0;
        $this->verb				= 'GET';
        $this->responseBody		= null;
        $this->responseInfo		= null;
        return $this;
    }

    public function execute()
    {
        $ch = curl_init();
        $this->setAuth($ch);
        try
        {
            switch (strtoupper($this->verb))
            {
                case 'GET':
                    $this->executeGet($ch);
                    break;
                case 'POST':
                    $this->executePost($ch);
                    break;
                case 'PUT':
                    $this->executePut($ch);
                    break;
                case 'DELETE':
                    $this->executeDelete($ch);
                    break;
                default:
                    throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
            }
        }
        catch (InvalidArgumentException $e)
        {
            curl_close($ch);
            throw $e;
        }
        catch (Exception $e)
        {
            curl_close($ch);
            throw $e;
        }

        return $this->responseBody;
    }

    public function buildPostBody($data = null)
    {

        $this->requestBody = json_encode($data);
        return $this;
    }

    protected function executeGet($ch)
    {
        $this->doExecute($ch);
    }

    protected function executePost($ch)
    {
        if (!is_string($this->requestBody))
        {
            $this->buildPostBody();
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
        curl_setopt($ch, CURLOPT_POST, true);

        $this->doExecute($ch);
    }
    protected function buildPostBodyFromFile($filename){

        $this->requestBody = array (
            'file' => '@'.$filename
        );

    }

    protected function executePut($ch)
    {
        if (!is_string($this->requestBody)){
            $this->buildPostBody();
        }

        $this->requestLength = strlen($this->requestBody);

        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $this->requestBody);
        rewind($fh);

        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
        curl_setopt($ch, CURLOPT_PUT, true);

        $this->doExecute($ch);

        fclose($fh);
    }

    protected function executeDelete($ch)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $this->doExecute($ch);
    }

    protected function doExecute(&$ch)
    {
        $this->setCurlOpts($ch);
        $this->responseBody = json_decode(curl_exec($ch));
        $this->responseInfo	= curl_getinfo($ch);

        curl_close($ch);
    }

    protected function setCurlOpts(&$ch)
    {
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Accept: ' . $this->acceptType ,'Content-Type: application/json'));
    }

    protected function setAuth(&$ch)
    {
        if ($this->_apiUsername !== null && $this->_apiPassword !== null)
        {
            curl_setopt($ch, CURLAUTH_BASIC, CURLAUTH_DIGEST);
            curl_setopt($ch, CURLOPT_USERPWD, $this->_apiUsername . ':' . $this->_apiPassword);
        }
    }

    public function getAcceptType()
    {
        return $this->acceptType;
    }

    public function setAcceptType($acceptType)
    {
        $this->acceptType = $acceptType;
    }

    public function getApiPassword()
    {
        return $this->_apiPassword;
    }

    public function setApiPassword($apiPassword)
    {
        $this->_apiPassword = $apiPassword;
        return $this;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getResponseInfo()
    {
        return $this->responseInfo;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getApiUsername()
    {
        return $this->_apiUsername;
    }

    public function setApiUsername($apiUsername)
    {
        $this->_apiUsername = $apiUsername;
        return $this;
    }

    public function getVerb ()
    {
        return $this->verb;
    }

    public function setVerb ($verb)
    {
        $this->verb = $verb;
        return $this;
    }
}
