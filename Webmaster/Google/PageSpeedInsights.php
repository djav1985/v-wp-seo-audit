<?php
class PageSpeedInsights {
    private $domain;
    private $api_key;
    private $locale = "en_US";

    const API_URL = "https://www.googleapis.com/pagespeedonline/v2/runPagespeed";

    public function __construct($domain, $api_key) {
        $this->domain = $domain;
        $this->api_key = $api_key;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    private function getResultForStrategy($strategy) {
        $params = array(
            "url"=>"http://".$this->domain,
            "locale"=>$this->locale,
            "strategy"=>$strategy,
            "screenshot"=>"true",
            "key"=>$this->api_key,
        );
        if(empty($this->api_key)) {
            unset($params['key']);
        }
        $query_params = http_build_query($params, null, "&");
        $url = self::API_URL."?".$query_params;
        return @json_decode(V_WPSA_Utils::curl($url), true);
    }

    public function getResults() {
        $strategies = array(
            "mobile", "desktop"
        );
        $results = array();
        foreach($strategies as $strategy) {
            if($r = $this->getResultForStrategy($strategy)) {
                if(!isset($r['error'])) {
                    $results[$strategy] = $this->formatResult($r);
                }
            }
        }
        return $results;
    }


    private function getRuleGroups ($ruleGroups, $advice) {
        $groups = array();
        foreach($advice['groups'] as $group) {
            if(in_array($group, $ruleGroups)) {
                $groups[] = $group;
            }
        }
        return $groups;
    }

    private function formatResult($result) {
        $formatted = array();
        $advices = $result['formattedResults']['ruleResults'];



        $ruleGroups = array_keys($result['ruleGroups']);
        foreach($ruleGroups as $ruleGroup) {
            $formatted[$ruleGroup] = array();
            $formatted[$ruleGroup]["important"] = array();
            $formatted[$ruleGroup]["warning"] = array();
            $formatted[$ruleGroup]["success"] = array();
        }

        foreach($advices as $id=>$advice) {
            $impactGroup = $this->getRuleImpactGroup($advice['ruleImpact']);
            $groupsInAdvice = $this->getRuleGroups($ruleGroups, $advice);

            // Check if isset summary
            if(isset($advice['summary'])) {
                $advice['summary'] = $this->formatArgs($advice['summary']);
            }

            // // Check if isset urlBlocks
            if(isset($advice['urlBlocks'])) {
                $advice['urlBlocks'] = $this->formatUrlBlocks($advice['urlBlocks']);
            }

            foreach($groupsInAdvice as $group) {
                $formatted[$group][$impactGroup][$id] = $advice;
            }
        }
        $result['formattedResults']['ruleResults'] = $formatted;
        return $result;
    }

    private function getRuleImpactGroup($ruleImpact) {
        if($ruleImpact == 0) {
            return "success";
        } elseif($ruleImpact < 10) {
            return "warning";
        } else {
            return "important";
        }
    }

    private function formatUrlBlocks($urlBlocks) {
        foreach($urlBlocks as $id=>$urlBlock) {
            $urlBlocks[$id] = $this->formatArgs($urlBlock['header']);
            if(isset($urlBlock['urls'])) {
                foreach($urlBlock['urls'] as $i=>$url) {
                    $urlBlocks[$id]['urls'][$i] = $this->formatArgs($url['result']);
                }
            }
        }
        return $urlBlocks;
    }

    private function formatArgs($data, $key = 'format') {
        $args = isset($data['args']) ? $data['args'] : array();
        foreach($args as $arg) {
            $data[$key] = $this->replaceArg($data[$key], $arg);
        }
        return $data;
    }

    private function replaceArg($str, array $arg = array()) {
        if(strtoupper($arg['type']) == 'HYPERLINK') {
            $openTag = "#{{BEGIN_LINK}}#i";
            $closeTag = "#{{END_LINK}}#i";
            $replaceOpen = '<a href="'.$arg['value'].'" target="_blank" rel="nofollow">';
            $replaceClose = '</a>';

            $str = preg_replace($openTag, $replaceOpen, $str, 1);
            $str = preg_replace($closeTag, $replaceClose, $str, 1);
            return $str;
        } elseif(strtoupper($arg['type']) == 'STRING_LITERAL') {
            return str_replace("{{".$arg['key']."}}", '<code>'.htmlspecialchars($arg['value']).'</code>', $str);
        } else {
            return str_replace("{{".$arg['key']."}}", $arg['value'], $str);
        }
    }

    public static function getClassFromScore($mark) {
        if($mark < 50) {
            return "important";
        } elseif($mark < 90) {
            return "warning";
        } else {
            return "success";
        }
    }

    public static function getIconNameByImpact($impact) {
        switch($impact) {
            case "success":
                return "check";
            break;
            default:
                return "exclamation";
            break;
        }
    }

    public static function decodeData($data) {
        $data = str_replace('_', '/', $data);
        $data = str_replace('-', '+', $data);
        return $data;
    }
}