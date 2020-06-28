<?php

class CardmarketConnection
{
    private $method = null;
    private $xml = null;
    private $url = null;
    private $nonce = null;
    private $timestamp = null;
    private $signatureMethod = 'HMAC-SHA1';
    private $version = '1.0'; //Version of oauth
    private $appToken = null;
    private $appSecret = null;
    private $accessToken = null;
    private $accessSecret = null;
    private $header = null;

    function __construct($method, $url, $xml)
    {
        $this->method = $method;
        $this->url = $url;
        $this->xml = $xml;
        $this->nonce = strtolower(substr(base64_encode(sha1(mt_rand())), 0, 13));
        $this->timestamp = time();
        include 'Credentials.php';
        $credentials = new Credentials();
        $this->appToken = $credentials->appToken;
        $this->appSecret = $credentials->appSecret;
        $this->accessToken = $credentials->accessToken;
        $this->accessSecret = $credentials->accessSecret;
    }

    function setMethod($method)
    {
        $this->method = $method;
    }

    function setXML($xml)
    {
        $this->xml = $xml;
    }

    function setURL($url)
    {
        $this->url = $url;
    }

    private function initRequest()
    {
        $params = array(
            'realm' => $this->url,
            'oauth_consumer_key' => $this->appToken,
            'oauth_token' => $this->accessToken,
            'oauth_nonce' => $this->nonce,
            'oauth_timestamp' => $this->timestamp,
            'oauth_signature_method' => $this->signatureMethod,
            'oauth_version' => $this->version,
        );

        $baseString = strtoupper($this->method) . "&";
        $baseString .= rawurlencode($this->url) . "&";

        $encodedParams = array();
        foreach ($params as $key => $value) {
            if ("realm" != $key) {
                $encodedParams[rawurlencode($key)] = rawurlencode($value);
            }
        }
        ksort($encodedParams);

        $values = array();
        foreach ($encodedParams as $key => $value) {
            $values[] = $key . "=" . $value;
        }
        $paramsString = rawurlencode(implode("&", $values));
        $baseString .= $paramsString;

        $signatureKey = rawurlencode($this->appSecret) . "&" . rawurlencode($this->accessSecret);

        $rawSignature = hash_hmac("sha1", $baseString, $signatureKey, true);
        $oAuthSignature = base64_encode($rawSignature);

        $params['oauth_signature'] = $oAuthSignature;

        $this->header = "Authorization: OAuth ";
        $headerParams = array();
        foreach ($params as $key => $value) {
            $headerParams[] = $key . "=\"" . $value . "\"";
        }
        $this->header .= implode(", ", $headerParams);
    }

    function execHTTPRequest()
    {
        $this->initRequest();
        $curlHandle = curl_init();

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $this->url);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array($this->header));
        if ($this->xml != null)
        {
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->xml);
        }

        $content = curl_exec($curlHandle);
        $info = curl_getinfo($curlHandle);


        echo $content;
        echo 'took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
        echo 'got ', $info['http_code'], ' as response ', $info['url'], "\n";
        curl_close($curlHandle);

    }
}


/**
 *
 *
 * $curlHandle = curl_init();
 *
 * curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
 * curl_setopt($curlHandle, CURLOPT_URL, $url);
 * curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array($header));
 * curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $xml);
 *
 *
 * $content = curl_exec($curlHandle);
 * $info = curl_getinfo($curlHandle);
 * echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
 * echo 'got ', $info['http_code'], ' as response ', $info['url'], "\n";
 * echo $content;
 * curl_close($curlHandle);
 *
 * $decoded = simplexml_load_string($content);
 * $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
 * <request>
 * <article>
 * <idArticle>833736899</idArticle>
 * <idLanguage>1</idLanguage>
 * <comments>Edited through the API</comments>
 * <price>400</price>
 * <condition>EX</condition>
 * <isFoil>true</isFoil>
 * <isSigned>false</isSigned>
 * <isPlayset>false</isPlayset>
 * </article>
 * </request>";
 *
 * }
 * ?>
 */

?>