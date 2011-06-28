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
class OpensrfClient
{
    public $endpoint; // XXX: where's a good place to "default" this?
    private $_curl;

    /**
     * Constructor
     *
     * @param string $endpoint URL to gateway
     *
     * @return OpensrfClientRequest
     */
    function __construct( $endpoint )
    {
        $this->endpoint = $endpoint;
        $this->_curl     = curl_init();
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($this->_curl, CURLOPT_CAPATH, '/etc/ssl/certs');
        //curl_setopt($this->_curl, CURLOPT_CAINFO, 'thawte.pem');
        curl_setopt($this->_curl, CURLOPT_URL, $this->endpoint);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_POST, true);
    } 

    /**
     * Request
     *
     * Make a request using this client object
     *
     * @param string $service  OpenSRF service name
     * @param string $method   OpenSRF method name
     * @param array  $params   Array of parameters
     *
     * @return object|false Request result or false
     */
    function request( $service, $method, $params = array() )
    {
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $this->_buildPost($service, $method, $params));

        $response = new OpensrfResponse();

        $result_raw = curl_exec($this->_curl);

        // check for curl-level errors
        if ($result_raw === false) {
            $response->success = false;
            $curl_error = curl_error($this->_curl);
            $response->debug = "curl error: " . $curl_error;
            return $response;
        }

        // record HTTP status code and check for errors
        $http_status = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $response->http_status = $http_status;
        if ($http_status <> '200') {
            $response->success = false;
            $response->debug = "HTTP status: " . $http_status;
            return $response;
        }

        $response->raw = $result_raw;
        $result_decoded = json_decode($result_raw); // XXX: what about json decode failures?
        if ($result_decoded) {
            $response->status  = $result_decoded->status;
            $response->payload = $result_decoded->payload;
            $response->debug   = $result_decoded->debug;
        } else {
            die("JSON decode failure.");
        }

        // Check OpenSRF request status
        if ($response->status == 200) {
            $response->success = true;
        } else {
            $response->success = false;
        }

        return $response;
    }

    /**
     * _buildPost
     *
     * @param array $params Parameters for OpenSRF request
     *
     * @return string URL-encoded query string
     */
    private function _buildPost($service, $method, $params)
    {
        /* gateway requests should be application/x-www-form-urlencoded
           which in php curl land means we use http_build_query and
           don't pass curl an array of post params
           we also don't use the array option because we have to pass
           multiple key/value pairs with the same key
        */

        $post_data = array(
        'service' => $service,
        'method' => $method,
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

}

/**
 *  Opensrf response class
 *
 */
class OpensrfResponse
{
    public $success = false;
    public $http_status;
    public $raw;
    public $status;
    public $payload;
    public $debug;
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4
