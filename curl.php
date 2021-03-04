<?php
session_start();
include('./functions.php');

/*
    Store variables in session.
*/
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'refresh') {
        $token = queryServer('token', $_SESSION['refresh_token'])['body'];
        $_SESSION['access_token'] = $token['access_token'];
        $_SESSION['refresh_token'] = $token['refresh_token'];
        $_SESSION['ttl'] = $token['expires_in'];
        header("Location: ./display.php");
        exit();
    } elseif ($_GET['type'] === 'request') {
    }
}
if (!isset($_SESSION['input']) && isset($_POST)) {
    $_SESSION['input'] = $_POST;
} elseif (empty(array_diff_key($_SESSION['input'], $_POST))) {
    if ($_SESSION['input'] !== $_POST) {
        $_SESSION['input'] = $_POST;
    }
}

$input = $_SESSION['input'];

if (verifyURL('https://' . $input['host_name'] . $input['route_path'])) {
    if ($input['grant_type'] === 'authorization_code' or $input['grant_type'] === 'implicit') {
        $authorization = authorize();
        if (array_key_exists('error', $authorization)) {
            $_SESSION['error'] = true;
            $_SESSION['error_response'] = $authorization;
            header("Location: ./display.php");
            exit();
        }
        $_SESSION['authroized_url'] = $authorization['authroized_url'];
        $_SESSION['authorize_code'] = $authorization['authorize_code'];
        if ($input['grant_type'] === 'implicit') {
            $_SESSION['access_token'] = $authorization['authorize_code'];
        } else {
            $request=queryServer('token', $authorization['authorize_code']);
            if ($request['status'] !== 200) {
                $_SESSION['error'] = true;
                $message=$request['body'];
                if(array_key_exists('message',$request['body'])){
                    $message = array(
                        'error' => '',
                        'error_description' => $request['body']['message']
                    );
                }
                $_SESSION['error_response'] = $message;
                header("Location: ./display.php");
                exit();
            }
            $token = $request['body'];
            $_SESSION['access_token'] = $token['access_token'];
            $_SESSION['refresh_token'] = $token['refresh_token'];
            $_SESSION['ttl'] = $token['expires_in'];
        }
    } else {
        $request = queryServer('token');
        $token = $request['body'];
        if ($request['status'] !== 200) {
            $_SESSION['error'] = true;
            $message=$request['body'];
            if(array_key_exists('message',$request['body'])){
                $message = array(
                    'error' => '',
                    'error_description' => $request['body']['message']
                );
            }
            $_SESSION['error_response'] = $message;
            header("Location: ./display.php");
            exit();
        }
        $_SESSION['access_token'] = $token['access_token'];
        if ($input['grant_type'] === 'password') {
            $_SESSION['refresh_token'] = $token['refresh_token'];
        }
        $_SESSION['ttl'] = $token['expires_in'];
    }
    header("Location: ./display.php");
    exit();
} else {
    $message = array(
        'error' => 'connection_error',
        'error_description' => 'Please check your connection, make sure you can access <b>https://' . $input['host_name'] . $input['route_path'] . '</b>'
    );
    $_SESSION['error'] = true;
    $_SESSION['error_response'] = $message;
    header("Location: ./display.php");
    exit();
}

function queryServer($request, $token = '')
{
    $input = $_SESSION['input'];
    $auth_suffix = '/oauth2/authorize';
    $token_suffix = '/oauth2/token';
    $url = 'https://' . $input['host_name'] . $input['route_path'];
    $grant_type = $input['grant_type'];
    $response = [];
    $options['client_id'] = $input['client_id'];

    if ($request === 'auth') {
        $url .= $auth_suffix;
        if (!isset($_SESSION['code_verifier'])) {
            $random = bin2hex(openssl_random_pseudo_bytes(50));
            $code_verifier = base64url_encode(pack('H*', $random));
            $code_challenge = base64url_encode(pack('H*', hash('sha256', $code_verifier)));
            $_SESSION['code_challenge'] = $code_challenge;
            $_SESSION['code_verifier'] = $code_verifier;
            $options['code_challenge'] = $code_challenge;
        } else {
            $options['code_challenge'] = $_SESSION['code_challenge'];
        }
        $options['code_challenge_method'] = 'S256';
        $response_type = 'token';
        if ($grant_type === 'authorization_code') {
            $response_type = 'code';
        }
        $options['provision_key'] = $input['provision_key'];
        $options['response_type'] = $response_type;
        $options['scope'] = $input['scope'];
        $options['authenticated_userid'] = $input['authenticated_userid'];
        $response = post($url, json_encode($options));
    } else {
        $url .= $token_suffix;
        $options['client_secret'] = $input['client_secret'];
        $options['grant_type'] = $grant_type;
        if (isset($_GET['type']) and $_GET['type'] === 'refresh') {
            $options['refresh_token'] = $token;
            $options['grant_type'] = 'refresh_token';
        } elseif ($grant_type === 'authorization_code') {
            $options['code'] = $token;
            $options['code_verifier'] = $_SESSION['code_verifier'];
        } else {
            $options['provision_key'] = $input['provision_key'];
            if ($grant_type === 'password') {
                $options['authenticated_userid'] = $input['authenticated_userid'];
            }
        }
        $response = post($url, json_encode($options));
    }
    if (array_key_exists('error', $response)) {
        $_SESSION['error'] = true;
        $_SESSION['error_response'] = $response['body'];
        header("Location: ./display.php");
        exit();
    }
    return $response;
}

function authorize()
{
    $request = queryServer('auth');
    if($request['status'] !== 200){
        if(array_key_exists('redirect_uri',$request['body'])){
            $parsed_url = parse_url($request['body']['redirect_uri']);
            $url_components = explode('=', $parsed_url['query']);
            $desc = explode('&', $url_components[1]);
            $error_message = array(
                'error' => $desc[0],
                'error_description' => urldecode($url_components[2])
            );
            $_SESSION['error'] = true;
            $_SESSION['error_response'] = $error_message;
            header("Location: ./display.php");
            exit();
        }
        $message=$request['body'];
        if(array_key_exists('message',$request['body'])){
            $message = array(
                'error' => '',
                'error_description' => $request['body']['message']
            );
        }
        $_SESSION['error'] = true;
        $_SESSION['error_response'] = $message;
        header("Location: ./display.php");
        exit();
    }
    $redirect_uri = $request['body']['redirect_uri'];
    $authorize_code = '';
    $parsed_url = parse_url($redirect_uri);

    if (array_key_exists('fragment', $parsed_url)) {
        $url_components = explode('=', $parsed_url['fragment']);
        $authorize_code = explode('&', $url_components[1])[0];
        $ttl = explode('&', $url_components[2])[0];
        $_SESSION['ttl'] = $ttl;
    } else {
        $url_components = explode('=', $parsed_url['query']);
        $authorize_code = $url_components[1];
    }
    return array(
        'authroized_url' => $redirect_uri,
        'authorize_code' => $authorize_code
    );
}
