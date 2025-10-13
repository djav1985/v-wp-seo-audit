<?php

return array(
	'Title advice - success'                  => 'Perfecto, tu título contiene entre {1} y {2} caracteres.',
	'Title advice - warning'                  => 'Preferiblemente, tu título debería contener entre {1} y {2} caracteres (espacios incluidos). Usa <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">esta herramienta gratuita</a> para calcular la longitud del texto.',
	'Title advice - error'                    => 'Muy mal. No hemos podido encontrar un título en tu página.',

	'Description advice - success'            => 'Genial, tu descripción meta contiene entre {1} y {2} caracteres.',
	'Description advice - warning'            => 'Preferiblemente tu descripción meta debe contener entre {1} y {2} caracteres (espacios incluidos). Usa <a href="http://webmaster-tools.php8developer.com/text-length-online.html" target="_blank">esta herramienta gratuita</a> para calcular la longitu del texto.',

	'Description advice - error'              => 'Muy mal. No hemos encontrado descripción meta en tu página. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">este generador online gratuito</a> para crear la descripción.',


	'Friendly url advice - success'           => 'Bien. Tus enlaces parecen amigables',
	'Friendly url advice - error'             => 'Mal. Tus enlaces parecen usar parámetros en la url (query string).',

	'Underscore advice - success'             => 'Perfecto! No hemos detectado guiones bajos en tus URLs',

	'Underscore advice - error'               => 'Hemos detectado guiones bajos en tus URLs. Deberías usar guiones en su lugar para optimizar tu SEO.',

	'Keywords advice - success'               => 'Bien, tu página contiene palabras clave (meta keywords).',
	'Keywords advice - error'                 => 'Muy mal. No hemos encontrado palabras clave (meta keywords) en tu página. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">este generador de meta tags gratuito</a> para crear tus palabras clave.',

	'Image advice - success'                  => 'Bien, la mayoría de tus imágenes tienen atributo alt.',
	'Image advice - error'                    => '{Number} atributos alt están vacios o no existen. Agrega texto alternativo para que los motores de búsqueda puedan entender las imágenes.',

	'HTML ratio advice - success'             => 'Bien, el ratio entre texto y código HTML de esta página es mayor que {BadNr}, pero menor que {GoodNr} por ciento.',

	'HTML ratio advice - success ideal_ratio' => 'Ideal! El ratio entre texto y código HTML de esta página está entre {GoodNr} y {BestNr} por ciento.',
	'HTML ratio advice - error more_than'     => 'El ratio entre texto y código HTML de esta página es mayor que el {BestNr} por ciento, lo que significa que la página corre el riesgo de ser considerada spam.',
	'HTML ratio advice - error less_than'     => 'El ratio entre texto y código HTML de esta página es menor que el {BadNr} por ciento, esto significa que tu web posiblemente necesite más contenido en texto.',

	'Flash advice - success'                  => 'Perfecto, no se ha detectado contenido Flash en la página.',
	'Flash advice - error'                    => 'Horrible, tienes contenido Flash en la página, esto significa que los buscadores lo van a tener muy dificil para leer tu contenido.',

	'Iframe advice - success'                 => 'Genial, no se han detectado Iframes en la página.',
	'Iframe advice - error'                   => 'Muy mal, tienes Iframes en la página, esto significa que el contenido no podrá ser indexado.',

	'Favicon advice - success'                => 'Genial, tu web tiene un favicon.',
	'Favicon advice - error'                  => 'Muy mal, no hemos encontrado un favicon. Los favicon son una forma de atraer a los visitantes a tu sitio de forma más a menudo.',

	'Printability advice - success'           => 'Genial. Hemos encontrado una hoja de estilos CSS para impresión.',
	'Printability advice - error'             => 'No hemos encontrado una hoja de estilos CSS para impresión.',

	'Language advice - success'               => 'Genial. Has declarado el idioma {Language}.',
	'Language advice - error'                 => 'No has especificado un idioma. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">este generador de meta tags gratuito</a> para declarar el idioma de tu sitio.',

	'Dublin Core advice - success'            => 'Genial. La página usa Dublin Core.',
	'Dublin Core advice - error'              => 'Esta página no usa Dublin Core.',

	'Og Meta Properties advice - success'     => 'Bien. Tu página usa propiedades Og (etiquetas og).',
	'Og Meta Properties advice - error'       => 'Esta página no usa etiquetas Og. Estas etiquetas permiten a los rastreadores sociales estructurar mejor tu página. Usa <a href="http://webmaster-tools.php8developer.com/og-properties-generator.html" target="_blank">este generador de etiquetas og gratuito</a> para crearlas.',

	'Encoding advice - success'               => 'Perfecto. Has declarado como codificación {Charset}.',
	'Encoding advice - error'                 => 'No has especificado la codificación del documento. Usa <a href="http://webmaster-tools.php8developer.com/meta-tags-generator.html" target="_blank">este generador de etiqueta meta gratuito</a> para declarar la codificación del documento.',

	'Email Privacy advice - success'          => 'Genial. No hay ninguna dirección de email como texto plano!',
	'Email Privacy advice - error'            => 'Atención! Hemos encontrado por lo menos una dirección de correo electrónico en texto plano. Usa <a href="http://webmaster-tools.php8developer.com/antispam-protector.html" target="_blank">este protector antispam gratuito</a> para ocultarla de los spammers.',

	'Nested tables advice - success'          => 'Excelente, esta web no usa tablas.',
	'Nested tables advice - error'            => 'Atención! Intenta evitar el uso de tablas en HTML.',

	'Inline CSS advice - success'             => 'Perfecto. No se han declaro estilos embenidos (inline CSS) en tus etiquetas HTML!',
	'Inline CSS advice - error'               => 'Muy mal, tu web está usando estilos embenidos (inline CSS).',

	'CSS count advice - success'              => 'Genial, tu página web usa muy pocos ficheros CSS.',
	'CSS count advice - error'                => 'Muy mal, tu página web usa demasiados ficheros CSS (más de {MoreNr}).',

	'JS count advice - success'               => 'Perfecto, tu web usa pocos ficheros JavaScript.',
	'JS count advice - error'                 => 'Muy mal, tu sitio usa demasiados ficheros JavaScript (más de {MoreNr}).',

	'Deprecated advice - success'             => 'Genial, no hemos detectado ninguna etiqueta HTML obsoleta.',
	'Deprecated advice - error'               => 'Hemos detectado etiquetas HTML obsoletas que ya no se usa. Es recomendable que cambies esas etiquetas por otras similares.',

	// v 4.0
	'XML Sitemap - success'                   => '¡Perfecto! Su sitio tiene un mapa del sitio en XML.',
	'XML Sitemap - error'                     => 'Su sitio web no tiene un mapa del sitio XML. Esto puede traerle problemas. <br><br> Un mapa del sitio enumera las URL que pueden rastrearse y puede incluir información adicional, como las últimas actualizaciones de su sitio web, la frecuencia de cambios y la importancia de las URL. Esto permite a los motores de búsqueda rastrear el sitio web de una forma más exhaustiva.',

	'Robots txt - success'                    => '¡Estupendo! Su sitio web tiene un archivo robots.txt.',
	'Robots txt - error'                      => 'Su página web no tiene un archivo robots.txt. Esto puede traerle problemas. <br><br> Un fichero robots.txt permite restringir el acceso a los robots de los motores de búsqueda que rastrean la web y puede evitar que accedan a directorios y páginas determinadas. También especifica especifica la ubicación del archivo del mapa del sitio XML.',

	'Gzip - success'                          => 'Su sitio web se beneficia del tipo de compresión gzip. ¡Perfecto!',
	'Gzip - error'                            => 'Su sitio web no se beneficia de gzip. Intente implementarlo en su sitio web.',

	'Analytics - success'                     => '¡Perfecto! Su sitio web tiene una herramienta de análisis.',
	'Analytics - error'                       => 'No hemos encontrado ninguna herramienta de analítica en esta web. <br><br>La analítica Web le permite medir la actividad de los visitantes de su sitio web. Debería tener instalada al menos una herramienta de analítica y se recomienda instalar otra más para obtener una confirmación de los resultados.',
);
