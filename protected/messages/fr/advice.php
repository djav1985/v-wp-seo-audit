<?php

return array(
	'Title advice - success'                  => 'Parfait, votre titre contient entre {1} et {2} caractères.',
	'Title advice - warning'                  => 'Idéalement, votre titre devrait contenir entre {1} et {2} caractères (espaces compris). Utilisez <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">cet outil gratuit</a> pour calculer la longueur du texte.',
	'Title advice - error'                    => 'Très mauvais. Nous n\'avons pas trouvé de titre sur votre page.',

	'Description advice - success'            => 'Génial, votre balise META description contient entre {1} et {2} caractères.',
	'Description advice - warning'            => 'Idéalement, votre balise META description devrait contenir entre {1} et {2} caractères (espaces compris). Utilisez <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">cet outil gratuit</a> pour calculer la longueur du texte.',
	'Description advice - error'              => 'Très mauvais. Nous n\'avons pas trouvé de balise META description sur votre page. Utilisez <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">ce générateur gratuit de balises META en ligne</a> pour créer une description.',

	'Friendly url advice - success'           => 'Bien. Vos liens sont optimisés!',
	'Friendly url advice - error'             => 'Mauvais. Vos liens contiennent des chaînes de requête.',

	'Underscore advice - success'             => 'Parfait! Aucuns soulignements détectés dans vos URLs.',
	'Underscore advice - error'               => 'Nous avons détectés des soulignements dans vos URLs. Vous devriez plutôt utiliser des tirets pour optimiser votre référencement.',

	'Keywords advice - success'               => 'Bien, votre page contient une balise META keywords.',
	'Keywords advice - error'                 => 'Très mauvais. Nous n\'avons pas trouvé de balise META keywords sur votre page. Utilisez <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">ce générateur gratuit de balises META en ligne</a> pour créer des mots-clés.',

	'Image advice - success'                  => 'Bien, la plupart ou la totalité de vos images possèdent un attribut alt',
	'Image advice - error'                    => '{Number} attribut(s) alt sont vides ou manquants. Ajouter un texte alternatif permet aux moteurs de recherche de mieux comprendre le contenu de vos images.',

	'HTML ratio advice - success'             => 'Bien, le ratio de cette page texte/HTML est supérieur à {BadNr}, mais inférieur à  {GoodNr} pour cent.',
	'HTML ratio advice - success ideal_ratio' => 'Idéal! le ratio de cette page texte/HTML est entre {GoodNr} et {BestNr} pour cent.',
	'HTML ratio advice - error more_than'     => 'le ratio de cette page texte/HTML est de plus de {BestNr} pour cent, cela signifie que votre page peut courir le risque d\'être considéré comme spam.',
	'HTML ratio advice - error less_than'     => 'le ratio de cette page texte/HTML est au-dessous de {BadNr} pour cent, ce qui signifie que votre site manque de contenu textuel.',

	'Flash advice - success'                  => 'Parfait, aucun contenu FLASH n\'a été détecté sur cette page.',
	'Flash advice - error'                    => 'Misére, vous avez du contenu Flash, cela signifie que les moteurs de recherche auront des difficultés à analyser votre contenu.',

	'Iframe advice - success'                 => 'Génial, il n\'y a pas d\'Iframes détectés sur cette page.',
	'Iframe advice - error'                   => 'Dommage, vous avez des Iframes sur vos pages Web, cela signifie que son contenu ne peut pas être indexé par les moteurs de recherche.',

	'Favicon advice - success'                => 'Génial, votre site web dispose d\'un favicon.',
	'Favicon advice - error'                  => 'Trés mauvais. Nous n\'avons pas trouvé d\'icônes de raccourci. Les icônes sont l\'un des moyens faciles d\'attirer des visiteurs réguliers plus souvent sur votre site.',

	'Printability advice - success'           => 'Génial. L\'impression est optimisée grâce aux styles CSS.',
	'Printability advice - error'             => 'Aucun style CSS pour optimiser l\'impression n\'a pu être trouvé.',

	'Language advice - success'               => 'Bien. Votre langue est : {Language}.',
	'Language advice - error'                 => 'Vous n\'avez pas précisé la langue. Utilisez <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">ce générateur gratuit de balises META en ligne</a> pour preciser la langue de votre site',

	'Dublin Core advice - success'            => 'Génial. Cette page profite des métadonnées Dublin Core.',
	'Dublin Core advice - error'              => 'Cette page ne profite pas des métadonnées Dublin Core.',

	'Og Meta Properties advice - success'     => 'Bien, cette page profite des balises META Open Graph.',
	'Og Meta Properties advice - error'       => 'Cette page ne profite pas des balises META Open Graph. Cette balise permet de
	représenter de manière riche n\'importe quelle page dans le graph social (environnement social). Utilisez <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">ce générateur gratuit de balises META Open Graph</a> pour les créer.',

	'Encoding advice - success'               => 'Parfait. Votre charset est {Charset}.',
	'Encoding advice - error'                 => 'Vous n\'avez pas spécifié le charset du document. Utilisez <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">ce générateur gratuit de balises META</a> pour préciser le charset du document.',

	'Email Privacy advice - success'          => 'Génial, aucune adresse e-mail n\'a été trouvé sous forme de texte!',
	'Email Privacy advice - error'            => 'Attention! Au moins une adresse e-mail a été trouvée en texte clair. Utilisez <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">une protection anti-spam gratuite</a> pour cacher vos e-mails aux spammeurs.',

	'Nested tables advice - success'          => 'Excellent, votre site n\'utilise pas de tableaux imbriqués.',
	'Nested tables advice - error'            => 'Attention! Essayez d\'éviter les tableaux imbriqués au format HTML.',

	'Inline CSS advice - success'             => 'Parfait. Aucun style css inline n\'a été trouvé dans vos tags HTML!',
	'Inline CSS advice - error'               => 'Mauvais, votre site web utilise des styles css inline.',

	'CSS count advice - success'              => 'Génial, votre site web contient peu de fichiers CSS.',
	'CSS count advice - error'                => 'Mauvais, votre site web contient trop de fichiers CSS (plus de {MoreNr}).',

	'JS count advice - success'               => 'Parfait, votre site web contient peu de fichiers javascript.',
	'JS count advice - error'                 => 'Mauvais, votre site web contient trop de fichiers javascript (plus de {MoreNr}).',

	'Deprecated advice - success'             => 'Génial! Nous n\'avons pas trouvé de balises HTML obsolètes dans votre code.',
	'Deprecated advice - error'               => 'Les balises HTML obsolètes sont des balises qui ne sont plus utilisés. Il est recommandé de supprimer ou de remplacer ces balises HTML, car elles sont désormais obsolètes.',

	// v 4.0
	'XML Sitemap - success'                   => 'Votre site web dispose d’une sitemap XML, ce qui est optimal.',
	'XML Sitemap - error'                     => 'Votre site web ne dispose pas d’une sitemap XML, ce qui peut poser problème.<br><br>La sitemap recense les URLs que les moteurs de recherche peuvent indexer, tout en proposant d’éventuelles informations supplémentaires (comme la date de dernière mise à jour, la fréquence des changements, ainsi que leur niveau d’importance). Ceci permet aux moteurs de recherche de parcourir le site de façon plus efficace.',

	'Robots txt - success'                    => 'Votre site dispose d’un fichier robots.txt, ce qui est optimal.',
	'Robots txt - error'                      => 'Votre site ne dispose pas d’un fichier robots.txt, ce qui peut poser problème. <br><br>Un fichier robots.txt vous permet d’empêcher l’accès des moteurs de recherche à certains dossiers et pages spécifiques. Ce fichier précise également où se trouve la sitemap XML.',

	'Gzip - success'                          => 'Parfait : votre site tire parti de gzip.',
	'Gzip - error'                            => 'Dommage, votre site n\'est pas optimisé avec gzip.',

	'Analytics - success'                     => 'Votre site web dispose d’une outil d\'analytics, ce qui est optimal.',
	'Analytics - error'                       => 'Nous n\'avons trouvé aucun outil d\'analytics sur ce site. <br><br>Un outil de mesure d\'audience vous permet d\'analyser l’activité des visiteurs sur votre site. Vous devriez installer au moins un outil Analytics. Il est souvent utile d’en rajouter un second, afin de confirmer les résultats du premier.',
);
