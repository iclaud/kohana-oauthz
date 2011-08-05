<?php
/**
 * Grant type is authorization_code
 *
 * Oauth parameter handler for webserver flow
 *
 * @author      sumh <oalite@gmail.com>
 * @package     Oauthz
 * @copyright   (c) 2010 OALite
 * @license     ISC License (ISCL)
 * @link        http://oalite.com
 * @see         Oauthz_Extension
 * *
 */
class Oauthz_Extension_Authorization_Code extends Oauthz_Extension {

    /**
     * REQUIRED.  The client identifier as described in Section 2.1.
     *
     * @access	public
     * @var		string	$client_id
     */
    public $client_id;

    /**
     * REQUIRED.  The redirection URI used in the initial request.
     *
     * @access	public
     * @var		string	$redirect_uri
     */
    public $redirect_uri;

    /**
     * Load oauth parameters from GET or POST
     *
     * @access	public
     * @param	string	$flag	default [ FALSE ]
     * @return	void
     */
    public function __construct(array $args)
    {
        // Load oauth_token from form-encoded body
        isset($_SERVER['CONTENT_TYPE']) OR $_SERVER['CONTENT_TYPE'] = getenv('CONTENT_TYPE');

        // oauth_token already send in authorization header or the encrypt Content-Type is not single-part
        if(stripos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') === FALSE)
        {
            throw new Oauthz_Exception_Token('invalid_request');
        }
        else
        {
            // Check all required parameters should NOT be empty
            foreach($args as $key => $val)
            {
                if($val === TRUE)
                {
                    if(isset($_POST[$key]) AND $value = Oauthz::urldecode($_POST[$key]))
                    {
                        $this->$key = $value;
                    }
                    else
                    {
                        throw new Oauthz_Exception_Token('invalid_request');
                    }
                }
            }
        }
    }

    /**
     * Populate the access token thu the request info and client info stored in the server
     *
     * @access	public
     * @param	array	$client
     * @return	Oauthz_Token
     * @throw   Oauthz_Exception_Authorize    Error Codes: invalid_request, invalid_scope
     */
    public function execute()
    {
        if($client = Model_Oauthz::factory('Token')->oauth_token($this->client_id, $this->code))
        {
            //$audit = new Model_Oauthz_Audit;
            //$audit->audit_token($response);

            // Verify the oauth token send by client
        }
        else
        {
            throw new Oauthz_Exception_Token('invalid_client');
        }

        $response = new Oauthz_Token;

        if($client['redirect_uri'] !== $this->redirect_uri)
        {
            $exception = new Oauthz_Exception_Token('invalid_request');

            throw $exception;
        }

        if($client['client_secret'] !== sha1($this->client_secret))
        {
            throw new Oauthz_Exception_Token('invalid_client');
        }

        $response->token_type       = $client['token_type'];
        $response->access_token     = $client['access_token'];
        $response->refresh_token    = $client['refresh_token'];
        $response->expires_in       = (int) $client['expires_in'];

        return $response;
    }

} // END Oauthz_Extension_Authorization_Code
