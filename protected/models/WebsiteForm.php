<?php

Yii::import('application.vendors.Webmaster.Utils.IDN');

class WebsiteForm extends CFormModel
{
	public $domain;
    public $idn;
    // президент.рф (IDN)
    public $ip;

    public function rules()     {
        return array(
        array(
        'domain',
        'filter',
        'filter' => array( $this, 'trimDomain' ),
        ),
        array(
        'domain',
        'filter',
        'filter' => array( $this, 'punycode' ),
        ),
        array( 'domain', 'required' ),
        array(
        'domain',
        'match',
        'pattern'     => '#^[a-z\d-]{1,62}\.[a-z\d-]{1,62}(.[a-z\d-]{1,62})*$#i',
        'skipOnError' => true,
        ),
        array( 'domain', 'bannedWebsites' ),
        array( 'domain', 'isReachable' ),
        array( 'domain', 'tryToAnalyse' ),
        );
     
    }

    public function attributeLabels()
    {

        return array( 'domain' => Yii::t( 'app', 'Domain' ) );
     
    }

    public function punycode( $domain )
    {

        $idn          = new IDN();
        $this->domain = $idn->encode( $domain );
        $this->idn    = $domain;
        return $this->domain;
    }

    public function bannedWebsites()
    {

        if (  ! $this->hasErrors() ) {
            $banned = Utils::getLocalConfigIfExists( 'domain_restriction' );
            foreach ( $banned as $pattern ) {
                if ( preg_match( "#{$pattern}#i", $this->idn ) ) {
                    $this->addError( 'domain', Yii::t( 'app', 'Error Code 103' ) );
                 
                }
           
            }
         
        }
     
    }

    public function trimDomain( $domain )
    {

        $domain = trim( $domain );
        $domain = trim( $domain, '/' );
        $domain = mb_strtolower( $domain );
        $domain = preg_replace('#^(https?://)#i', '', $domain);
        $domain = preg_replace('#^www\.#i', '', $domain);
        return $domain;
     
    }

    public function isReachable()
    {

        if (! $this->hasErrors()) {
            $this->ip = gethostbyname($this->domain);
            $long     = ip2long($this->ip);
            if ($long == -1 or $long === false) {
                $this->addError('domain', Yii::t('app', 'Could not reach host: {Host}', array( '{Host}' => $this->domain )));
             
            }
         
        }
     
    }

    public function tryToAnalyse()
    {

        if (! $this->hasErrors()) {
            // Remove "www" from domain
            $this->domain = str_replace('www.', '', $this->domain);
            // Get command instance
            $command = Yii::app()->db->createCommand();
            // Check if website already exists in the database
            $website = $command->select('modified, id')->from('{{website}}')->where('md5domain=:id', array( ':id' => md5($this->domain) ))->queryRow();
            // If website exists and we do not need to update data then exit from method
            if ($website and ( $notUpd = ( strtotime($website['modified']) + Yii::app()->params['analyzer.cache_time'] > time() ) )) {
                return true;
            } elseif ($website and ! $notUpd) {
                Utils::deletePdf($this->domain);
                Utils::deletePdf($this->domain . '_pagespeed');
                $args = array( 'yiic', 'parse', 'update', "--domain={$this -> domain}", "--idn={$this -> idn}", "--ip={$this -> ip}", "--wid={$website['id']}" );
            } else {
                $args = array( 'yiic', 'parse', 'insert', "--domain={$this -> domain}", "--idn={$this -> idn}", "--ip={$this -> ip}" );
            }

            // Get command path
            $commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
            // Create new console command runner
            $runner = new CConsoleCommandRunner();
            // Adding commands
            $runner->addCommands($commandPath);
            // If something goes wrong return error
            if ($error = $runner->run( $args)) {
                $this->addError('domain', Yii::t('app', "Error Code $error"));
            } else {
                // After analysis, check if DB record exists
                $websiteCheck = $command->select('id')->from('{{website}}')->where('md5domain=:id', array( ':id' => md5($this->domain) ))->queryRow();
                if (! $websiteCheck) {
                    $this->addError('domain', Yii::t('app', 'Analysis failed: domain record not created. Please try again or check your domain input.'));
                    return false;
                }
                return true;
             
            }
         
        }
     
    }
}
