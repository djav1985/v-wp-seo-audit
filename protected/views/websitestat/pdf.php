<?php
/**
 * File: pdf.php
 * PDF view template for SEO audit report.
 *
 * @package V_WP_SEO_Audit
 */
?>
<style>
table {background-color: #ffffff;}
.table {width:546px !important;}
.table tr {border-bottom:5px solid #fff !important;}
.table-inner {width:350px !important;}
table td {padding:5px;margin:5px;}
a { color:#315D86; text-decoration: underline; }
.even { background-color:#fff;}
.odd { background-color:#f9f9f9;}
.header { font-size:14px; font-weight: bold; }
.suh-header { font-size: 12px; font-weight: bold; }
.td-icon { width:40px; }
.td-compare { width: 120px; }
.td-result { width:370px; }
.adv-icon { width:32px; height: 32px; padding:5px !important;}
.grade-1 {font-weight:300;font-size:12px;color:#191a1b;}
.grade-2 {font-weight:300;font-size:14px;color:#141415;}
.grade-3 {font-size:18px;color:#0f0f10;}
.grade-4 {font-size:20px;color:#315d86;}
.grade-5 {font-weight:600;font-size:24px;color:#315d86;}
.success { background-color: #dff0d8; }
.error { background-color: #f2dede; }
.warning { background-color: #fcf8e3; }
.icon-time { font-size:8px; }
.progress { background-color: #f7f7f7; }
.bar { background-color: #149bdf; }
</style>

<table class="table table-fluid">
<tr class="no-top-line">
<td>
<img class="thumbnail" id="thumb_<?php echo $website['id']; ?>" src="<?php echo $thumbnail; ?>" alt="<?php echo $website['idn']; ?>" />
</td>
<td>
<h1 class="h-review"><?php echo 'Website review for ' . CHtml::encode( $website['idn'] ); ?></h1>
<i class="icon-time"></i>&nbsp;<small><?php echo 'Generated on'; ?> <?php
$monthNames = array(
	'Jan' => 'January',
	'Feb' => 'February',
	'Mar' => 'March',
	'Apr' => 'April',
	'May' => 'May',
	'Jun' => 'June',
	'Jul' => 'July',
	'Aug' => 'August',
	'Sep' => 'September',
	'Oct' => 'October',
	'Nov' => 'November',
	'Dec' => 'December',
);
$month      = isset( $monthNames[ $generated['M'] ] ) ? $monthNames[ $generated['M'] ] : $generated['M'];
echo $month . ' ' . $generated['d'] . ' ' . $generated['Y'] . ' ' . $generated['H'] . ':' . $generated['i'] . ' ' . $generated['A'];
?>
</small><br/><br/>

<strong><?php echo 'The score is ' . (int) $website['score'] . '/100'; ?></strong>
<br/><br/>

<table width="180px" cellspacing="0" cellpadding="0">
<tr>
<td width="<?php echo $website['score']; ?>%" class="bar"></td>
<td class="progress"></td>
</tr>
</table>

</td>
</tr>
</table>

<br/>

<!-- SEO Content -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'SEO Content'; ?></h4><br/><br/></th>
</tr>
</thead>
<tbody>

<!-- Title -->
<?php $advice = $rateprovider->addCompareArray( 'title', mb_strlen( (string) $meta['title'] ) ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $img_advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Title'; ?>
</td>
<td class="td-result">
<?php echo CHtml::encode( $meta['title'] ); ?>
<br/><br/>
<strong><?php echo 'Length'; ?> : <?php echo mb_strlen( (string) $meta['title'] ); ?></strong>
<br/><br/>
<?php
$title_length = mb_strlen( (string) $meta['title'] );
if ( $advice === 'success' ) {
	echo 'Great! Your title tag has an optimal length (' . $title_length . ' characters).';
} elseif ( $advice === 'warning' ) {
	echo 'Your title tag length (' . $title_length . ' characters) could be improved. Aim for 10-70 characters.';
} else {
	echo 'Your title tag needs attention. Current length is ' . $title_length . ' characters. Optimal length is 10-70 characters.';
}
?>
</td>
</tr>

<!-- Description -->
<?php $advice = $rateprovider->addCompareArray( 'description', mb_strlen( (string) $meta['description'] ) ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $img_advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Description'; ?>
</td>
<td>
<?php echo CHtml::encode( $meta['description'] ); ?>
<br/><br/>
<strong><?php echo 'Length'; ?> : <?php echo mb_strlen( (string) $meta['description'] ); ?></strong>
<br/><br/>
<?php
$desc_length = mb_strlen( (string) $meta['description'] );
if ( $advice === 'success' ) {
	echo 'Perfect! Your meta description has an optimal length (' . $desc_length . ' characters).';
} elseif ( $advice === 'warning' ) {
	echo 'Your meta description length (' . $desc_length . ' characters) could be improved. Aim for 70-160 characters.';
} else {
	echo 'Your meta description needs attention. Current length is ' . $desc_length . ' characters. Optimal length is 70-160 characters.';
}
?>
?>
</td>
</tr>

<!-- Og properties -->
<?php $advice = $rateprovider->addCompare( 'ogmetaproperties', ! empty( $meta['ogproperties'] ) ); ?>
<tr class="<?php echo $advice; ?>">

<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>

<td class="td-compare" align="center" valign="middle">
<?php echo 'Og Meta Properties'; ?>
</td>

<td>
<?php
if ( $advice === 'success' ) {
	echo 'Great! Your page has Open Graph meta properties for better social media sharing.';
} else {
	echo 'Your page is missing Open Graph meta properties. Adding these tags helps control how your content appears when shared on social media.';
}
?>
<br/><br/>
<?php if ( ! empty( $meta['ogproperties'] )) : ?>
<table class="table table-striped table-fluid table-inner" cellpadding="5">
<tr nobr="true" class="odd">
<td width="100px"><span class="suh-header"><?php echo 'Property'; ?></span><br/><br/></td>
<td width="250px"><span class="suh-header"><?php echo 'Content'; ?></span><br/><br/></td>
</tr>
	<?php
	$i = 0;
	foreach ($meta['ogproperties'] as $property => $c) :
		$even = $i % 2 === 0;
		?>
<tr nobr="true" class="<?php echo $even ? 'even' : 'odd'; ?>">
<td><?php echo CHtml::encode( $property ); ?></td>
<td><?php echo CHtml::encode( $c ); ?></td>
</tr>
		<?php
		$i++;
	endforeach;
	?>
</table>
<?php endif; ?>
</td>

</tr>

<!-- Headings -->
<tr class="odd">
<td>
<br/><br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Headings'; ?>
</td>
<td>

<table class="table table-inner table-striped table-fluid">
<tbody>
<tr class="no-top-line">
<?php foreach ($content['headings'] as $heading => $headings) : ?>
<td><strong><?php echo strtoupper( $heading ); ?></strong></td>
<?php endforeach; ?>
</tr>
<tr>
<?php foreach ($content['headings'] as $headings) : ?>
<td><?php echo count( $headings ); ?></td>
<?php endforeach; ?>
</tr>
</tbody>
</table>

<?php
if ($content['isset_headings']) :
	$i = 0;
	?>
<ul id="headings">
	<?php
	foreach ($content['headings'] as $heading => $headings) :
		if ( ! empty( $headings )) :
			foreach ($headings as $h) :
				$i++;
				?>
<li>[<?php echo mb_strtoupper( (string) $heading ); ?>] <?php echo CHtml::encode( $h ); ?></li>
				<?php
			endforeach;
		endif;
	endforeach;
	?>
</ul>
<?php endif; ?>

</td>
</tr>

<!-- Images -->
<?php $advice = $rateprovider->addCompare( 'imgHasAlt', $content['total_img'] === $content['total_alt'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Images'; ?>
</td>
<td>
<?php echo 'We found ' . (int) $content['total_img'] . ' images on this web page.'; ?>
<br/>
<br/>
<?php
if ( $advice === 'success' ) {
	echo 'Excellent! All images have alt attributes, which is great for SEO and accessibility.';
} else {
	echo 'Some images are missing alt attributes. Alt text is important for SEO and accessibility. ' . (int) $content['total_alt'] . ' out of ' . (int) $content['total_img'] . ' images have alt attributes.';
}
?>
</td>
</tr>

<!-- Text/HTML Ratio -->
<?php $advice = $rateprovider->addCompareArray( 'htmlratio', $document['htmlratio'] ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $img_advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Text/HTML Ratio'; ?>
</td>
<td>
<?php echo 'Ratio'; ?> : <strong><?php echo $document['htmlratio']; ?>%</strong>
<br/>
<br/>
<?php
if ( $advice === 'success' ) {
	echo 'Good! Your page has a healthy text to HTML ratio (' . $document['htmlratio'] . '%). This means your page has a good balance of content to code.';
} elseif ( $advice === 'warning' ) {
	echo 'Your text/HTML ratio (' . $document['htmlratio'] . '%) could be improved. Aim for a ratio between 10-25% for better SEO.';
} else {
	echo 'Your text/HTML ratio (' . $document['htmlratio'] . '%) is too low. Consider adding more content or reducing HTML markup for better SEO.';
}
?>
</td>
</tr>

<!-- Flash -->
<?php $advice = $rateprovider->addCompare( 'noFlash', ! $isseter['flash'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Flash'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Great! Your page does not use Flash, which is good for SEO and modern web standards.';
} else {
	echo 'Your page uses Flash content. Flash is obsolete and not supported by most modern browsers and devices. Consider using HTML5 alternatives.';
}
?>
</td>
</tr>

<!-- Iframe -->
<?php $advice = $rateprovider->addCompare( 'noIframe', ! $isseter['iframe'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare" align="center" valign="middle">
<?php echo 'Iframe'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Good! Your page does not use iframes, which is better for SEO and page performance.';
} else {
	echo 'Your page uses iframes. While sometimes necessary, iframes can negatively impact SEO and page load times.';
}
?>
</td>
</tr>

</tbody>
</table>

<br/><br/><br/>


<!-- SEO Links -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'SEO Links'; ?></h4></th>
</tr>
</thead>
<tbody>
<!-- Friendly url -->
<?php $advice = $rateprovider->addCompare( 'isFriendlyUrl', $links['friendly'] ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
<?php echo 'URL Rewrite'; ?>
</td>
<td class="td-result">
<?php
if ( $advice === 'success' ) {
	echo 'Perfect! Your URLs are SEO-friendly and do not contain query strings or dynamic parameters.';
} else {
	echo 'Your URLs contain query strings or dynamic parameters. Consider using URL rewriting to create cleaner, more SEO-friendly URLs.';
}
?>
</td>
</tr>

<!-- Underscore -->
<?php $advice = $rateprovider->addCompare( 'noUnderScore', ! $links['isset_underscore'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Underscores in the URLs'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Great! Your URLs do not contain underscores, which is better for SEO. Search engines prefer hyphens over underscores.';
} else {
	echo 'Your URLs contain underscores. Consider using hyphens instead of underscores in URLs for better SEO, as search engines treat hyphens as word separators.';
}
?>
</td>
</tr>

<!-- In-page links -->
<?php $advice = $rateprovider->addCompare( 'issetInternalLinks', $links['internal'] > 0 ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'In-page links'; ?>
</td>
<td>
<?php
$file_links = 0;
foreach ( $links['links'] as $link ) {
	if ( ! empty( $link['Link'] ) && preg_match( '/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar|txt|csv)$/i', $link['Link'] ) ) {
		$file_links++;
	}
}
$total_links = count( $links['links'] );
echo 'We found a total of ' . $total_links . ' links including ' . $file_links . ' link(s) to files';
?>

</td>
</tr>

<!-- Statistic -->
<tr class="odd">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Statistics'; ?>
</td>
<td>
<?php echo 'External Links'; ?> : <?php echo 'noFollow'; ?> <?php echo Utils::proportion( $linkcount, $links['external_nofollow'] ); ?>%<br/><br/>
<?php echo 'External Links'; ?> : <?php echo 'Passing Juice'; ?> <?php echo Utils::proportion( $linkcount, $links['external_dofollow'] ); ?>%<br/><br/>
<?php echo 'Internal Links'; ?> <?php echo Utils::proportion( $linkcount, $links['internal'] ); ?>%
</td>
</tr>
</tbody>
</table>

<br/><br/><br/>

<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'In-page links'; ?></h4></th>
</tr>
</thead>
<tbody>

<tr class="odd">
<td width="60%"><span class="suh-header"><?php echo 'Anchor'; ?></span></td>
<td width="20%"><span class="suh-header"><?php echo 'Type'; ?></span></td>
<td width="20%"><span class="suh-header"><?php echo 'Juice'; ?></span></td>
</tr>
<?php
$i = 0;
foreach ($links['links'] as $link) :
	$even = $i % 2 === 0;
	?>
<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
<td>
<a href="<?php echo $link['Link']; ?>" target="_blank">
	<?php echo ! empty( $link['Name'] ) ? CHtml::encode( $link['Name'] ) : '-'; ?>
</a>
</td>
<td><?php echo ( $link['Type'] === 'internal' ? 'Internal' : 'External' ); ?></td>
<td><?php echo ( $link['Juice'] === 'nofollow' ? 'noFollow' : 'Passing Juice' ); ?></td>
</tr>
	<?php
	$i++;
endforeach;
?>
</tbody>
</table>

<br><br><br>

<!-- SEO Keywords -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'SEO Keywords'; ?></h4></th>
</tr>
</thead>
<tbody>
<!-- Tag cloud -->
<tr class="odd">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
<?php echo 'Keywords Cloud'; ?>
</td>
<td class="cloud-container td-result">
<?php foreach ($cloud['words'] as $word => $stat) : ?>
<span class="grade-<?php echo (int) $stat['grade']; ?>"><?php echo CHtml::encode( $word ); ?></span>
<?php endforeach; ?>
</td>
</tr>
</tbody>
</table>

<br/><br/><br/>

<!-- SEO Keywords -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="6" align="center"><h4 class="header"><?php echo 'Keywords Consistency'; ?></h4></th>
</tr>
</thead>
<tbody>
<tr class="odd">
<td width="20%"><span class="suh-header"><?php echo 'Keyword'; ?></span></td>
<td width="15%"><span class="suh-header"><?php echo 'Content'; ?></span></td>
<td width="15%"><span class="suh-header"><?php echo 'Title'; ?></span></td>
<td width="15%"><span class="suh-header"><?php echo 'Keywords'; ?></span></td>
<td width="15%"><span class="suh-header"><?php echo 'Description'; ?></span></td>
<td width="15%"><span class="suh-header"><?php echo 'Headings'; ?></span></td>
</tr>
<?php
$i = 0;
foreach ($cloud['matrix'] as $word => $object) :
	$even = $i % 2 === 0;
	?>
<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
<td><?php echo CHtml::encode( $word ); ?></td>
<td><?php echo (int) $cloud['words'][ $word ]['count']; ?></td>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $object['title']; ?>.png" /></td>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $object['keywords']; ?>.png" /></td>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $object['description']; ?>.png" /></td>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $object['headings']; ?>.png" /></td>
</tr>
	<?php
	$i++;
endforeach;
?>
</tbody>
</table>

<br/><br/><br/>

<!-- USability -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'Usability'; ?></h4></th>
</tr>
</thead>
<tbody>

<!-- Url -->
<tr class="odd">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
<?php echo 'Url'; ?>
</td>
<td class="td-result">
<?php echo 'Domain'; ?> : <?php echo $website['idn']; ?>
<br />
<?php echo 'Length'; ?> : <?php echo mb_strlen( $website['idn'] ); ?>
</td>
</tr>

<!-- Favicon -->
<?php $advice = $rateprovider->addCompare( 'issetFavicon', ! empty( $document['favicon'] ) ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Favicon'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Excellent! Your website has a favicon, which helps with branding and user experience.';
} else {
	echo 'Your website is missing a favicon. A favicon helps with branding and makes your site more recognizable in browser tabs and bookmarks.';
}
?>
</td>
</tr>

<!-- Language -->
<?php $advice = $rateprovider->addCompare( 'lang', $document['lang'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Language'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Great! Your page has a language attribute declared, which helps search engines understand your content.';
} else {
	echo 'Your page is missing a language attribute. Adding a language attribute to your HTML tag helps search engines and screen readers.';
}
?>
</td>
</tr>

<!-- Dublin Core -->
<?php $advice = $rateprovider->addCompare( 'lang', $isseter['dublincore'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Dublin Core'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Good! Your page uses Dublin Core metadata, which can help with content categorization and discovery.';
} else {
	echo 'Your page does not use Dublin Core metadata. While not essential, Dublin Core can help with content categorization in digital libraries and archives.';
}
?>
</td>
</tr>

</tbody>
</table>

<br/><br/><br/>

<!-- Document -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'Document'; ?></h4></th>
</tr>
</thead>
<tbody>

<!-- Doctype -->
<?php $advice = $rateprovider->addCompare( 'doctype', $document['doctype'] ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
<?php echo 'Doctype'; ?>
</td>
<td class="td-result">
<?php
if ($document['doctype']) :
	echo $document['doctype'];
else :
	echo 'Missing doctype';
endif;
?>
</td>
</tr>

<!-- Encoding -->
<?php $advice = $rateprovider->addCompare( 'charset', $document['charset'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Encoding'; ?>
</td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Perfect! Your page specifies a character encoding, which is essential for proper text display.';
} else {
	echo 'Your page is missing a character encoding declaration. This can lead to display issues with special characters. Add a charset meta tag.';
}
?>
</td>
</tr>

<!-- W3C Validity -->
<?php $advice = $rateprovider->addCompare( 'w3c', $w3c['valid'] ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'W3C Validity'; ?>
</td>
<td>
<?php echo 'Errors'; ?> : <?php echo (int) $w3c['errors']; ?>
<br/>
<?php echo 'Warnings'; ?> : <?php echo (int) $w3c['warnings']; ?>
</td>
</tr>

<!-- Deprecated -->
<?php $advice = $rateprovider->addCompare( 'noDeprecated', empty( $content['deprecated'] ) ); ?>
<tr class="<?php echo $advice; ?>">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Deprecated HTML'; ?>
</td>
<td width="70%">
<?php if ( ! empty( $content['deprecated'] )) : ?>
<table class="table table-striped table-fluid table-inner" cellpadding="5">
<tr class="odd">
<td align="center"><span class="suh-header"><?php echo 'Deprecated tags'; ?></span></td>
<td align="center"><span class="suh-header"><?php echo 'Occurrences'; ?></span></td>
</tr>
	<?php
	$i = 0;
	foreach ($content['deprecated'] as $tag => $count) :
		$even = $i % 2 === 0;
		?>
<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
<td align="center"><?php echo htmlspecialchars( '<' . $tag . '>' ); ?></td>
<td align="center"><?php echo $count; ?></td>
</tr>
		<?php
		$i++;
	endforeach;
	?>
</table>
<?php endif; ?>
<?php
if ( $advice === 'success' ) {
	echo 'Excellent! Your page does not use deprecated HTML tags.';
} else {
	echo 'Your page uses deprecated HTML tags. Consider updating to modern HTML5 elements for better compatibility and standards compliance.';
}
?>
</td>
</tr>

<!-- Speed Tips -->
<tr class="odd">
<td>
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
<?php echo 'Speed Tips'; ?>
</td>
<td>

<table cellspacing="3" cellpadding="5">
<tbody>

<tr class="no-top-line even">
<?php $advice = $rateprovider->addCompare( 'noNestedtables', ! $isseter['nestedtables'] ); ?>
<td width="20px"><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['nestedtables']; ?>.png" /></td>
<td width="330px">
<?php
if ( $advice === 'success' ) {
	echo 'Good! No nested tables found. Nested tables can slow down page rendering.';
} else {
	echo 'Your page uses nested tables, which can slow down page rendering. Consider using CSS for layout instead.';
}
?>
</td>
</tr>

<tr class="odd">
<?php $advice = $rateprovider->addCompare( 'noInlineCSS', ! $isseter['inlinecss'] ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['inlinecss']; ?>.png" /></td>
<td>
<?php
if ( $advice === 'success' ) {
	echo 'Perfect! No inline CSS found. External stylesheets are better for performance and maintainability.';
} else {
	echo 'Your page uses inline CSS. Consider moving styles to external stylesheets for better performance and caching.';
}
?>
</td>
</tr>

<tr class="even">
<?php $advice = $rateprovider->addCompareArray( 'cssCount', $document['css'] ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
<td>
<?php
$css_count = is_array( $document['css'] ) ? count( $document['css'] ) : 0;
if ( $advice === 'success' ) {
	echo 'Great! Your page has an optimal number of CSS files (' . $css_count . '). Keep stylesheets minimal for better performance.';
} else {
	echo 'Your page has ' . $css_count . ' CSS files. Too many CSS files can slow down page load. Consider combining them.';
}
?>
</td>
</tr>

<tr class="odd">
<?php $advice = $rateprovider->addCompareArray( 'jsCount', $document['js'] ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
<td>
<?php
$js_count = is_array( $document['js'] ) ? count( $document['js'] ) : 0;
if ( $advice === 'success' ) {
	echo 'Excellent! Your page has an optimal number of JavaScript files (' . $js_count . '). Keep scripts minimal for better performance.';
} else {
	echo 'Your page has ' . $js_count . ' JavaScript files. Too many JS files can slow down page load. Consider combining them.';
}
?>
</td>
</tr>

<tr class="even">
	<?php $advice = $rateprovider->addCompare( 'hasGzip', $isseter['gzip'] ); ?>
	<?php list($img_advice,) = explode( ' ', $advice ); ?>
	<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
	<td>
		<?php
		echo 'Gzip Compression';
		if ( $advice === 'success' ) {
			echo ' - Enabled! Your server is using Gzip compression to reduce file sizes and improve page load times.';
		} else {
			echo ' - Not detected. Enable Gzip compression to reduce file sizes and improve page load speed.';
		}
		?>
	</td>
</tr>

</tbody>
</table>

</td>
</tr>

</tbody>
</table>

<br/><br/><br/>


<!-- Mobile Optimization -->
<!-- Document -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'Mobile'; ?></h4></th>
</tr>
</thead>
<tbody>
<!-- Mobile Optimization -->
<tr class="odd">
<td class="td-icon">
<br/>
<img src = "<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/neutral.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
<?php echo 'Mobile Optimization'; ?>
</td>
<td class="td-result">

<table cellspacing="3" cellpadding="5">
<tbody>

<tr class="even">
<td width="20px"><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $isseter['appleicons']; ?>.png" /></td>
<td width="330px"><?php echo 'Apple Icon'; ?></td>
</tr>

<tr class="odd">
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) $isseter['viewport']; ?>.png" /></td>
<td><?php echo 'Meta Viewport Tag'; ?></td>
</tr>

<tr class="even">
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['flash']; ?>.png" /></td>
<td><?php echo 'Flash content'; ?></td>
</tr>

</tbody>
</table>

</td>
</tr>

</tbody>
</table>

<?php if ($misc) : ?>
<br/><br/><br/>

<!-- Optimization -->
<table class="table table-striped table-fluid" cellspacing="3" cellpadding="5">
<thead>
<tr>
<th colspan="3" align="center"><h4 class="header"><?php echo 'Optimization'; ?></h4></th>
</tr>
</thead>
<tbody>

<!-- Sitemap -->
	<?php $advice = $rateprovider->addCompare( 'hasSitemap', ! empty( $misc['sitemap'] ) ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="td-compare">
	<?php echo 'XML Sitemap'; ?>
</td>
<td class="td-result">
	<?php if ( ! empty( $misc['sitemap'] )) : ?>
		<?php
		echo 'Great! We found an XML sitemap on your website. Sitemaps help search engines discover and index your pages more efficiently.';
		?>
<br><br>

<table class="table table-striped table-fluid table-inner" cellpadding="5">
		<?php
		$i = 0;
		foreach ($misc['sitemap'] as $sitemap) :
			$even = $i % 2 === 0;
			?>
<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
<td><?php echo CHtml::encode( $sitemap ); ?></td>
</tr>
			<?php
			$i++;
		endforeach;
		?>
</table>
	<?php else : ?>
		<?php echo 'Missing'; ?>
<br><br>
		<?php echo 'Your website does not have an XML sitemap. Creating and submitting a sitemap helps search engines discover and index all your important pages.'; ?>
	<?php endif; ?>
</td>
</tr>

<!-- Robots -->
	<?php $advice = $rateprovider->addCompare( 'hasRobotsTxt', $isseter['robotstxt'] ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<br/>
<img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
	<?php echo 'Robots.txt'; ?>
</td>
<td class="td-result">
	<?php if ($isseter['robotstxt']) : ?>
		<?php echo 'http://' . $website['domain'] . '/robots.txt'; ?>
<br><br>
		<?php
		echo 'Great! Your website has a robots.txt file. This helps search engines understand which pages to crawl and index.';
		?>
	<?php else : ?>
		<?php echo 'Missing'; ?>
<br><br>
		<?php echo 'Your website does not have a robots.txt file. While not always required, a robots.txt file helps control how search engines access your site.'; ?>
	<?php endif; ?>
</td>
</tr>

<!-- Analytics support -->
	<?php $advice = $rateprovider->addCompare( 'hasAnalytics', ! empty( $misc['analytics'] ) ); ?>
<tr class="<?php echo $advice; ?>">
<td class="td-icon">
<img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/<?php echo $advice; ?>.png" width="32px" height="32px" class="adv-icon" align="middle"/>
</td>
<td class="compare">
	<?php echo 'Analytics'; ?>
</td>
<td class="td-result">
	<?php if ( ! empty( $misc['analytics'] )) : ?>
		<?php
		echo 'Great! We detected analytics tracking on your website. Analytics help you understand visitor behavior and improve your site.';
		?>
<br><br>
<table class="table table-striped table-fluid table-inner" cellpadding="5">
		<?php
		$i = 0;
		foreach ($misc['analytics'] as $analytics) :
			$even = $i % 2 === 0;
			?>
<tr class="<?php echo $even ? 'even' : 'odd'; ?>">
<td>
<img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/analytics/<?php echo $analytics; ?>.png" />
&nbsp;&nbsp;
			<?php echo CHtml::encode( AnalyticsFinder::getProviderName( $analytics ) ); ?>
</td>
</tr>
			<?php
			$i++;
		endforeach;
		?>
</table>
	<?php else : ?>
		<?php echo 'Missing'; ?>
<br><br>
		<?php echo 'Your website does not have analytics tracking installed. Consider adding analytics to gain insights into your visitors and improve your site performance.'; ?>
	<?php endif; ?>
</td>
</tr>
</tbody>
</table>
<?php endif; ?>
