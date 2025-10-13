<?php
/**
 * File: advice.php
 *
 * @package V_WP_SEO_Audit
 */

return array(
	'Title advice - success'                  => 'Perfect, uw title tag bevat tussen de {1} en {2} karakters.',
	'Title advice - warning'                  => 'Let op, uw title tag zou tussen de {1} en {2} karakters (spaties inbegrepen) moeten bevatten. Gebruik <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">deze gratis tool</a> om tekst lengte te berekenen.',
	'Title advice - error'                    => 'Erg slecht. We hebben geen title tag gevonden in uw website.',

	'Description advice - success'            => 'Perfect, uw meta description bevat tussen de {1} en {2} karakters.',
	'Description advice - warning'            => 'Let op, uw meta description zou tussen de {1} en {2} karakters (spaces included) moeten bevatten.',
	'Description advice - error'              => 'Erg slecht.we hebben geen meta description gevonden in uw website. Gebruik <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">deze gratis online meta tags generator</a> om een beschrijving te genereren.',

	'Friendly url advice - success'           => 'Perfect. Uw links zien er vriendelijk uit!',
	'Friendly url advice - error'             => 'Slecht. Uw links maken gebruik van een query string.',

	'Underscore advice - success'             => 'Perfect! Geen underscores gevonden in uw URLs.',
	'Underscore advice - error'               => 'We hebben underscores gevonden in uw URLs. U zou het minteken moeten gebruiken ten behoeven van SEO.',

	'Keywords advice - success'               => 'Goed, uw bevat meta keywords.',
	'Keywords advice - error'                 => 'Erg slecht. We hebben geen meta keywords gevonden in uw website. Gebruik <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">deze gratis online meta tags generator</a> om keywords te genereren.',

	'Image advice - success'                  => 'Goed, de meeste of alle afbeeldingen hebben een alt tekst',
	'Image advice - error'                    => '{Number} alt attributen ontbreken. Voeg alternatieve text toe zodat zoekmachines beter kunnen beoordelen wat het onderwerp van de afbeeldingen is.',

	'HTML ratio advice - success'             => 'Goed, De ratio van text tot HTML code hoger dan {BadNr}, maar lager dan {GoodNr} procent.',
	'HTML ratio advice - success ideal_ratio' => 'Ideaal! De ratio van text tot HTML code is tussen de {GoodNr} en {BestNr} procent.',
	'HTML ratio advice - error more_than'     => 'De ratio van text tot HTML code is meer dan {BestNr} procent, uw pagina kan worden gezien als spam.',
	'HTML ratio advice - error less_than'     => 'De ratio van text tot HTML code is below {BadNr} procent, dit betekent dat uw pagina waarschijnlijk meer tekst nodig heeft.',

	'Flash advice - success'                  => 'Perfect, geen Flash content gevonden in uw website.',
	'Flash advice - error'                    => 'Verschrikkelijk, u heeft Flash content, hierdoor begrijpen zoekmachines de inhoud van uw site waarschijnlijk niet correct.',

	'Iframe advice - success'                 => 'Perfect, er zijn geen Iframes in uw website aangetroffen.',
	'Iframe advice - error'                   => 'Jammer, u heeft Iframes in uw website, dit betekent dat deze content niet kan worden geïndexeerd.',

	'Favicon advice - success'                => 'Goed, uw website heeft een favicon.',
	'Favicon advice - error'                  => 'Erg slecht. We hebben geen favicon icoon gevonden. Deze trekken de aandacht van bezoekers in de regel.',

	'Printability advice - success'           => 'Geweldig. We vonden Print-Vriendelijke CSS.',
	'Printability advice - error'             => 'Jammer. We vonden geen Print-Vriendelijke CSS.',

	'Language advice - success'               => 'Goed. Uw ingestelde taal is {Language}.',
	'Language advice - error'                 => 'Er is geen taal ingesteld. Gebruik <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">deze gratis meta tags generator</a> om de taal van uw website mee in te stellen.',

	'Dublin Core advice - success'            => 'Geweldig. Deze pagina maakt gebruik van Dublin Core.',
	'Dublin Core advice - error'              => 'Deze pagina maakt geen gebruik van Dublin Core.',

	'Og Meta Properties advice - success'     => 'Goed, uw page maakt gebruik van Og Properties.',
	'Og Meta Properties advice - error'       => 'Deze pagina maakt geen gebruik van Og Properties. Deze tags maken het sociale crawlers makkelijker uw pagina te indexeren. ',

	'Encoding advice - success'               => 'Perfect. Uw ingestelde Charset is {Charset}.',
	'Encoding advice - error'                 => 'U heeft geen Charset ingesteld voor uw pagina. Gebruik <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">deze gratis meta tags generator</a> om de Charset van uw website mee in te stellen.',

	'Email Privacy advice - success'          => 'Geweldig er is geen e-mail adres gevonden als platte tekst!',
	'Email Privacy advice - error'            => 'Waarschuwing! Er is op zijn minst 1 e-mailadres gevonden als platte tekst. (voorkom spam!). Gebruik <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">deze gratis antispam protector</a> om e-mailadressen te verbergen voor spammers.',

	'Nested tables advice - success'          => 'Geweldig, uw website heeft geen tabellen in een tabel.',
	'Nested tables advice - error'            => 'Attentie! Probeer tabellen in tabellen te voorkomen in uw HTML.',

	'Inline CSS advice - success'             => 'Perfect. Er zijn geen inline CSS style gevonden in uw HTML!',
	'Inline CSS advice - error'               => 'Jammer, uw website maakt gebruik van inline styles.',

	'CSS count advice - success'              => 'Geweldig, uw website heeft een correct aantal CSS bestanden.',
	'CSS count advice - error'                => 'Jammer, uw website heeft teveel CSS bestanden (meer dan {MoreNr}).',

	'JS count advice - success'               => 'Perfect, uw website heeft een correct aantal JavaScript bestanden.',
	'JS count advice - error'                 => 'Jammer, uw website heeft teveel JS bestanden (meer dan {MoreNr}).',

	'Deprecated advice - success'             => 'Geweldig! We hebben geen niet meer ondersteunde HTMl tags gevonden in uw HTML.',
	'Deprecated advice - error'               => 'We hebben niet meer ondersteunde HTML tags gevonden in uw HTML, we adviseren u deze te verwijderen.',

	// v 4.0.
	'XML Sitemap - success'                   => 'Geweldig, uw website heeft een XML sitemap.',
	'XML Sitemap - error'                     => 'Uw website heeft geen XML sitemap- dit kan problematisch zijn.<br><br>Een sitemap vermeld url\'s die beschikbaar zijn voor crawlen en kan extra informatie bevatten zoals uw laatste site-updates, frequentie van wijzigingen en het belang van de url\'s. Dit laat zoekmachines toe om de site intelligenter te doorzoeken.',

	'Robots txt - success'                    => 'Geweldig uw website heeft een robots.txt bestand.',
	'Robots txt - error'                      => 'Uw website heeft geen robots.txt bestand - wat problematisch kan zijn. <br><br>Een robots.txt bestand laat u toe om de toegang voor zoekmachinerobots te beperken en voorkomt ook toegang tot specifieke directory\'s en pagina\'s Het geeft ook aan waar de XML sitemap te vinden is.',

	'Gzip - success'                          => 'Perfect, uw website haalt voordeel uit gzip.',
	'Gzip - error'                            => 'Jammer, uw website haalt geen voordeel uit gzip.',

	'Analytics - success'                     => 'Perfect, uw website heeft een analytics tool.',
	'Analytics - error'                       => 'We hadden niet op te sporen van een analytics tool op deze website geplaatst. <br><br>Web Analytics laat u toe de bezoekersactiviteit op uw website te meten. U zou minstens 1 Analytics tool geïnstalleerd moeten hebben en een extra tool voor de bevestiging van de resultaten.',
);
