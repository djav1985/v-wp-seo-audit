<?php
/**
 * File: advice.php
 *
 * @package V_WP_SEO_Audit
 */

return array(
	'Title advice - success'                  => 'Perfekt, din titel indeholder mellem {1} og {2} bogstaver.',
	'Title advice - warning'                  => 'Kan optimeres; Optimalt bør din titel indeholde mellem {1} og {2} karakterer (med mellemrum) Brug <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">dette gratis redskab</a> til at regne længden ud.',
	'Title advice - error'                    => 'Meget kritisk. Vi kan ikke finde en titel på din side.',

	'Description advice - success'            => 'Perfekt, din meta beskrivelse indeholder mellem {1} og {2} karakterer.',
	'Description advice - warning'            => 'Kan optimeres; din meta beskrivelse bør indeholde mellem {1} og {2} karakterer (med mellemrum). Brug <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">dette gratis redskab</a> til at regne længden ud.',
	'Description advice - error'              => 'Meget kritisk. Vi kan ikke finde en meta beskrivelse på dit website! Brug <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denne gratis meta generator</a> til at lave beskrivelser.',

	'Friendly url advice - success'           => 'Godt. Dine links ser venlige ud!',
	'Friendly url advice - error'             => 'Dårligt! Dine links er ikke optimerede til SEO.',

	'Underscore advice - success'             => 'Perfekt! Ingen underscores blev fundet i dine links',
	'Underscore advice - error'               => 'Dårligt! Vi har fundet underscores i dine links, du bør benytte bindestreg istedet for underscores for at optimere din SEO.',

	'Keywords advice - success'               => 'Godt, din side indeholder meta nøgleord.',
	'Keywords advice - error'                 => 'Dårligt! Vi kan ikke finde nogle meta nøgleord på din side! Brug <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denne gratis online meta generator</a> for at oprette nye nøgleord.',

	'Image advice - success'                  => 'Godt, de fleste eller alle af dine billeder har ALT tags.',
	'Image advice - error'                    => '{Number} alt tags mangler eller er tomme. Tilføj alternativ tekst til dine billeder for at gøre siden mere brugervenlig, og for at optimere din SEO i forhold til søgemaskinerne.',

	'HTML ratio advice - success'             => 'Godt, denne side har en god fordeling af text og HTML. Balancen er højere end {BadNr}, men lavere end {GoodNr} procent.',
	'HTML ratio advice - success ideal_ratio' => 'Optimalt! Denne sides text til HTML fordeling er mellem {GoodNr} og {BestNr} procent.',
	'HTML ratio advice - error more_than'     => 'Denne sides text til HTML fordeling er over  {BestNr} procent, dette kan betyde din side bliver opfattet som spam!',
	'HTML ratio advice - error less_than'     => 'Denne sides text til HTML fordeling er under {BadNr} procent, dette betyder at din side mangler indhold!',

	'Flash advice - success'                  => 'Perfekt, ingen Flash objekter er blevet fundet på siden.',
	'Flash advice - error'                    => 'Dårligt! Du har flash objekter på din side - Søgemaskinerne kan ikke tolke disse.',

	'Iframe advice - success'                 => 'Perfekt, der er ikke nogen iFrames på din side!',
	'Iframe advice - error'                   => 'Beklager! Din side har iFrames og det kan medføre i yderst dårlig læsning af søgerobotterne.',

	'Favicon advice - success'                => 'Godt, din side har et FavIcon!',
	'Favicon advice - error'                  => 'Dårligt, din side har ikke et FavIcon - dette kan tiltrække gamle besøgende gang på gang!',

	'Printability advice - success'           => 'Godt, vi har fundet en printer venlig CSS skabelon.',
	'Printability advice - error'             => 'Vi kunne ikke finde en printer venlig CSS skabelon.',

	'Language advice - success'               => 'Godt, dit tildelte sprog er {Language}.',
	'Language advice - error'                 => 'Du har ikke tildelt din side et sprog! Brug <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denne gratis meta tag generator</a> til at tildele din side dit valgte sprog.',

	'Dublin Core advice - success'            => 'Godt. Denne side benytter Dublin Core principperne.',
	'Dublin Core advice - error'              => 'Denne side benytter IKKE Dublin Core principperne.',

	'Og Meta Properties advice - success'     => 'Godt, din side benytter Og egenskaberne',
	'Og Meta Properties advice - error'       => 'Din side benytter ikke Og egenskaberne. Disse tags tillader sociale medier at forstå din side bedre. Brug <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">denne gratis Og generator</a> for at oprette tags.',

	'Encoding advice - success'               => 'Perfekt. Dit Charset er tildelt {Charset}.',
	'Encoding advice - error'                 => 'Fejl! Du har ikke tildelt et Charset! Brug <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denne gratis meta tag generator</a> for at tildele et charset.',

	'Email Privacy advice - success'          => 'Godt! Ingen email adresser er blevet fundet i rå tekst!',
	'Email Privacy advice - error'            => 'Advarsel! Mindst én email adresse er blevet fundet i rå tekst. Brug <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">gratis antispam beskytter</a> for at hemme din email fra spammere.',

	'Nested tables advice - success'          => 'Alle tiders! Din webside bruger ikke nestede tabeller.',
	'Nested tables advice - error'            => 'Advarsel! Prøv at undgå at benytte nestede tabeller i HTML.',

	'Inline CSS advice - success'             => 'Perfekt. Ingen inline CSS kode er blevet fundet i dine HTML tags!',
	'Inline CSS advice - error'               => 'Advarsel! Din webside benytter inline CSS kode!',

	'CSS count advice - success'              => 'Godt, din website har få antal CSS filer',
	'CSS count advice - error'                => 'Dårligt, din webside har for mange CSS filer (mere end {MoreNr}).',

	'JS count advice - success'               => 'Perfekt, din website har få JavaScript filer.',
	'JS count advice - error'                 => 'Dårligt, din webside har for mange JavaScript filer (mere end  {MoreNr}).',

	'Deprecated advice - success'             => 'Godt! Vi har ikke fundet udgåede HTML tags i din kildekode',
	'Deprecated advice - error'               => 'Fejl! Vi har fundet udgåede HTML tags i din kildekode. Udgåede tags bliver ikke længere understøttet af alle browsere.',

	// v 4.0.
	'XML Sitemap - success'                   => 'Stor, din hjemmeside har en XML sitemap.',
	'XML Sitemap - error'                     => 'Dit websted ikke har en XML sitemap - det kan være problematisk.<br><br>Et sitemap lister URL\'er, der er tilgængelige for gennemgang og kan indeholde yderligere oplysninger som dit websted seneste opdateringer, hyppigheden af ændringer, og betydningen af de webadresser. Dette gør det muligt søgemaskiner til at gennemgå webstedet mere intelligent.',

	'Robots txt - success'                    => 'Stor, din hjemmeside har en robots.txt-fil.',
	'Robots txt - error'                      => 'Dit website har ikke en robots.txt-fil - det kan være problematisk. <br><br>En robots.txt-fil kan du begrænse adgangen for søgemaskinen robotter, der gennemgår internettet, og det kan forhindre disse robotter i at få adgang bestemte mapper og sider. Den angiver også, hvor XML sitemap-filen er placeret.',

	'Gzip - success'                          => 'Perfekt, din hjemmeside udnytter gzip.',
	'Gzip - error'                            => 'Ærgerligt, din hjemmeside ikke udnytte gzip.',

	'Analytics - success'                     => 'Stor, din hjemmeside har et analyseværktøj.',
	'Analytics - error'                       => 'Vi har ikke registrerer en analyseværktøj installeret på denne hjemmeside. <br><br>Web analytics kan du måle besøgendes aktivitet på dit websted. Du bør have mindst én analyseværktøj installeret, men det kan også være godt at installere et sekund for at krydstjekke data.',
);
