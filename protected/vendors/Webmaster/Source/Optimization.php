<?php
class Optimization {
    private $domain;

    private $robotsTxt = null;

    private $final_url;

    public function __construct($domain, $final_url) {
        $this->domain = $domain;
        $this->final_url = $final_url;
    }

    public function getSitemap() {
        $robotsTxt = $this->getRobotsTxt();

        $pattern = "/Sitemap: ([^\r\n]*)/is";
        $sitemaps = array();
        preg_match_all($pattern, $robotsTxt, $matches);

        if(!empty($matches[1])) {
            foreach($matches[1] as $sitemap) {
                $sitemaps[] = $sitemap;
            }
        } else {
            $urlMap = array();
            $urlMap[] = "https://".$this->domain."/sitemap.xml";
            $urlMap[] = "http://".$this->domain."/sitemap.xml";
            $acceptedCodes = array(
                200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
                300, 301, 302, 303, 304, 305, 306, 307, 308
            );
            foreach($urlMap as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $ch = Utils::ch($ch);

                if(false === $ch) {
                    continue;
                }
                curl_exec($ch);
                if(curl_errno($ch)) {
                    continue;
                }
                $i = (array) curl_getinfo($ch);

                if(isset($i['http_code']) AND in_array((int) $i['http_code'], $acceptedCodes)) {
                    $sitemaps[] = Utils::v($i, "url", $url);
                }
            }
        }

        return array_unique($sitemaps);
    }

    public function getRobotsTxt() {
        if($this->robotsTxt !== null) {
            return $this->robotsTxt;
        }
        $url = "http://".$this->domain."/robots.txt";

        $ch = Utils::ch(curl_init($url));
        if(false === $ch) {
            $this->robotsTxt = false;
            return $this->robotsTxt;
        }

        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            $this->robotsTxt = false;
            return $this->robotsTxt;
        }
        $info = (array) curl_getinfo($ch);
        if(!isset($info['http_code']) AND ($info['http_code'] != '200')) {
            $this->robotsTxt = false;
        } else {
            $this->robotsTxt = $response;
        }
        return $this->robotsTxt;
    }

    public function hasRobotsTxt() {
        $r = $this->getRobotsTxt();
        return $r !== false;
    }

    public function hasGzipSupport() {
        $ch = curl_init($this->final_url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $ch = Utils::ch($ch, array(
            'Accept-Encoding:gzip',
        ));

        if(false === $ch) {
            return false;
        }

        $response = (string) curl_exec($ch);
        $info = (array) curl_getinfo($ch);

        $h_size = Utils::v($info, "header_size", 0);
        if(!$h_size) {
            return false;
        }
        $h = Utils::get_headers_from_curl_response(substr($response, 0, $h_size));
        return isset($h['content-encoding']) AND (mb_stripos($h['content-encoding'], 'gzip') !== false);
    }

}