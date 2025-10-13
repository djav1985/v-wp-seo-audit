<?php

return array(
	'Title advice - success'                  => 'Perfekt, din titel innehåller mellan {1} och {2} tecken.',
	'Title advice - warning'                  => 'Idealisk, din titel bör innehålla mellan {1} och {2} tecken (mellanslag räknas som tecken). Använd <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">denna gratis verktyg</a> för att räkna ut textlängden.',
	'Title advice - error'                    => 'Mycket dåligt. Vi har inte lyckats hitta någon titel på din sida.',

	'Description advice - success'            => 'Bra, din metabeskrivning innehåller mellan {1} och {2} tecken.',
	'Description advice - warning'            => 'Idealisk, din metabeskrivning bör innehålla mellan {1} och {2} tecken (mellanslag räknas som tecken). Använd <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">denna gratis verktyg</a> för att räkna ut textlängden.',
	'Description advice - error'              => 'Mycket dåligt. Vi har inte lyckats hitta någon metabeskrivning på din sida. Använd <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denna online meta-taggar generator, gratis</a> för att skapa beskrivningar.',

	'Friendly url advice - success'           => 'Bra. Dina adressfält ser bra ut!',
	'Friendly url advice - error'             => 'Mycket dåligt. Din adressfält innehåller frågesträngar.',

	'Underscore advice - success'             => 'Perfekt! Inga understreck upptäcktes i din webbadress.',
	'Underscore advice - error'               => 'Vi har upptäckt understreck i din webbadress. Du bör hellre använda bindestreck för att optimera din SEO.',

	'Keywords advice - success'               => 'Bra, din sida innehåller meta-taggar.',
	'Keywords advice - error'                 => 'Mycket dåligt. Vi har inte lyckats hitta några meta-taggar på din sida. Använd <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denna meta-tag generator, gratis</a> för att skapa nyckelord.',

	'Image advice - success'                  => 'Bra, de flesta eller alla dina bilder innehåller alt-attribut',
	'Image advice - error'                    => '{Number} alt attribut är tomma eller saknas. Lägg till alternativ text så att sökmotorer enklare kan förstå innehållet i dina bilder.',

	'HTML ratio advice - success'             => 'Bra, den här sidans förhållande mellan text till HTML-kod är högre än {BadNr}, men lägre än {GoodNr} procent.',
	'HTML ratio advice - success ideal_ratio' => 'Idealisk! Den här sidans text till HTML-kod förhållande är mellan {GoodNr} och {BestNr} procent.',
	'HTML ratio advice - error more_than'     => 'Denna sidas förhållande mellan text till HTML-kod är mer än {BestNr} procent, vilket innebär att din sida riskera att bli betraktad som skräppost.',
	'HTML ratio advice - error less_than'     => 'Denna sidas förhållande mellan text till HTML-kod är lägre än {BadNr} procent, vilket innebär att din webbplats troligen behöver mer textinnehåll.',

	'Flash advice - success'                  => 'Perfekt, inga Flash-innehåll har upptäckts på denna sida.',
	'Flash advice - error'                    => 'Fruktansvärt, du har Flash-innehåll som gör att sökmotorer har svårt att förstå ditt innehåll. ',

	'Iframe advice - success'                 => 'Bra, vi upptäckte inga Iframes på den här sidan.',
	'Iframe advice - error'                   => 'Synd, du har Iframes på webbsidorna vilket innebär att innehållet i en Iframe inte kan indexeras.',

	'Favicon advice - success'                => 'Bra, din webbplats har en favicon.',
	'Favicon advice - error'                  => 'Väldigt dåligt.  Vi har inte hittat någon favicon. Ikoner är en av dom enklare sätten att locka regelbundna besökare till din webbplats oftare.',

	'Printability advice - success'           => 'Bra. Vi hittade CSS för utskrifter.',
	'Printability advice - error'             => 'Vi kunde inte hitta CSS för utskrifter.',

	'Language advice - success'               => 'Bra. Ditt angivna språk är {Language}.',
	'Language advice - error'                 => 'Du har inte angett språk. Använd <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denna meta-tag generator helt gratis</a> för att ange språk på din webbplats.',

	'Dublin Core advice - success'            => 'Bra. Denna sida drar nytta utav Dublin Core.',
	'Dublin Core advice - error'              => 'Denna sida drar inte nytta utav Dublin Core.',

	'Og Meta Properties advice - success'     => 'Bra, din sida drar nytta utav Og.',
	'Og Meta Properties advice - error'       => 'Den här sidan drar inte nytta utav Og. Deras taggar möjliggör sociala sökrobotar att bättre strukturera strukturera din sida. Använd <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">denna og generatorn gratis </a> för att skapa dom.',

	'Encoding advice - success'               => 'Perfekt. Din deklarerade teckenuppsättning är {Charset}.',
	'Encoding advice - error'                 => 'Du har inte specifierat dokumentens teckenuppsättning. Använd <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">denna meta-tag generatorn helt gratis</a> för att deklarera dokumentens teckenuppsättning.',

	'Email Privacy advice - success'          => 'Bra! Ingen e-postadress har hittats i klartext.',
	'Email Privacy advice - error'            => 'Varning! Minst en e-postadress har påträffats i klartext. Använd <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">gratis antispam skydd</a> för att dölja e-post från spammare.',

	'Nested tables advice - success'          => 'Utmärkt, din webbplats använder inga nästlade tabeller.',
	'Nested tables advice - error'            => 'Varning! Försök att undvika nästlade tabeller i HTML.',

	'Inline CSS advice - success'             => 'Perfekt. Ingen inline css har upptäckts i HTML taggar!',
	'Inline CSS advice - error'               => 'Synd, din webbplats använder sig utav inline stilar.',

	'CSS count advice - success'              => 'Bra, din webbplats har få CSS-filer.',
	'CSS count advice - error'                => 'Synd, din webbplats har för många CSS-filer (fler än {MoreNr} stycken).',

	'JS count advice - success'               => 'Perfekt, din webbplats har få Javascript filer',
	'JS count advice - error'                 => 'Synd, din webbplats har för många JS filer (fler än {MoreNr} stycken).',

	'Deprecated advice - success'             => 'Bra! Vi har inte hittat några föråldrad HTML taggar i din HTML.',
	'Deprecated advice - error'               => 'Föråldrade HTML-taggar är HTML-taggar som inte längre används. Vi rekommenderar att du tar bort eller ersätter dessa eftersom dom nu är föråldrade.',

	// v 4.0
	'XML Sitemap - success'                   => 'Bra, din webbplats har en XML sitemap.',
	'XML Sitemap - error'                     => 'Din webbplats saknar en XML sitemap - detta kan orsaka ett problem.<br><br>Eftersom en sitemap listar webbadresser som är tillgängliga för genomsökning och kan innehålla ytterligare information såsom din webbplats senaste uppdatering, antal förändringar och betydelsen av webbadresserna. Detta gör det möjligt för sökmotorer att på ett mer intelligent sätt genomsöka webbplatsen.',

	'Robots txt - success'                    => 'Bra, din webbplats har en robots.txt fil.',
	'Robots txt - error'                      => 'Din webbplats saknar en robots.txt fil. Detta kan orsaka ett problem<br><br>Eftersom en robots.txt fil kan begränsa tillgång för sökmotorer som söker igenom webbplatsen och förhindra dom från åtkomst till specifika kataloger och sidor. Den anger också var XML sitemap filen finns.',

	'Gzip - success'                          => 'Perfekt, din webbplats utnyttjar gzip.',
	'Gzip - error'                            => 'Synd, din webbplats utnyttjar inte gzip.',

	'Analytics - success'                     => 'Bra, din webbplats har ett analysverktyg.',
	'Analytics - error'                       => 'Vi hittade inte någon analysverktyg på din webbplats.<br><br> Webbanalys program kan mäta besökare på din webbplats. Du bör ha minst ett analysverktyg installerat, men det kan också vara en bra ide att installera två för att dubbelkolla uppgifterna.',
);
