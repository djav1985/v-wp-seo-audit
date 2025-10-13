<?php

return array(
	'Title advice - success'                  => 'Perfeito, o Título contém entre {1} e {2} caracteres.',
	'Title advice - warning'                  => 'Idealmente, o Título deve conter entre {1} e {2} caracteres (incluíndo espaços).',
	'Title advice - error'                    => 'Mau. Não encontrámos nenhum título na página.',

	'Description advice - success'            => 'Perfeito, a Descrição META contém entre {1} e {2} caracteres.',
	'Description advice - warning'            => 'Idealmente, a Descrição META deve conter entre {1} e {2} caracteres (incluíndo espaços).',
	'Description advice - error'              => 'Mau. Não encontrámos nenhuma Descrição META na sua página.',

	'Friendly url advice - success'           => 'Perfeito. As ligações aparentam ser limpas!',
	'Friendly url advice - error'             => 'Mau. Existem ligações que contêm \'query strings\' (sequências de consulta).',

	'Underscore advice - success'             => 'Perfeito. Não foram encontrados \'underscores\' (traços inferiores) nas suas URLs.',
	'Underscore advice - error'               => 'Detetámos \'underscores\' (traços inferiores) nas suas URLs. O uso hífens é mais eficiente em termos de otimização SEO.',

	'Keywords advice - success'               => 'Perfeito, a página contém palavras-chave META.',
	'Keywords advice - error'                 => 'Mau. Não detetámos palavras-chave META na sua página.',

	'Image advice - success'                  => 'Bom, a maioria das imagens têm o atributo ALT definidos.',
	'Image advice - error'                    => '{Number} atributos ALT estão vazios ou em falta. É recomendado adicionar texto alternativo de modo a que os motores de busca identifiquem melhor o conteúdo das suas imagens.',

	'HTML ratio advice - success'             => 'Bom! O rácio de texto para código HTML desta página é maior que {BadNr}, mas menor que {GoodNr} porcento.',
	'HTML ratio advice - success ideal_ratio' => 'Ideal! O rácio de texto para código HTML desta página está entre {GoodNr} e {BestNr} porcento.',
	'HTML ratio advice - error more_than'     => 'O rácio de texto para código HTML desta página é maior que {BestNr} porcento, o que significa que existe um risco elevado de ser considerada SPAM.',
	'HTML ratio advice - error less_than'     => 'O rácio de texto para código HTML desta página é menor que {BadNr} porcento, o que significa que provavelmente é necessário de adicionar mais conteúdos em forma de texto.',

	'Flash advice - success'                  => 'Perfeito, não foi encontrado conteúdo Flash nesta página.',
	'Flash advice - error'                    => 'Terrível, esta página tem conteúdo em Flash, o que significa que os motores de busca terão mais dificuldades em entender o conteúdo da sua página.',

	'Iframe advice - success'                 => 'Excelente, não foram detetadas Iframes nesta página.',
	'Iframe advice - error'                   => 'Oh, não, esta página tem Iframes na página, o que significa que o conteúdo destas não pode ser indexado.',

	'Favicon advice - success'                => 'Ótimo, o site tem um favicon.',
	'Favicon advice - error'                  => 'Oh, não! Não encontrámos nenhum favicon. Os favicon são umas das formas mais fáceis de atraír visitantes regulares para qualquer site, uma vez que eles o tornam distintivo.',

	'Printability advice - success'           => 'Fantástico. Encontrámos CSS apropriado para impressão.',
	'Printability advice - error'             => 'Não encontrámos CSS apropriado para impressão.',

	'Language advice - success'               => 'Otimo! A língua declarada deste site é {Language}.',
	'Language advice - error'                 => 'Não foi declarada nenhuma língua para este site.',

	'Dublin Core advice - success'            => 'Boa! Esta página tira vantagens do Dublin Core.',
	'Dublin Core advice - error'              => 'Esta página não tira vantagens do Dublin Core.',

	'Og Meta Properties advice - success'     => 'Boa! Esta página tira vantagens das propriedades Og.',
	'Og Meta Properties advice - error'       => 'Esta página não tira vantagens das propriedades Og.',

	'Encoding advice - success'               => 'Perfeito. O conjunto de caracteres {Charset} está declarado.',
	'Encoding advice - error'                 => 'Não está declarado nenhum conjunto de caracteres.',

	'Email Privacy advice - success'          => 'Boa! Nenhum endereço de email está declarado sob a forma de texto!',
	'Email Privacy advice - error'            => 'Aviso! No mínimo, foi encontrado um endereço de email sob a forma de texto. Isto é um convite para que spammers entupam a caixa de correio deste endereço.',

	'Nested tables advice - success'          => 'Excelente, este site não usa tablelas dentro de tabelas.',
	'Nested tables advice - error'            => 'Atenção! Existem tabelas dentro de outras tabelas em HTML.',

	'Inline CSS advice - success'             => 'Perfeito. Não foram detetados estilos CSS nas etiquetas HTML!',
	'Inline CSS advice - error'               => 'Oh não, o site usa estilos CSS nas etiquetas HTML.',

	'CSS count advice - success'              => 'Boa, o site usa poucos ficheiros CSS.',
	'CSS count advice - error'                => 'Oh, não! O site utiliza demasiados ficheiros CSS (mais que {MoreNr}).',

	'JS count advice - success'               => 'Perfeito, o site usa poucos ficheiros JavaScript.',
	'JS count advice - error'                 => 'Oh, não! O site utiliza demasiados ficheiros JavaScript (mais que {MoreNr}).',

	'Deprecated advice - success'             => 'Fantástico! Não detetámos etiquetas HTML obsoletas.',
	'Deprecated advice - error'               => 'Etiquetas HTML obsoletas são etiquetas que já não são usadas segundo as normas mais recentes. É recomendado que sejam removidas ou substituídas por outras etiquetas atualizadas.',

	// v 4.0
	'XML Sitemap - success'                   => 'Perfeito, o site tem um mapa XML do site (sitemap).',
	'XML Sitemap - error'                     => 'O site não tem um mapa XML do site (sitemap) - isto pode ser problemático.<br><br>Um mapa do site identifica todas as URLs que estão disponíveis para rastreio, incluindo informação acerca de atualizações, frequência de alterações ou a importancia de cada URL. Isto contribui para uma maior inteligência e eficiência do rastreio.',

	'Robots txt - success'                    => 'Perfeito, o seu site tem um ficheiro robots.txt.',
	'Robots txt - error'                      => 'O site não tem um ficheiro robots.txt  - isto pode ser problemático. <br><br>Um ficheiro robots.txt permite restringir o acesso por parte dos robots de motores de busca a certas partes do site, o que é importante por motivos de segurança ou de otimização da indexação. Pode igualmente indicar aos motores de busca onde está o ficheiro XML com o mapa do site.',

	'Gzip - success'                          => 'Perfeito, o site tira vantagens da compressão gzip.',
	'Gzip - error'                            => 'Atenção, o site não tira vantagem da compressão gzip.',

	'Analytics - success'                     => 'Perfeito, o site tem uma ferramenta analítica para a análise de atividade.',
	'Analytics - error'                       => 'Não detetámos nenhuma ferramenta analítica de análise de atividade. <br><br>Este tipo de ferramentas (como por exemplo o Google Analytics) permite perceber o comportamento dos visitantes e o tipo de atividade que fazem. No mínimo, uma ferramenta deve estar instalada, sendo que em algumas situações mais do que uma pode ser útil.',
);
