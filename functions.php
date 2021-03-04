<?php
/*
    Validate url verification

*/
function verifyURL($url)
{
    stream_context_set_default([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    if (@get_headers($url, 1) !== FALSE) {
        $headers = get_headers($url, 1);
        $status = get_http_response_code($url);
        if (strpos($headers['Server'], 'kong') === true or $status < 500) {
            return true;
        }
        return false;
    } else {
        return false;
    }
}

function get_http_response_code($theURL)
{
    $headers = get_headers($theURL);
    return substr($headers[0], 9, 3);
}

function request($method, $URL, $data = null)
{


    try {
        $headers = array('Accept: application/json', 'Content-Type: application/json');
        if ($method == 'GET') {
            $headers = array('Content-Type: application/json');
        }
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $URL);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, true);
        if (isset($_SESSION['input']['dev_mode']) and $_SESSION['input']['dev_mode'] === 'on') {
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        }
    
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                break;
        }
        $response = curl_exec($handle);
    
        if (curl_errno($handle)) {
            throw new \RuntimeException('Curl: ' . curl_error($handle));
        }
    
        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $header = trim(substr($response, 0, $headerSize));
        $body = substr($response, $headerSize);
        $jsonbody = json_decode($body, true);
        $jsonresponse = array(
            'request' => $method . ' ' . $URL,
            'header' => $header,
            'status' => $statusCode,
            'body' => $jsonbody === null ? $body : $jsonbody,
        );
    
        return $jsonresponse;
    } catch(Exception $e) {
        // trigger_error(sprintf('Curl failed with error #%d: %s',$e->getCode(), $e->getMessage()),E_USER_ERROR);
        $message = array(
            'error' => '',
            'error_description' => $e->getMessage()
        );
        $_SESSION['error']=true;
        $_SESSION['error_response'] = $message;
        header("Location: ./display.php");
        exit();
    }
}


function get($url)
{
    return request('GET', $url);
}

function post($url, $data = null)
{
    return request('POST', $url, $data);
}

function base64url_encode($plainText)
{
    $base64 = base64_encode($plainText);
    $base64 = trim($base64, "=");
    $base64url = strtr($base64, '+/', '-_');
    return ($base64url);
}
