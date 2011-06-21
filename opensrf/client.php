<?php
/**
 * OpenSRF Client for PHP
 *
 * PHP Version 5
 *
 * @category Net
 * @package  OpenSRF
 * @author   Jeff Godin <jgodin@tadl.org>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/tadl
 *
 */

/**
 * Client class
 *
 * @category Net
 * @package  OpenSRF
 * @author   Jeff Godin <jgodin@tadl.org>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/tadl
 */
class OpensrfClientRequest
{
    public $endpoint; // XXX: where's a good place to "default" this?
    private $_curl;
    private $_state; // not requested, error, success?
    private $_service;
    private $_method;
    private $_params;
    private $_success;

    /**
     * Constructor
     *
     * @param string $endpoint URL to gateway
     * @param string $service  OpenSRF service name
     * @param string $method   OpenSRF method name
     * @param array  $params   Array of parameters
     *
     * @return OpensrfClientRequest
     */
    function __construct( $endpoint, $service, $method, $params = array() )
    {
        $this->endpoint = $endpoint;
        $this->_service  = $service;
        $this->_method   = $method;
        $this->_params   = $params;
        $this->_curl     = curl_init();
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($this->_curl, CURLOPT_CAPATH, '/etc/ssl/certs');
        //curl_setopt($this->_curl, CURLOPT_CAINFO, 'thawte.pem');
    } 

    /**
     * Execute
     *
     * @return object|false Request result or false
     */
    function execute( )
    {
        curl_setopt($this->_curl, CURLOPT_URL, $this->endpoint);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $this->_buildPost($params));

        $result_raw = curl_exec($this->_curl);

        // check for curl-level errors
        if ($result_raw === false) {
            $curl_error = curl_error($this->_curl);
            $this->success = false;
            $this->error = $curl_error;
            return false;
        }

        $result = json_decode($result_raw); // XXX: what about json decode failures?

        // Check OpenSRF request status
        if ($result->status == 200) {
            $this->success = true;
        } else {
            $this->success = false;
            $this->error = $result->debug;
        }

        return $result;
    }

    /**
     * _buildPOST
     *
     * @param array $params Parameters for OpenSRF request
     *
     * @return string URL-encoded query string
     */
    private function _buildPOST($params)
    {
        /* gateway requests should be application/x-www-form-urlencoded
           which in php curl land means we use http_build_query and
           don't pass curl an array of post params
           we also don't use the array option because we have to pass
           multiple key/value pairs with the same key
        */

        $post_data = array(
        'service' => $this->_service,
        'method' => $this->_method,
        );

        // XXX: we are not making use of $params at all at this point

        /*  Need to walk over $params as an array
            If we encounter an associative array json-encode it
            What about objects? Need to turn them into their json encoded
            __c/__p equivalents?
            What about non-assoc arrays? json encode them also?
        */

        return(http_build_query($post_data));
    }

    /**
     * Success
     *
     * @return boolean Success or not?
     */
    function success()
    {
        return $this->success;
    }

    // build and make a request
    // check for success
    // give it a gather() method?
    // (what did gather() do again?)
    // ability to display errors 

}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4
