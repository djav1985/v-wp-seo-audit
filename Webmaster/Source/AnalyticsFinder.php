<?php
class AnalyticsFinder
{
    private $html;
    private static $analytics = array(
        'googleanalytics' => 'Google Analytics',
        'liveinternet' => 'LiveInternet',
        'clicky' => 'Clicky',
        'quantcast' => 'Quantcast',
        'piwik' => 'PIWIK',
    );

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function findAll()
    {
        $analytics = array_keys(self::$analytics);
        $has = array();
        foreach ($analytics as $a) {
            $method = "has" . $a;
            if ($this->$method()) {
                $has[] = $a;
            }
        }
        return $has;
    }

    public static function getProviderName($id)
    {
        return isset(self::$analytics[$id]) ? self::$analytics[$id] : "Unknown";
    }

    public function hasGoogleAnalytics()
    {
        // UA_ID Regex
        $ua_regex = "/UA-[0-9]{5,}-[0-9]{1,}/";

        // Old version
        preg_match_all($ua_regex, $this->html, $ua_id);
        if (count($ua_id[0]) > 0) {
            return true;
        }

        // New version
        $ga_script_regex = "/googletagmanager\.com\/gtag\/js\?id=G-[a-z0-9]{1,32}/is";
        preg_match_all($ga_script_regex, $this->html, $g_id);
        if (count($g_id[0]) > 0) {
            return true;
        }

        // Additional script detection
        $additional_scripts = [
            'gtagv4.js',
            'ga.js',
            'gtag.js',
            'analytics.js',
            'gtm.js',
            'analytics-minimal-v4.js'
        ];
        foreach ($additional_scripts as $script) {
            if (stripos($this->html, $script) !== false) {
                return true;
            }
        }

        // Detect GTM URLs with or without an ID
        $gtm_regex = "/googletagmanager\.com\/gtm\.js(\?id=GTM-[a-zA-Z0-9_-]+)?/i";
        if (preg_match($gtm_regex, $this->html)) {
            return true;
        }

        return false;
    }

