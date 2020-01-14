<?php

namespace sgkirby\Selfauth;

use Page, Response;

/**
 * Kirby 3 Selfauth
 *
 * @version   0.1.0
 * @author    Sebastian Greger <msg@sebastiangreger.net>
 * @copyright Sebastian Greger <msg@sebastiangreger.net>
 * @link      https://github.com/sebastiangreger/kirby3-selfauth
 * @license   MIT
 */

require( __DIR__ . DS . 'helpers.php' );

\Kirby::plugin('sgkirby/selfauth', [

    'templates' => [
        'selfauth-login' => __DIR__ . '/templates/selfauth-login.php',
        'selfauth-authenticate' => __DIR__ . '/templates/selfauth-authenticate.php',
	],

    'routes' => function ($kirby) {

		return [

			[	

				'pattern' => option( 'sgkirby.selfauth.endpoint', 'auth' ) . '-setup',
				'method' => 'GET|POST',
				'action'  => function() {

					// KIRBY: process kirby login if submitted
					if (kirby()->request()->is('POST') && get('kirbylogin')) :
						if ($user = kirby()->user(get('email'))) {
							try {
								$user->login(get('pass'));
							} catch ( \Exception $e ) {
								$error = "Login failed";
							}
						} else {
							$error = "Login failed";
						}
					endif;

					// KIRBY: do not proceed beyond verification unless logged in
					if (!kirby()->user()) :
						return Page::factory([
							'slug' => 'selfauth-login',
							'template' => 'selfauth-login',
							'model' => 'virtual',
							'content' => [
								'action' => option( 'sgkirby.selfauth.endpoint', 'auth' ) . '-setup',
								'errormsg' => !empty($error) ? $error : false,
							],
						]);
					endif;
					
					define('RANDOM_BYTE_COUNT', 32);

					// KIRBY: since php7 is required, can rely on random_bytes
					$bytes = random_bytes(RANDOM_BYTE_COUNT);
					$strong_crypto = true;
					$app_key = bin2hex($bytes);

					$configured = true;

					// KIRBY: replacing the config file with Kirby config options
					if ( option( 'sgkirby.selfauth.userurl' )
						&& option( 'sgkirby.selfauth.appkey' )
						&& option( 'sgkirby.selfauth.userhash' )
						&& option( 'sgkirby.selfauth.userurl' ) ) {

						define('APP_URL', option( 'sgkirby.selfauth.appurl' ));
						define('APP_KEY', option( 'sgkirby.selfauth.appkey' ));
						define('USER_HASH', option( 'sgkirby.selfauth.userhash' ));
						define('USER_URL', option( 'sgkirby.selfauth.userurl' ));

						if ((!defined('APP_URL') || APP_URL == '')
							|| (!defined('APP_KEY') || APP_KEY == '')
							|| (!defined('USER_HASH') || USER_HASH == '')
							|| (!defined('USER_URL') || USER_URL == '')
						) {
							$configured = false;
						}
					} else {
						$configured = false;
					}

					if ($configured) :
						return new Response( 'System already configured. If you wish to reconfigure, remove all sgkirby.selfauth.* variables from site/config/config.php', 'text/html' );

					// KIRBY: no need to provide form, as all needed values can be derived from Kirby
					else :
						$app_url = rtrim( site()->url(), '/' ) . '/' . option( 'sgkirby.selfauth.endpoint', 'auth' );
						$user = rtrim( site()->url(), '/' );
						$user_tmp = trim(preg_replace('/^https?:\/\//', '', $user), '/');
						// KIRBY: instead of a password, use the kirby user id to ensure only the admin user may authenticate via indieauth
						$pass = md5($user_tmp . kirby()->user()->id() . $app_key);
						// KIRBY: no auto-writing of the config; setup options need to be hand-copied into config
						echo "
							Please add these lines to your site/config/config.php to authorize yourself (user: " . kirby()->user()->username() . ") to identify using Indieauth:<br><br>
							'sgkirby.selfauth.appurl' => '$app_url',<br>
							'sgkirby.selfauth.appkey' => '$app_key',<br>
							'sgkirby.selfauth.userhash' => '$pass',<br>
							'sgkirby.selfauth.userurl' => '$user',
						";

					endif;
					
				}

			],
			[

				'pattern' => option( 'sgkirby.selfauth.endpoint', 'auth' ),
				'method' => 'GET|POST',
				'action'  => function() {

					function error_page($header, $body, $http = '400 Bad Request')
					{
						$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
						header($protocol . ' ' . $http);
						die($header . ': ' . $body);
					}

					// KIRBY: replacing the config file with Kirby config options
					if ( option( 'sgkirby.selfauth.userurl' )
						&& option( 'sgkirby.selfauth.appkey' )
						&& option( 'sgkirby.selfauth.userhash' )
						&& option( 'sgkirby.selfauth.userurl' ) ) {
						define('APP_URL', option( 'sgkirby.selfauth.appurl' ));
						define('APP_KEY', option( 'sgkirby.selfauth.appkey' ));
						define('USER_HASH', option( 'sgkirby.selfauth.userhash' ));
						define('USER_URL', option( 'sgkirby.selfauth.userurl' ));
					} else {
						error_page(
							'Configuration Error',
							'Endpoint not yet configured.'
						);
					}

					// Enable string comparison in constant time.
					if (!function_exists('hash_equals')) {
						function hash_equals($known_string, $user_string)
						{
							$known_length = strlen($known_string);
							if ($known_length !== strlen($user_string)) {
								return false;
							}
							$match = 0;
							for ($i = 0; $i < $known_length; $i++) {
								$match |= (ord($known_string[$i]) ^ ord($user_string[$i]));
							}
							return $match === 0;
						}
					}

					// Signed codes always have an time-to-live, by default 1 year (31536000 seconds).
					function create_signed_code($key, $message, $ttl = 31536000, $appended_data = '')
					{
						$expires = time() + $ttl;
						$body = $message . $expires . $appended_data;
						$signature = hash_hmac('sha256', $body, $key);
						return dechex($expires) . ':' . $signature . ':' . base64_url_encode($appended_data);
					}

					function verify_signed_code($key, $message, $code)
					{
						$code_parts = explode(':', $code, 3);
						if (count($code_parts) !== 3) {
							return false;
						}
						$expires = hexdec($code_parts[0]);
						if (time() > $expires) {
							return false;
						}
						$body = $message . $expires . base64_url_decode($code_parts[2]);
						$signature = hash_hmac('sha256', $body, $key);
						return hash_equals($signature, $code_parts[1]);
					}

					function verify_password($pass)
					{
						$hash_user = trim(preg_replace('/^https?:\/\//', '', USER_URL), '/');
						$hash = md5($hash_user . $pass . APP_KEY);

						return hash_equals(USER_HASH, $hash);
					}

					function filter_input_regexp($type, $variable, $regexp, $flags = null)
					{
						$options = array(
							'options' => array('regexp' => $regexp)
						);
						if ($flags !== null) {
							$options['flags'] = $flags;
						}
						return filter_input(
							$type,
							$variable,
							FILTER_VALIDATE_REGEXP,
							$options
						);
					}

					function get_q_value($mime, $accept)
					{
						$fulltype = preg_replace('@^([^/]+\/).+$@', '$1*', $mime);
						$regex = implode(
							'',
							array(
								'/(?<=^|,)\s*(\*\/\*|',
								preg_quote($fulltype, '/'),
								'|',
								preg_quote($mime, '/'),
								')\s*(?:[^,]*?;\s*q\s*=\s*([0-9.]+))?\s*(?:,|$)/'
							)
						);
						$out = preg_match_all($regex, $accept, $matches);
						$types = array_combine($matches[1], $matches[2]);
						if (array_key_exists($mime, $types)) {
							$q = $types[$mime];
						} elseif (array_key_exists($fulltype, $types)) {
							$q = $types[$fulltype];
						} elseif (array_key_exists('*/*', $types)) {
							$q = $types['*/*'];
						} else {
							return 0;
						}
						return $q === '' ? 1 : floatval($q);
					}

					// URL Safe Base64 per https://tools.ietf.org/html/rfc7515#appendix-C

					function base64_url_encode($string)
					{
						$string = base64_encode($string);
						$string = rtrim($string, '=');
						$string = strtr($string, '+/', '-_');
						return $string;
					}

					function base64_url_decode($string)
					{
						$string = strtr($string, '-_', '+/');
						$padding = strlen($string) % 4;
						if ($padding !== 0) {
							$string .= str_repeat('=', 4 - $padding);
						}
						$string = base64_decode($string);
						return $string;
					}

					if ((!defined('APP_URL') || APP_URL == '')
						|| (!defined('APP_KEY') || APP_KEY == '')
						|| (!defined('USER_HASH') || USER_HASH == '')
						|| (!defined('USER_URL') || USER_URL == '')
					) {
						error_page(
							'Configuration Error',
							'Endpoint not configured correctly.'
						);
					}

					// First handle verification of codes.
					$code = filter_input_regexp(INPUT_POST, 'code', '@^[0-9a-f]+:[0-9a-f]{64}:@');

					if ($code !== null) {
						$redirect_uri = filter_input(INPUT_POST, 'redirect_uri', FILTER_VALIDATE_URL);
						$client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_URL);

						// Exit if there are errors in the client supplied data.
						if (!(is_string($code)
							&& is_string($redirect_uri)
							&& is_string($client_id)
							&& verify_signed_code(APP_KEY, USER_URL . $redirect_uri . $client_id, $code))
						) {
							error_page('Verification Failed', 'Given Code Was Invalid');
						}

						$response = array('me' => USER_URL);

						$code_parts = explode(':', $code, 3);

						if ($code_parts[2] !== '') {
							$response['scope'] = base64_url_decode($code_parts[2]);
						}

						// Find the q value for application/json.
						$json = get_q_value('application/json', $_SERVER['HTTP_ACCEPT']);

						// Find the q value for application/x-www-form-urlencoded.
						$form = get_q_value('application/x-www-form-urlencoded', $_SERVER['HTTP_ACCEPT']);

						// Respond in the correct way.
						if ($json === 0 && $form === 0) {
							error_page(
								'No Accepted Response Types',
								'The client accepts neither JSON nor Form encoded responses.',
								406
							);
						} elseif ($json >= $form) {
							header('Content-Type: application/json');
							exit(json_encode($response));
						} else {
							header('Content-Type: application/x-www-form-urlencoded');
							exit(http_build_query($response));
						}
					}

					// KIRBY: process kirby login if submitted
					if (kirby()->request()->is('POST') && get('kirbylogin')) :
						if ($user = kirby()->user(get('email'))) {
							try {
								$user->login(get('pass'));
							} catch ( \Exception $e ) {
								$error = "Login failed";
							}
						} else {
							$error = "Login failed";
						}
					endif;

					// KIRBY: do not proceed beyond verification unless logged in
					if (!kirby()->user()) :
						return Page::factory([
							'slug' => 'selfauth-login',
							'template' => 'selfauth-login',
							'model' => 'virtual',
							'content' => [
								'action' => option( 'sgkirby.selfauth.endpoint', 'auth' ) . '?' . $_SERVER['QUERY_STRING'],
								'errormsg' => !empty($error) ? $error : false,
							],
						]);
					endif;
					
					// If this is not verification, collect all the client supplied data. Exit on errors.

					$me = filter_input(INPUT_GET, 'me', FILTER_VALIDATE_URL);
					$client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_URL);
					$redirect_uri = filter_input(INPUT_GET, 'redirect_uri', FILTER_VALIDATE_URL);
					$state = filter_input_regexp(INPUT_GET, 'state', '@^[\x20-\x7E]*$@');
					$response_type = filter_input_regexp(INPUT_GET, 'response_type', '@^(id|code)?$@');
					$scope = filter_input_regexp(INPUT_GET, 'scope', '@^([\x21\x23-\x5B\x5D-\x7E]+( [\x21\x23-\x5B\x5D-\x7E]+)*)?$@');

					if (!is_string($client_id)) { // client_id is either omitted or not a valid URL.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "client_id" field is invalid.'
						);
					}
					if (!is_string($redirect_uri)) { // redirect_uri is either omitted or not a valid URL.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "redirect_uri" field is invalid.'
						);
					}
					if ($state === false) { // state contains invalid characters.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "state" field contains invalid data.'
						);
					}
					if ($response_type === false) { // response_type is given as something other than id or code.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "response_type" field must be "id" or "code".'
						);
					}
					if ($scope === false) { // scope contains invalid characters.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "scope" field contains invalid data.'
						);
					}
					if ($scope === '') { // scope is left empty.
						// Treat empty parameters as if omitted.
						$scope = null;
					}
					if ($response_type === null || $response_type === '') { // response_type is omitted or left empty.
						// For omitted or left empty, use the default response_type.
						$response_type = 'id';
					}
					if ($response_type !== 'code' && $scope !== null) { // scope defined on identification request.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "scope" field cannot be used with identification.'
						);
					}
					if ($response_type === 'code' && $scope === null) { // scope omitted on code request.
						error_page(
							'Faulty Request',
							'There was an error with the request. The "scope" field must be used with code requests.'
						);
					}

					// If the user submitted a password, get ready to redirect back to the callback.

					$pass_input = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

					if ($pass_input !== null) {
						$csrf_code = filter_input(INPUT_POST, '_csrf', FILTER_UNSAFE_RAW);

						// Exit if the CSRF does not verify.
						if ($csrf_code === null || !verify_signed_code(APP_KEY, $client_id . $redirect_uri . $state, $csrf_code)) {
							error_page(
								'Invalid CSF Code',
								'Usually this means you took too long to log in. Please try again.'
							);
						}

						// Exit if the password does not verify.
						// KIRBY: instead of a password, use the kirby user id to validate that this is the user selfauth was set up for
						if (!verify_password( kirby()->user()->id() )) {
							// Optional logging for failed logins.
							//
							// Enabling this on shared hosting may not be a good idea if syslog
							// isn't private and accessible. Enable with caution.
							if (function_exists('syslog') && defined('SYSLOG_FAILURE') && SYSLOG_FAILURE === 'I understand') {
								syslog(LOG_CRIT, sprintf(
									'IndieAuth: login failure from %s for %s',
									$_SERVER['REMOTE_ADDR'],
									$me
								));
							}

							error_page('Login Failed', 'Invalid password.');
						}

						$scope = filter_input_regexp(INPUT_POST, 'scopes', '@^[\x21\x23-\x5B\x5D-\x7E]+$@', FILTER_REQUIRE_ARRAY);

						// Scopes are defined.
						if ($scope !== null) {
							// Exit if the scopes ended up with illegal characters or were not supplied as array.
							if ($scope === false || in_array(false, $scope, true)) {
								error_page('Invalid Scopes', 'The scopes provided contained illegal characters.');
							}

							// Turn scopes into a single string again.
							$scope = implode(' ', $scope);
						}

						$code = create_signed_code(APP_KEY, USER_URL . $redirect_uri . $client_id, 5 * 60, $scope);

						$final_redir = $redirect_uri;
						if (strpos($redirect_uri, '?') === false) {
							$final_redir .= '?';
						} else {
							$final_redir .= '&';
						}
						$parameters = array(
							'code' => $code,
							'me' => USER_URL
						);
						if ($state !== null) {
							$parameters['state'] = $state;
						}
						$final_redir .= http_build_query($parameters);

						// Optional logging for successful logins.
						//
						// Enabling this on shared hosting may not be a good idea if syslog
						// isn't private and accessible. Enable with caution.
						if (function_exists('syslog') && defined('SYSLOG_SUCCESS') && SYSLOG_SUCCESS === 'I understand') {
							syslog(LOG_INFO, sprintf(
								'IndieAuth: login from %s for %s',
								$_SERVER['REMOTE_ADDR'],
								$me
							));
						}

						// Redirect back.
						header('Location: ' . $final_redir, true, 302);
						exit();
					}

					// If neither password nor a code was submitted, we need to ask the user to authenticate.

					$csrf_code = create_signed_code(APP_KEY, $client_id . $redirect_uri . $state, 2 * 60);

					// KIRBY: display auth screen via template
					return Page::factory([
						'slug' => 'selfauth-authenticate',
						'template' => 'selfauth-authenticate',
						'model' => 'virtual',
						'content' => [
							'client_id' => $client_id,
							'scope' => $scope,
							'redirect_uri' => $redirect_uri,
							'csrf_code' => $csrf_code,
							'user_url' => USER_URL,
						],
					]);
				
				}

			],

		];

	},

]);
