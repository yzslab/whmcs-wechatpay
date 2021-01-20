<?php


namespace YunInternet\WHMCS\WeChatPay;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayAPIException;
use YunInternet\WHMCS\WeChatPay\Exceptions\WeChatPayException;

class WeChatPayAPIv3
{
    const URL_PREFIX = "https://api.mch.weixin.qq.com/v3/";

    private $client;

    /**
     * WeChatPayAPIv3 constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function nativePay(array $params): string
    {
        return $this->sendJSONRequest("pay/transactions/native", $params)["code_url"];
    }

    public function h5Pay(array $params): string
    {
        return $this->sendJSONRequest("pay/transactions/h5", $params)["code_url"];
    }

    public function getCertificate(&$resp = null): array
    {
        return $this->sendGetRequest("certificates", $resp);
    }

    public function getParamBuilder(): WeChatPayParamBuilder
    {
        return new WeChatPayParamBuilder();
    }

    private function sendJSONRequest($uri, $params, &$resp = null): array
    {
        $resp = $this->sendRequest("POST", $uri,  [
            "json" => $params,
            "headers" => ["Accept" => "application/json"]
        ]);
        return json_decode($resp->getBody(), true);
    }

    private function sendGetRequest($uri, &$resp = null): array
    {
        $resp = $this->sendRequest("GET", $uri, [
            "headers" => ["Accept" => "application/json"]
        ]);
        return json_decode($resp->getBody(), true);
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     * @return ResponseInterface
     * @throws WeChatPayAPIException
     * @throws WeChatPayException
     */
    private function sendRequest($method, $uri, $options): ResponseInterface
    {
        try {
            // 设置超时时间
            if (array_key_exists("connect_timeout", $options) === false) {
                $options["connect_timeout"] = 10;
            }
            return $this->client->request($method, self::URL_PREFIX . $uri, $options);
        } catch (ClientException $e) {
            if ($e->hasResponse() && ($decodedResponse = json_decode($e->getResponse()->getBody()))) {
                throw new WeChatPayAPIException($decodedResponse->message, $decodedResponse->code);
            }
            throw new WeChatPayAPIException("unknown error");
        } catch (GuzzleException $e) {
            throw new WeChatPayException("request error: " . $e->getMessage(), 0, $e);
        }
    }
}