    //  new Image().src = "//counter.yadro.ru/hit?r" + escape(document.referrer) + ((typeof(screen)=="undefined")?"" : ";s"+screen.width+"*"+screen.height+"*" + (screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ";u"+escape(document.URL) +  ";" +Math.random();
    public function hasLiveInternet()
    {
        if (!$scripts = $this->getAllScripts()) {
            return false;
        }
        $counter = 'counter.yadro.ru';
        $total = count($scripts);
        for ($i = 0; $i < $total; $i++) {
            if (stripos($scripts[$i], $counter)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Default
     *
     * <a title="Google Analytics Alternative" href="http://clicky.com/100856226"><img alt="Google Analytics Alternative" src="//static.getclicky.com/media/links/badge.gif" border="0" /></a>
    <script src="//static.getclicky.com/js" type="text/javascript"></script>
    <script type="text/javascript">try{ clicky.init(100856226); }catch(e){}</script>
    <noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100856226ns.gif" /></p></noscript>
     *
     *
     * Async
     *
     * <a title="Google Analytics Alternative" href="http://clicky.com/100856226"><img alt="Google Analytics Alternative" src="//static.getclicky.com/media/links/badge.gif" border="0" /></a>
    <script type="text/javascript">
    var clicky_site_ids = clicky_site_ids || [];
    clicky_site_ids.push(100856226);
    (function() {
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = '//static.getclicky.com/js';
    ( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s );
    })();
    </script>
    <noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100856226ns.gif" /></p></noscript>
     *
     * Non Javascript
     *
     * <a title="Google Analytics Alternative" href="http://clicky.com/100856226"><img alt="Google Analytics Alternative" src="//static.getclicky.com/media/links/badge.gif" border="0" /></a>
    <img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100856226ns.gif" />
     */
    public function hasClicky()
    {
        // Sync javascript

        // Search for image tracking
        $img_regex = '#getclicky\.com\/[\d]+ns\.gif#i';

        // Search for static initialization
        $scripts = $this->getAllScripts();
        $total = $scripts ? count($scripts) : 0;
        $init_regex = "/clicky\.init\([\d]+\)/i";

        $js_flag = false;
        if ($scripts) {
            for ($i = 0; $i < $total; $i++) {
                // Sync code
                if (preg_match($init_regex, $scripts[$i])) {
                    $static_init = true;
                    break;
                }
                // Async code
                if (stripos($scripts[$i], "static.getclicky.com/js")) {
                    $js_flag = true;
                    break;
                }
            }
        }

        return $js_flag or (preg_match($img_regex, $this->html));
    }


    /**
     *
    <!-- Quantcast Tag -->
    <script type="text/javascript">
    var _qevents = _qevents || [];

    (function() {
    var elem = document.createElement('script');
    elem.src = (document.location.protocol == "https:" ? "https://secure" : "http://edge") + ".quantserve.com/quant.js";
    elem.async = true;
    elem.type = "text/javascript";
    var scpt = document.getElementsByTagName('script')[0];
    scpt.parentNode.insertBefore(elem, scpt);
    })();

    _qevents.push({
    qacct:"p-gfPQEGpDfx_4E"
    });
    </script>

    <noscript>
    <div style="display:none;">
    <img src="//pixel.quantserve.com/pixel/p-gfPQEGpDfx_4E.gif" border="0" height="1" width="1" alt="Quantcast"/>
    </div>
    </noscript>
    <!-- End Quantcast tag -->
     */
    public function hasQuantcast()
    {
        $js_file = "quantserve.com/quant.js";
        $no_script = "#pixel\.quantserve\.com\/pixel\/(.*)\.gif#i";

        // Search for static initialization
        $scripts = $this->getAllScripts();
        $total = $scripts ? count($scripts) : 0;

        $script_flag = false;
        for ($i = 0; $i < $total; $i++) {
            if (stripos($scripts[$i], $js_file)) {
                $script_flag = true;
                break;
            }
        }
        return $script_flag or preg_match($no_script, $this->html);
    }


    /*
     	<!-- Piwik -->
        <script type="text/javascript">
          var _paq = _paq || [];
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          (function() {
            var u="//www.smkonzept-server.de/stats/";
            _paq.push(['setTrackerUrl', u+'piwik.php']);
            _paq.push(['setSiteId', 1]);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
          })();
        </script>
        <noscript><p><img src="//www.smkonzept-server.de/stats/piwik.php?idsite=1" style="border:0;" alt="piwik" /></p></noscript>
        <!-- End Piwik Code -->

        <!-- Piwik -->
        <script type="text/javascript">
          var _paq = _paq || [];
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          (function() {
            var u="//forza020.piwikpro.com/";
            _paq.push(['setTrackerUrl', u+'piwik.php']);
            _paq.push(['setSiteId', 3]);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
          })();
        </script>
        <noscript><p><img src="//forza020.piwikpro.com/piwik.php?idsite=3" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->
     */
    public function hasPiwik()
    {
        $scripts = $this->getAllScripts();
        $no_scripts = $this->getAllScripts(true);
        $php_file = "piwik.php";
        foreach ($scripts as $script) {
            if (stripos($script, $php_file)) {
                return true;
            }
        }
        foreach ($no_scripts as $no_script) {
            if (stripos($no_script, $php_file)) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param $no_script boolean
     * @return mixed array|bool
     */
    private function getAllScripts($no_script = false)
    {
        // Script Regex
        $pref = $no_script ? "no" : null;

        $script_regex = "/<{$pref}script\b[^>]*>([\s\S]*?)<\/{$pref}script>/i";

        preg_match_all($script_regex, $this->html, $inside_script);

        return !empty($inside_script[0]) ? $inside_script[0] : array();
    }
}
