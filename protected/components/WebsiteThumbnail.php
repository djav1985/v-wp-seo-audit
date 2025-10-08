<?php
class WebsiteThumbnail
{
    private static $imgUrl = '{{Protocol}}://{{Endpoint}}.pagepeeker.com/v2/thumbs.php';
    private static $resetUrl = '{{Protocol}}://{{Endpoint}}.pagepeeker.com/v2/thumbs.php';
    private static $pollUrl = '{{Protocol}}://{{Endpoint}}.pagepeeker.com/v2/thumbs_ready.php';

    private static $http = array("free", "free2", "free3", "free4");
    private static $https = array("api");

    private static function isSecuredCall()
    {
        return Yii::app()->request->getIsSecureConnection();
    }

    private static function getApiKey()
    {
        return Yii::app()->params['pagepeeker.api_key'];
    }

    private static function getSingleCallEndpoint()
    {
        return self::isSecuredCall() ? "api" : "free";
    }

    private static function getEndpointServers()
    {
        return self::isSecuredCall() ? self::$https : self::$http;
    }

    public static function getResetUrl(array $params = array())
    {
        $strtr = array();
        $strtr['{{Endpoint}}'] = self::getSingleCallEndpoint();
        $params['refresh'] = "1";
        if ($apiKey = self::getApiKey()) {
            $params['code'] = $apiKey;
        }
        return self::prepareUrl(self::$resetUrl, $strtr, $params);
    }

    public static function getPollUrl(array $params = array())
    {
        $strtr = array();
        $strtr['{{Endpoint}}'] = self::getSingleCallEndpoint();
        if ($apiKey = self::getApiKey()) {
            $params['code'] = $apiKey;
        }
        return self::prepareUrl(self::$pollUrl, $strtr, $params);
    }

    public static function getThumbData(array $params = array(), $num = 0)
    {
        $servers = self::getEndpointServers();
        if (!isset($servers[$num])) {
            throw new InvalidArgumentException("Invalid thumbnail server");
        }
        $strtr = array();
        $strtr['{{Endpoint}}'] = $servers[$num];
        if ($apiKey = self::getApiKey()) {
            $params['code'] = $apiKey;
        }
        return json_encode(array(
            'thumb' => self::prepareUrl(self::$imgUrl, $strtr, $params),
            'size' => isset($params['size']) ? $params['size'] : "m",
            'url' => $params['url']
        ));
    }

    public static function getOgImage(array $params = array())
    {
        $strtr = array();
        $strtr['{{Endpoint}}'] = self::$http[0];
        $strtr['{{Protocol}}'] = 'http';
        return self::prepareUrl(self::$imgUrl, $strtr, $params);
    }

    public static function prepareUrl($url, array $strtr = array(), array $params = array())
    {
        if (!isset($params['url'])) {
            throw new InvalidArgumentException("Url param is not specified");
        }
        $default = array();
        if (!isset($strtr['{{Protocol}}'])) {
            $default['{{Protocol}}'] = self::isSecuredCall() ? 'https' : 'http';
        }
        $strtr = array_merge($default, $strtr);
        $url = strtr($url, $strtr);
        if (!isset($params['size'])) {
            $params['size'] = 'm';
        }
        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }
        return $url;
    }

    public static function thumbnailStack($websites, array $params = array())
    {
        $count = count(self::getEndpointServers());
        $counter = 0;
        $stack = array();
        foreach ($websites as $website) {
            $index = $counter % $count;
            $params['url'] = $website['domain'];
            $stack[$website['id']] = self::getThumbData($params, $index);
            $counter++;
        }
        return $stack;
    }
}
