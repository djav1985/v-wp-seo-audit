<?php
/**
 * File: advice.php
 *
 * @package V_WP_SEO_Audit
 */

return array(
	'Title advice - success'                  => 'Täydellistä, otsikkosi sisältää väliltä {1} ja {2} kirjainta.',
	'Title advice - warning'                  => 'Ihannetapauksessa, sinun otsikkosi pitäisi sisältää väliltä {1} ja {2} kirjainta (välilyönnit mukaanlukien ). Käytä <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">tätä ilmaista työkalua</a> laskeaksi tekstin pituus.',
	'Title advice - error'                    => 'Erittäin huono. Emme löytäneet otsikkoa sivustoltasi.',

	'Description advice - success'            => 'Hienoa, sinun meta-kuvauksesi sisältää väliltä{1} ja {2} kirjainta.',
	'Description advice - warning'            => 'Ihannetapauksessa, sinun meta-kuvauksessa pitäisi sisältää väliltä{1} ja {2} kirjainta (välilyönnit mukaanlukien). Käytä <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">tätä ilmaista työkalua</a> laskeaksi tekstin pituus.',
	'Description advice - error'              => 'Erittäin huono. Emme löytäneet meta-kuvausta sivustoltasi ollenkaan. Käytä <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">Tätä ilmaista meta-kuvaus generaattoria</a> lisätäksesi kuvauksen.',

	'Friendly url advice - success'           => 'Hyvä. Sinun linkkisi näyttävät puhtailta!',
	'Friendly url advice - error'             => 'Huono. Sinun linkeissä on hakulauseke (QueryString).',

	'Underscore advice - success'             => 'Täydellistä! URL-osoitteissasi ei ole merkintöjä.',
	'Underscore advice - error'               => 'Olemme havainneet merkintöjä URL-osoitteissasi. Sinun pitäisi pikemminkin käyttää väliviivoja optimoimaan SEO.',

	'Keywords advice - success'               => 'Hyvä, sinun sivullasi on meta -avainsanoja.',
	'Keywords advice - error'                 => 'Erittäin huono. Emme löytäneen meta -sanoja sivultasi. Käytä <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">Tätä ilmaista meta-kuvaus generaattoria</a> lisätäksesi kuvauksen.',

	'Image advice - success'                  => 'Hyvä, lähes tai kaikissa kuvissassi on Alt-attribuutteja.',
	'Image advice - error'                    => '{Number} Alt-attribuutit on tyhjiä tai poistettu. Lisää vaihtoehtoista tekstiä niin, että hakukoneet ymmärtävät paremmin kuvatesi sisällön.',

	'HTML ratio advice - success'             => 'Hyvä, Tämä sivu /sivut sisältää tekstiä suhteesssa HTML-koodiin on suurempi kuin {BadNr}, mutta kuitenkin alle {GoodNr} prosenttia.',
	'HTML ratio advice - success ideal_ratio' => 'Hipoo täydellisyyttä! Tämä sivu /sivut sisältää tekstiä suhteesssa HTML-koodiin on suurempi kuin {BadNr}, mutta kuitenkin alle {GoodNr} prosenttia.',
	'HTML ratio advice - error more_than'     => 'Tämä sivu / sivujen suhde teksti -> HTML koodiin on enemmän kuin {BestNr} prosenttia, tämä tarkoittaa sitä, että sivu voidaan luokitella spämmiksi / roskapostia.',

	'HTML ratio advice - error less_than'     => 'Tämän sivun / sivujen suhde teksti -> HTML on vähemmäinkuin {BadNr} prosenttia, tämä tarkoittaa sitä, että luultavasti tulee tarvitsemaan lisää teksti sisältöä.',

	'Flash advice - success'                  => 'Täydellistä!, 
Flash-sisältöä ei ole havaittu tällä sivulla.',
	'Flash advice - error'                    => 'Surullista, sinulla on Flash-sisältöä, tämä tarkoittaa sitä, että hakukoneet eivät ymmärrä sisältöäsi.',

	'Iframe advice - success'                 => 'Hienoa, 
Tällä sivulla ei ole Iframeja.',
	'Iframe advice - error'                   => 'Erittäin huono, Web-sivuilla on Iframes, tämä tarkoittaa, että Iframe-sisältöä ei voida indeksoida.',

	'Favicon advice - success'                => 'Hienoa, sinun sivulla on favicon (pikakuvake).',
	'Favicon advice - error'                  => 'Erittäin surullista, Emme ole löytäneet pikakuvaketta. Kuvakkeet ovat yksi helpoista tavoista houkutella säännöllisesti kävijöitä verkkosivuillesi useammin.',

	'Printability advice - success'           => 'Hienoa. Olemme löytäneet tulostusystävällisen CSS:n.',
	'Printability advice - error'             => '
Emme löytäneet tulostusystävällistä CSS-palvelua.',

	'Language advice - success'               => 'Hyvä. Ilmoitettu kieli on {Language}.',
	'Language advice - error'                 => 'Et ole määrittänyt kieltä. Käytä <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">tätä ilmaista meta tägi generaattoria</a> määrittääksesi sivustosi kielen.',

	'Dublin Core advice - success'            => 'Hienoa. Tämä sivu käyttää hyödyksi (DublinCore =DC) metatietosanastostandardi informaatiokuvausta.',
	'Dublin Core advice - error'              => 'Tämä sivu ei käytä hyödyksi (DublinCore =DC) metatietosanastostandardi informaatiokuvausta.',

	'Og Meta Properties advice - success'     => 'Hienoa, sinun sivu käyttää hyödyksi Open Graph protokollaa (OG meta prop).',
	'Og Meta Properties advice - error'       => 'Tämä sivu ei käytä hyödyksi Open Graph protokollaa. Tunnisteet mahdollistavat sosiaalisen indeksoijan paremman jäsentämisen. Käytä <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">tätä ilmaista og määritelmä generaattoria</a> luodaksesi ne.',

	'Encoding advice - success'               => 'Täydellistä. Ilmoitettu asiakirjan merkkijono on {Charset}.',
	'Encoding advice - error'                 => 'Et ole määrittänyt asiakirjan merkkiä. Käytä <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">tätä ilmaista meta-tägi generaattoria</a> määrittääksesi asiakirja;n merkkijonon.',

	'Email Privacy advice - success'          => 'Mahtavaa!sähköpostiosoitteita ei ole löytynyt tavallisesta tekstistä!',
	'Email Privacy advice - error'            => 'Varoitus! Ainakin yksi sähköpostiosoite on löytynyt tavallisesta tekstistä. Käytä <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">tätä ilmaista antispam suojausta</a> piilottaaksesi sähköpostiosoitteet spämmereiltä.',

	'Nested tables advice - success'          => 'Erinomaista,
verkkosivustosi ei käytä sisäkkäisiä taulukoita.',
	'Nested tables advice - error'            => 'Huomio! Yritä välttää sisäkkäisiä taulukoita HTML: ssä.',

	'Inline CSS advice - success'             => 'Perfect. HTML-tunnisteita ei löytynyt css:n sisältä!',
	'Inline CSS advice - error'               => 'Harmillista, Sivustosi käyttää sisäisiä tyylejä.',

	'CSS count advice - success'              => 'Hienoa, Sivustossasi on muutamia CSS-tiedostoja.',
	'CSS count advice - error'                => 'Harmillista, sivustossasi on liian monta CSS-tiedostoa (enemmänkuin{MoreNr}).',

	'JS count advice - success'               => 'Perfect, sivustossasi on muutamia JavaScript-tiedostoja.',
	'JS count advice - error'                 => 'Harmillista, sivustossasi on liikaa JavaScript-tiedostoja (enemmänkuin{MoreNr}).',

	'Deprecated advice - success'             => 'Hienoa! Emme ole löytäneet vanhentuneita HTML-tunnisteita HTML-koodistasi.',
	'Deprecated advice - error'               => 'Epäillyt HTML-koodit ovat HTML-tageja, joita ei enää käytetä. On suositeltavaa poistaa tai korvata nämä HTML-tunnisteet, koska ne ovat vanhentuneet.',

	// v 4.0.
	'XML Sitemap - success'                   => 'Hienoa, sivustossasi on XML-sivukartta.',
	'XML Sitemap - error'                     => 'Sivustollasi ei ole XML-sivukarttaa - tämä voi olla ongelmallinen. <br> <br> Sivukartta sisältää URL-osoitteita, jotka ovat käytettävissä indeksointiin ja voivat sisältää lisätietoja, kuten sivustosi uusimmat päivitykset, muutosten tiheydet ja URL-osoitteita. Tämä sallii hakukoneiden indeksoida sivuston älykkäästi. ',

	'Robots txt - success'                    => 'Hienoa, sivustossasi on robots.txt-tiedosto.',
	'Robots txt - error'                      => 'Sivustollasi ei ole robots.txt-tiedostoa - tämä voi olla ongelmallinen. <br> <br> Robots.txt-tiedoston avulla voit rajoittaa web-indeksointiin tarkoitettujen hakukonerobottien käyttöä ja estää näiden robotien pääsyn tiettyihin hakemistoihin ja sivuihin. Se myös määrittää, missä XML-sivukarttatiedosto sijaitsee.',

	'Gzip - success'                          => 'Täydellistä, Sivustosi hyödyntää gzipia.',
	'Gzip - error'                            => 'Harmillista, sivustosi ei hyödynnä gzipia.',

	'Analytics - success'                     => 'Hienoa, sivustossasi on analyysityökalu.',
	'Analytics - error'                       => 'Emme tunnistaneet tällä sivustolla asennettua analytiikkatyökalua. <br> <br> Web-analyysilla voit mitata kävijän toimintaa verkkosivustollasi. Sinulla on oltava vähintään yksi analytiikkatyökalu, mutta voi myös olla hyvä asentaa toinen tietojen tarkistamiseen soveltuva työkalu.',
);
