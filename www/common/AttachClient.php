<?php

declare(strict_types=1);

use WebPageTest\RequestContext;
use WebPageTest\CPClient;
use WebPageTest\Util;
use WebPageTest\Util\OAuth as CPOauth;

(function (RequestContext $request) {

    $host = Util::getSetting('host');

    $client = new CPClient(Util::getSetting('cp_services_host'), array(
        'auth_client_options' => array(
            'base_uri' => Util::getSetting('cp_auth_host'),
            'client_id' => Util::getSetting('cp_auth_client_id'),
            'client_secret' => Util::getSetting('cp_auth_client_secret')
        )
    ));

  $access_token = null;
  $refresh_token = null;
  $cp_access_token_cookie_name = Util::getCookieName(CPOauth::$cp_access_token_cookie_key);
  $cp_refresh_token_cookie_name = Util::getCookieName(CPOauth::$cp_refresh_token_cookie_key);

  if (isset($_COOKIE[$cp_access_token_cookie_name]) && $_COOKIE[$cp_access_token_cookie_name] != null) {
    $access_token = $_COOKIE[$cp_access_token_cookie_name];
  }
  if (isset($_COOKIE[$cp_refresh_token_cookie_name]) && $_COOKIE[$cp_refresh_token_cookie_name] != null) {
    $refresh_token = $_COOKIE[$cp_refresh_token_cookie_name];
  }

  if (!is_null($access_token)) {
    $client->authenticate($access_token);
  } else if (is_null($access_token) && !is_null($refresh_token)) {
    $auth_token = $client->refreshAuthToken($refresh_token);
    $client->authenticate($auth_token->access_token);
    setcookie($cp_access_token_cookie_name, $auth_token->access_token, time() + $auth_token->expires_in, "/", $host);
    setcookie($cp_refresh_token_cookie_name, $auth_token->refresh_token, time() + 60*60*24*30, "/", $host);
  }

    $request->setClient($client);
})($request_context);

?>
