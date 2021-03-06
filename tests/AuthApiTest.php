<?php
namespace Auth0\Tests;

use Auth0\SDK\Auth0AuthApi;

class AuthApiTest extends ApiTests {

    public function testAuthorize() {

        $domain = 'dummy.auth0.com';
        $client_id = '123456';

        $api = new Auth0AuthApi($domain, $client_id);

        $authorize_url = $api->get_authorize_link('code', 'http://lala.com');

        $this->assertEquals("https://dummy.auth0.com/authorize?response_type=code&redirect_uri=http%3A%2F%2Flala.com&client_id=123456", $authorize_url);

        $authorize_url2 = $api->get_authorize_link('token', 'http://lala.com', 'facebook', 'dastate');

        $this->assertEquals("https://dummy.auth0.com/authorize?response_type=token&redirect_uri=http%3A%2F%2Flala.com&client_id=123456&connection=facebook&state=dastate", $authorize_url2);
    }

    public function testAuthorizeWithRO() {
        $env = $this->getEnv();
        
        $api = new Auth0AuthApi($env['DOMAIN'], $env['APP_CLIENT_ID']);

        $response = $api->authorize_with_ro('auth@test.com', '123456', 'openid', 'Username-Password-Authentication');

        $this->assertArrayHasKey('id_token', $response);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertEquals('bearer', $response['token_type']);

        $userinfo = $api->userinfo($response['access_token']);

        $this->assertArrayHasKey('email', $userinfo);
        $this->assertArrayHasKey('email_verified', $userinfo);
        $this->assertArrayHasKey('user_id', $userinfo);
        $this->assertEquals('auth@test.com', $userinfo['email']);
        $this->assertEquals('auth0|5730970e4518a40b60170e0f', $userinfo['user_id']);

        $tokeninfo = $api->tokeninfo($response['id_token']);

        $this->assertArrayHasKey('email', $tokeninfo);
        $this->assertArrayHasKey('email_verified', $tokeninfo);
        $this->assertArrayHasKey('user_id', $tokeninfo);
        $this->assertEquals('auth@test.com', $tokeninfo['email']);
        $this->assertEquals('auth0|5730970e4518a40b60170e0f', $tokeninfo['user_id']);
    }

    public function testOauthToken() {
        $env = $this->getEnv();
        
        $api = new Auth0AuthApi($env['DOMAIN'], $env['GLOBAL_CLIENT_ID'], $env['GLOBAL_CLIENT_SECRET']);

        $token = $api->get_access_token();

        $this->assertArrayHasKey('access_token', $token);
        $this->assertArrayHasKey('token_type', $token);
        $this->assertEquals('bearer', strtolower($token['token_type']));
    }

    public function testImpersonation() {
        $env = $this->getEnv();
        
        $api = new Auth0AuthApi($env['DOMAIN'], $env['GLOBAL_CLIENT_ID'], $env['GLOBAL_CLIENT_SECRET']);

        $url = $api->impersonate('facebook|1434903327', "oauth2", 'auth0|56b110b8d9d327e705e1d2da', 'ycynBrUeQUnFqNacG3GAsaTyDhG4h0qT', [ "response_type" => "code" ]);

        $this->assertStringStartsWith("https://" . $env['DOMAIN'], $url);
    }

    public function testLogoutLink() {
        $env = $this->getEnv();

        $api = new Auth0AuthApi($env['DOMAIN'], $env['GLOBAL_CLIENT_ID'], $env['GLOBAL_CLIENT_SECRET']);

        $this->assertSame("https://" . $env['DOMAIN'] . "/logout?", $api->get_logout_link());
        
        $this->assertSame("https://" . $env['DOMAIN'] . "/logout?returnTo=http%3A%2F%2Fexample.com", $api->get_logout_link("http://example.com"));

        $this->assertSame("https://" . $env['DOMAIN'] . "/logout?returnTo=http%3A%2F%2Fexample.com&client_id=" . $env['GLOBAL_CLIENT_ID'], $api->get_logout_link("http://example.com", $env['GLOBAL_CLIENT_ID']));
    }
}