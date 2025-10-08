<?php
Yii::import("application.vendors.Webmaster.Utils.IDN");

class WebsiteForm extends CFormModel {
	public $domain;
	public $idn; // президент.рф (IDN)
	public $ip;

	public function rules() {
		return array(
			array('domain', 'filter', 'filter'=>array($this, 'trimDomain')),
			array('domain', 'filter', 'filter'=>array($this, 'punycode')),
			array('domain', 'required'),
			array('domain', 'match', 'pattern' => '#^[a-z\d-]{1,62}\.[a-z\d-]{1,62}(.[a-z\d-]{1,62})*$#i', 'skipOnError'=>true),
			array('domain', 'bannedWebsites'),
			array('domain', 'isReachable'),
			array('domain', 'tryToAnalyse'),
		);
	}

	public function attributeLabels() {
		return array(
			'domain' => Yii::t("app", "Domain"),
		);
	}

    public function punycode($domain) {
        $this->domain = idn_to_ascii($domain);
        $this->idn = idn_to_utf8($domain);
        return $this->domain;
    }

	public function bannedWebsites() {
		if(!$this -> hasErrors()) {
            $banned = Utils::getLocalConfigIfExists("domain_restriction");
            foreach($banned as $pattern) {
                if(preg_match("#{$pattern}#i", $this->idn)) {
                    $this -> addError("domain", Yii::t("app", "Error Code 103"));
                }
            }
		}
	}

	public function trimDomain($domain) {
		$domain=trim($domain);
		$domain=trim($domain, "/");
		$domain=mb_strtolower($domain);
		$domain = preg_replace("#^(https?://)#i", "", $domain);
		$domain = preg_replace("#^www\.#i", "", $domain);
		return $domain;
	}

	public function isReachable() {
		if(!$this -> hasErrors()) {
			$this -> ip = gethostbyname($this -> domain);
			$long = ip2long($this -> ip);
			if($long == -1 OR $long === FALSE) {
				$this->addError("domain", Yii::t("app", "Could not reach host: {Host}", array("{Host}" => $this -> domain)));
			}
		}
	}

	public function tryToAnalyse() {
		if(!$this -> hasErrors()) {
			//Remove "www" from domain
			$this -> domain = str_replace("www.", '', $this -> domain);

			// Get command instance
			$command = Yii::app() -> db -> createCommand();

			// Check if website already exists in the database
			$website = $command -> select('modified, id') -> from('{{website}}') -> where('md5domain=:id', array(':id'=>md5($this -> domain))) -> queryRow();

			// If website exists and we do not need to update data then exit from method
			if($website AND ($notUpd = (strtotime($website['modified']) + Yii::app() -> params["analyzer.cache_time"] > time()))) {
				return true;
			} elseif($website AND !$notUpd) {
				Utils::deletePdf($this->domain);
                Utils::deletePdf($this->domain."_pagespeed");
				$args = array('yiic', 'parse', 'update', "--domain={$this -> domain}", "--idn={$this -> idn}", "--ip={$this -> ip}", "--wid={$website['id']}");
			} else {
				$args = array('yiic', 'parse', 'insert', "--domain={$this -> domain}", "--idn={$this -> idn}", "--ip={$this -> ip}");
			}

			// Get command path
			$commandPath = Yii::app() -> getBasePath() . DIRECTORY_SEPARATOR . 'commands';

			// Create new console command runner
			$runner = new CConsoleCommandRunner();

			// Adding commands
			$runner -> addCommands($commandPath);

			// If something goes wrong return error
			if($error = $runner -> run ($args)) {
				$this -> addError("domain", Yii::t("app", "Error Code $error"));
			} else {
				return true;
			}
		}
	}

}