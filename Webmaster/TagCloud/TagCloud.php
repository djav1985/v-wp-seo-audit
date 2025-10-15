<?php
class TagCloud {
	private $text;
	private $commonWords;

	public function __construct($html, $lg) {
		$this -> text = Helper::striptags($html);
		$cFile = dirname(__FILE__)."/CommonWords/".strtolower((string) $lg).".php";
		$dFile = dirname(__FILE__)."/CommonWords/en.php";
		$this -> commonWords = file_exists($cFile) ? include $cFile : include $dFile;
	}

	public function generate($total = 10) {
		$cloud = $_cloud = array();
		$string = $this->removeCommonWords($this -> text);
		$words = preg_split("/[\s]+/", $string);

		foreach($words as $word) {
			//$word = trim(preg_replace("#([\p{P}\p{S}]+)#iu", "", mb_strtolower($word)));
            $word = trim(preg_replace("#((?![-])[\p{P}\p{S}]+)#iu", "", mb_strtolower((string) $word)));

            // Skip short words
			if(mb_strlen($word) < 3) {
				continue;
			}

            // Skip dates
            if(preg_match("#^[\d\-]+$#ui", $word)) {
                continue;
            }

            // Skip string containing no letters
            /*if(!preg_match('~([\p{L&}]+)~iu', $word)) {
                continue;
            }*/

            // Skip if string has the same char sequence
            if(preg_match('#^(.)\1{2,}$#iu', $word)) {
                continue;
            }

			if(isset($_cloud[$word]))
				$_cloud[$word]++;
			else
				$_cloud[$word] = 1;
		}

		arsort($_cloud, SORT_NUMERIC);
		$toptags = array_slice($_cloud, 0, $total);
		$max = !empty($toptags) ? max($toptags) : 0;

		foreach($toptags as $tag => $cnt) {
			$cloud[$tag]['count'] = $cnt;
			$cloud[$tag]['grade'] = $this -> calculateGrade($cnt, $max);
		}
		return $cloud;
	}

	private function calculateGrade($cnt, $max) {
		return $max > 0 ? $this->gradeFrequency(($cnt * 100) / $max) : 0;
	}

	private function gradeFrequency($frequency) {
		$grade = 0;
		if ($frequency >= 80)
			$grade = 5;
		else if ($frequency >= 60)
			$grade = 4;
		else if ($frequency >= 40)
			$grade = 3;
		else if ($frequency >= 20)
			$grade = 2;
		else if ($frequency >= 5)
			$grade = 1;
		return $grade;
	}

	private function removeCommonWords($input) {
		return preg_replace('#\b('. implode('|', $this -> commonWords) .')\b#is', "", $input);
	}
}