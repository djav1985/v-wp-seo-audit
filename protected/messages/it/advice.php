<?php
/**
 * File: advice.php
 *
 * @package V_WP_SEO_Audit
 */

return array(
	'Title advice - success'                  => 'Perfetto, il tuo title contiene tra {1} e {2} caratteri.',
	'Title advice - warning'                  => 'Idealmente, il tuo title dovrebbe contenere tra {1} e {2} caratteri (spazi inclusi). Usa <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">questo strumento free</a> per calcolare la lunghezza del testo.',
	'Title advice - error'                    => 'Molto male. Non abbiamo trovato il title nella tua pagina.',

	'Description advice - success'            => 'Grande, la tua meta description contiene tra {1} e {2} caratteri.',
	'Description advice - warning'            => 'Idealmente, la tua meta description dovrebbe contenere tra {1} e {2} caratteri (spazi inclusi). Usa <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">questo strumento free</a> per calcolare la lunghezza del testo.',
	'Description advice - error'              => 'Molto male. Non abbiamo trovato meta description nella tua pagina. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">questo generatore online gratuito di meta tags</a> per creare la descrizione.',

	'Friendly url advice - success'           => 'Buono. I tuoi links appaiono friendly!',
	'Friendly url advice - error'             => 'Male. I tuoi links hanno query string.',

	'Underscore advice - success'             => 'Perfetto! Non sono stati rilevati underscores nei tuoi URLs.',
	'Underscore advice - error'               => 'Abbiamo rilevato underscores nei tuoi URLs. Dovresti utilizzare trattini per ottimizzare le pagine per il tuo SEO.',

	'Keywords advice - success'               => 'Buono, la tua pagina contiene meta keywords.',
	'Keywords advice - error'                 => 'Molto male. Non abbiamo trovato meta keywords nella tua pagina. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">questo generatore gratuito online di meta tags</a> per creare keywords.',

	'Image advice - success'                  => 'Buono, molte o tutte le tue immagini hanno attributo alt',
	'Image advice - error'                    => '{Number} attributi alt sono vuoti o mancanti. Aggiungi testo alternativo in modo tale che i motori di ricerca possano comprendere meglio il contenuto delle tue immagini.',

	'HTML ratio advice - success'             => 'Buono, il rapporto testo/codice HTML di questa pagina e maggiore di {BadNr}, e minore di {GoodNr} percento.',
	'HTML ratio advice - success ideal_ratio' => 'Ideale! Il rapporto testo/codice HTML di questa pagina e tra {GoodNr} e {BestNr} percento.',
	'HTML ratio advice - error more_than'     => 'Il rapporto testo/codice HTML di questa pagina e piu alto di {BestNr} percento, questo significa che il tuo sito web corre il rischio di essere considerata spam.',
	'HTML ratio advice - error less_than'     => 'Il rapporto testo/codice HTML di questa pagina e inferiore a {BadNr} percento, questo significa che il tuo sito web necessita probabilmente di molto piu contenuto.',

	'Flash advice - success'                  => 'Perfetto, non e stato rilevato contenuto Flash in questa pagina.',
	'Flash advice - error'                    => 'Terribile, hai contenuti Flash, questo significa che per i motori di ricerca e difficile comprendere il contenuto.',

	'Iframe advice - success'                 => 'Grande, non sono stati rilevati Iframes in questa pagina.',
	'Iframe advice - error'                   => 'Molto male, hai usato Iframes nelle tue pagine web, questo significa che in contenuto inserito negli Iframe non puo essere indicizzato.',

	'Favicon advice - success'                => 'Grande, il tuo sito usa una favicon.',
	'Favicon advice - error'                  => 'Molto male. Non abbiamo trovato shortcut icon. Le icone sono una tra le tante semplici possibilita per attirare regolarmente visitatori al tuo sito.',

	'Printability advice - success'           => 'Grande. Abbiamo riscontrato che il tuo codice CSS e Print-Friendly.',
	'Printability advice - error'             => 'Non abbiamo riscontrato codice CSS Print-Friendly.',

	'Language advice - success'               => 'Buono. La tua lingua dichiarata {Language}.',
	'Language advice - error'                 => 'Non hai specificato la lingua. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">questo generatore gratuito di meta tags generator</a> per dichiarare la lingua che intendi utilizzare per il tuo sito web.',

	'Dublin Core advice - success'            => 'Grande. Questa pagina sfrutta i vantaggi di Dublin Core.',
	'Dublin Core advice - error'              => 'Questa pagina non sfrutta i vantaggi di Dublin Core.',

	'Og Meta Properties advice - success'     => 'Buono, questa pagina sfrutta i vantaggi Og Properties.',
	'Og Meta Properties advice - error'       => 'Questa pagina non sfrutta i vantaggi Og Properties. Questi tags consentono ai social crawler di strutturare meglio la tua pagina. Use <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">questo generatore gratuito di og properties</a> per crearli.',

	'Encoding advice - success'               => 'Perfetto. Hai dichiarato che il tuo charset e {Charset}.',
	'Encoding advice - error'                 => 'Non hai specificato il charset per i documenti. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">questo generatore gratuito di meta tagr</a> per dichiarare il charset dei documenti.',

	'Email Privacy advice - success'          => 'Grande. Nessun indirizzo mail e stato trovato in plain text!',
	'Email Privacy advice - error'            => 'Attenzione! E stato trovato almeno un indirizzo mail in plain text. Usa <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">antispam protector gratuito</a> per nascondere gli indirizzi mail agli spammers.',

	'Nested tables advice - success'          => 'Eccellente, il tuo sito web non utilizza nested tables.',
	'Nested tables advice - error'            => 'Attenzione! Cerca di evitare di utilizzare nested tables in HTML.',

	'Inline CSS advice - success'             => 'Perfetto. Nessun codice css inline e stato trovato nei tags HTML!',
	'Inline CSS advice - error'               => 'Molto male, il tuo sito web utilizza stili CSS inline.',

	'CSS count advice - success'              => 'Grande, il tuo sito web ha pochi file CSS.',
	'CSS count advice - error'                => 'Molto male, il tuo sito web ha troppi file CSS files (piu di {MoreNr}).',

	'JS count advice - success'               => 'Perfetto, il tuo sito web ha pochi file JavaScript.',
	'JS count advice - error'                 => 'Molto male, il tuo sito web ha troppi file JS (piu di {MoreNr}).',

	'Deprecated advice - success'             => 'Grande! Non abbiamo trovato tags HTML deprecati nel tuo codice.',
	'Deprecated advice - error'               => 'Tags HTML deprecati sono tags HTML che non vengono piu utilizzati. Ti raccomandiamo di rimuoverli o sostituire questi tags HTML perche ora sono obsoleti.',

	// v 4.0.
	'XML Sitemap - success'                   => 'Grande, il vostro sito ha una sitemap XML.',
	'XML Sitemap - error'                     => 'Il tuo sito web non ha una sitemap XML - questo può essere problematico.<br><br>A elenca sitemap URL che sono disponibili per la scansione e possono includere informazioni aggiuntive come gli ultimi aggiornamenti del tuo sito, frequenza delle variazioni e l\'importanza degli URL. In questo modo i motori di ricerca di eseguire la scansione del sito in modo più intelligente.',

	'Robots txt - success'                    => 'Grande, il vostro sito ha un file robots.txt.',
	'Robots txt - error'                      => 'Il tuo sito web non dispone di un file robots.txt - questo può essere problematico. <br><br>Un file robots.txt consente di limitare l\'accesso dei robot dei motori di ricerca che strisciano sul web ed è possibile evitare che questi robot di accedere directory e pagine specifiche. Specifica anche il luogo dove si trova il file sitemap XML.',

	'Gzip - success'                          => 'Perfetto, il vostro sito si avvale di gzip.',
	'Gzip - error'                            => 'Peccato, il vostro sito non approfitta di gzip.',

	'Analytics - success'                     => 'Grande, il vostro sito ha uno strumento di analisi dei dati.',
	'Analytics - error'                       => 'Non abbiamo rilevato uno strumento di analisi installato su questo sito web. <br><br>Web analytics consentono di misurare l\'attività dei visitatori sul tuo sito web. Si dovrebbe avere installato almeno un strumento di analisi, ma può anche essere buona per installare una seconda, al fine di un controllo incrociato dei dati.',
);
