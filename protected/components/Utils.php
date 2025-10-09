<?php

class Utils
{
	public static function shuffle_assoc( $list)
    {
        $keys = array_keys( $list );
        shuffle( $keys );
        $random = array();
        foreach ($keys as $key) {
            $random[ $key ] = $list[ $key ];
        }

        return $random;
     
    }

    public static function proportion( $big, $small)
    {

        return $big > 0 ? round( $small * 100 / $big, 2 ) : 0;
     
    }

    public static function createNestedDir( $path)
    {

        $dir = pathinfo( $path, PATHINFO_DIRNAME );
        if (is_dir( $dir )) {
            return true;
        } elseif (self::createNestedDir( $dir )) {
            if (mkdir( $dir )) {
                chmod( $dir, 0777 );
                return true;
        
            }
        }
        return false;
     
    }
    public static function createPdfFolder( $domain)
    {

        $pdf = self::getPdfFile( $domain );
        if ( ! file_exists( $pdf )) {
            self::createNestedDir( $pdf );
        }
        return $pdf;
     
    }
    public static function deletePdf( $domain)
    {

        foreach (Yii::app()->params['app.languages'] as $langId => $language) {
            $pdf = self::getPdfFile( $domain, $langId );
            if (file_exists( $pdf )) {
                unlink( $pdf );
             
            }
       
        }
        // Also delete the cached thumbnail
        WebsiteThumbnail::deleteThumbnail( $domain );
        return true;
     
    }

    public static function getPdfFile( $domain, $lang = null)
    {

        $root      = Yii::getPathofAlias( 'webroot' );
        $lang      = $lang ? $lang : Yii::app()->language;
        $subfolder = mb_substr( $domain, 0, 1 );
        $file      = $root . '/pdf/' . $lang . '/' . $subfolder . '/' . $domain . '.pdf';
        return $file;
     
    }

    public static function v( array $a, $k, $d = null)
    {

        return isset( $a[ $k ] ) ? $a[ $k ] : $d;
     
    }

    /*
    * thelonglongdomain.com -> thelong...ain.com
    */
    public static function cropDomain( $domain, $length = 24, $separator = '...')
    {

        if (mb_strlen( $domain ) < $length) {
            return $domain;
        }
        $sepLength    = mb_strlen( $separator );
        $backLen      = 6;
        $availableLen = $length - $sepLength - $backLen;
        // 20-3-6=11
        $firstPart = mb_substr( $domain, 0, $availableLen );
        $lastPart  = mb_substr( $domain, -$backLen );
        return $firstPart . $separator . $lastPart;
     
    }

    public static function curl( $url, array $headers = array(), $cookie = false)
    {

        $ch = curl_init( $url );
        if ($cookie) {
            $path   = Yii::getPathOfAlias( Yii::app()->params['param.cookie_cache'] );
            $cookie = $path . "/cookie_{$cookie}.txt";
      
        }
        $html = self::curl_exec( $ch, $headers, $cookie );
        curl_close( $ch );
        return $html;
    }

    public static function curl_exec( $ch, $headers = array(), $cookie = false, &$maxredirect = null)
    {

         return curl_exec( self::ch( $ch, $headers, $cookie, $maxredirect ) );
    }

    public static function ch( $ch, $headers = array(), $cookie = false, &$maxredirect = null)
    {

         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
         curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie );
         
        }

        if ( ! empty( $headers )) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
       
        }

        if (isset( $headers['user_agent'] )) {
            $user_agent = $headers['user_agent'];
             unset( $headers['user_agent'] );
        
        } else {
                   $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        
        }


        curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent);

        $mr = $maxredirect === null ? 5 : intval( $maxredirect );
        if (ini_get( 'open_basedir' ) == '' && ( ini_get( 'safe_mode' ) == 'Off' || ini_get( 'safe_mode' ) == '' )) {
               curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, $mr > 0 );
               curl_setopt( $ch, CURLOPT_MAXREDIRS, $mr );
         
        } else {
                  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
                $original_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
                  $parsed     = parse_url( $original_url );
            if ( ! $parsed) {
                return false;
           
            }
            $scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] : '';
            $host   = isset( $parsed['host'] ) ? $parsed['host'] : '';

            if ($mr > 0)
            {
                $newurl = $original_url;
                $rch    = curl_copy_handle( $ch );

                curl_setopt( $rch, CURLOPT_HEADER, true );
                curl_setopt( $rch, CURLOPT_NOBODY, true );
                curl_setopt( $rch, CURLOPT_FORBID_REUSE, false );
                curl_setopt( $rch, CURLOPT_RETURNTRANSFER, true );
                do {
                          curl_setopt( $rch, CURLOPT_URL, $newurl );
                         $header = curl_exec( $rch );
                    if (curl_errno( $rch )) {
                        $code = 0;
                  
                    } else {
                        $code = curl_getinfo( $rch, CURLINFO_HTTP_CODE );
                        if (in_array( $code, array( 301, 302, 307, 308 ) )) {
                            preg_match( '/Location:(.*?)\n/i', $header, $matches );
                            $newurl = trim( array_pop( $matches) );

                            if (! $parsed = parse_url($newurl)) {
                                return false;
                           
                            }

                            if (! isset($parsed['scheme'])) {
                                $parsed['scheme'] = $scheme;
                            
                            } else {
                                $scheme = $parsed['scheme'];
                            
                            }

                            if (! isset($parsed['host'])) {
                                $parsed['host'] = $host;
                            
                            } else {
                                $host = $parsed['host'];
                            
                            }
                            $newurl = self::unparse_http_url($parsed);
                        
                        } else {
                            $code = 0;
                      
                        }
                   
                    }
               
                } while ($code && --$mr);
                curl_close($rch);

                if (! $mr)
                {
                    if ($maxredirect === null) {
                        return false;
                   
                    } else {
                                        $maxredirect = 0;
                    }

                    return false;
               
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
           
            }
       
        }
        return $ch;
     
    }

    public static function unparse_http_url( array $parsed)
    {

        if (! isset($parsed['host'])) {
            return false;
       
        }
        $url = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'http://';
        if (isset($parsed['user'])) {
            $url .= $parsed['user'];
            if (isset($parsed['pass'])) {
                 $url .= ':' . $parsed['pass'];
           
            }
             $url .= '@' . $parsed['host'];
       
        } else {
                   $url .= $parsed['host'];
        
        }

        if (isset($parsed['port'])) {
                 $url .= ':' . $parsed['port'];
       
        }

        if (isset($parsed['path'])) {
                 $url .= $parsed['path'];
        
        }
        if (isset($parsed['query'])) {
               $url .= '?' . $parsed['query'];
      
        }
        if (isset($parsed['fragment'])) {
                $url .= '#' . $parsed['fragment'];
       
        }
        return $url;
    
    }

    public static function curl_get_final_url( $curl_info, $default)
    {

        if (false === $curl_info) {
            return $default;
        
        }
        if (! empty($curl_info['redirect_url'])) {
             return $curl_info['redirect_url'];
      
        }
        return self::v($curl_info, 'url', $default);
    }

    public static function url_get_scheme_host( $url, $default)
    {

         $parsed = parse_url($url);
        if (false === $parsed) {
            return $default;
        
        }
        if (! isset($parsed['scheme'], $parsed['host'])) {
             return $default;
        
        }
        return $parsed['scheme'] . '://' . $parsed['host'] . '/';
    
    }

    public static function get_headers_from_curl_response( $response)
    {
         $headers    = array();
        $header_text = substr( $response, 0, strpos( $response, "\r\n\r\n" ) );
        foreach (explode( "\r\n", $header_text ) as $i => $line) {
            if ($i === 0) {
                $headers['status']    = $line;
                $data                 = explode( ' ', $line );
                $headers['http_code'] = isset( $data[1] ) ? $data[1] : null;
            } else {
                list ($key, $value)          = explode( ': ', $line );
                $headers[ strtolower( $key ) ] = $value;
            }
        }
         return $headers;
    }

    public static function isPsiActive( $k, $item)
    {
         $key        = "psi.{$k}";
         $configItem = Yii::app()->params[ $key ];
         return is_array( $configItem ) ? ( empty( $configItem ) or in_array( $item, $configItem ) ) : $configItem == $item;
    }

    public static function starts_with( $haystack, $needle)
    {
         return (string) $needle !== '' && strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
    }

    public static function getLocalConfigIfExists( $config_name)
    {
         $dir       = Yii::getPathOfAlias( 'application.config' );
        $conf_local = $dir . '/' . $config_name . '_local.php';
        $conf_prod  = $dir . '/' . $config_name . '.php';
         return file_exists( $conf_local ) ? require $conf_local : require $conf_prod;
    }

    public static function html_decode( $str)
    {
         return html_entity_decode(  (string) $str, ENT_QUOTES, 'UTF-8' );
    }

    public static function is_allowed_action()
    {
         $controllers = array(
          'site' => array(),
         );
         if ( ! isset( $controllers[ Yii::app()->controller->id ] )) {
             return true;
         }

         $actions = $controllers[ Yii::app()->controller->id ];
         if ( ! is_array( $actions )) {
             return false;
         }

         return ! in_array( Yii::app()->controller->action->id, $actions );
    }
}
