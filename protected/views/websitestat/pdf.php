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
<h1 class="h-review"><?php echo 'Website review {Domain}'; ?></h1>
<i class="icon-time"></i>&nbsp;<small><?php echo 'Generated on'; ?> <?php
$monthNames = array('Jan' => 'January', 'Feb' => 'February', 'Mar' => 'March', 'Apr' => 'April', 'May' => 'May', 'Jun' => 'June', 'Jul' => 'July', 'Aug' => 'August', 'Sep' => 'September', 'Oct' => 'October', 'Nov' => 'November', 'Dec' => 'December');
$month = isset($monthNames[$generated['M']]) ? $monthNames[$generated['M']] : $generated['M'];
echo $month . ' ' . $generated['d'] . ' ' . $generated['Y'] . ' ' . $generated['H'] . ':' . $generated['i'] . ' ' . $generated['A'];
?>
</small><br/><br/>

<strong><?php echo 'The score is {Score}/100'; ?></strong>
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
echo 'Title advice - $advice';
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
echo 'Description advice - $advice';
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
<?php echo 'Og Meta Properties advice - $advice'; ?>
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
<?php echo 'We found {Count} images on this web page.'; ?>
<br/>
<br/>
<?php echo 'Image advice - $advice'; ?>
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
echo 'HTML ratio advice - $advice';
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
<?php echo 'Flash advice - $advice'; ?>
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
<?php echo 'Iframe advice - $advice'; ?>
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
<?php echo 'Friendly url advice - $advice'; ?>
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
<?php echo 'Underscore advice - $advice'; ?>
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
echo 'We found a total of {Links} links including {Files} link(s) to files';
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
<td><?php echo ($link['Type'] === 'internal' ? 'Internal' : 'External'); ?></td>
<td><?php echo ($link['Juice'] === 'nofollow' ? 'noFollow' : 'Passing Juice'); ?></td>
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
<?php echo 'Favicon advice - $advice'; ?>
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
<?php echo 'Language advice - $advice'; ?>
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
<?php echo 'Dublin Core advice - $advice'; ?>
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
<?php echo 'Encoding advice - $advice'; ?>
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
<?php echo 'Deprecated advice - $advice'; ?>
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
<td width="330px"><?php echo 'Nested tables advice - $advice'; ?></td>
</tr>

<tr class="odd">
<?php $advice = $rateprovider->addCompare( 'noInlineCSS', ! $isseter['inlinecss'] ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo (int) ! $isseter['inlinecss']; ?>.png" /></td>
<td><?php echo 'Inline CSS advice - $advice'; ?></td>
</tr>

<tr class="even">
<?php $advice = $rateprovider->addCompareArray( 'cssCount', $document['css'] ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
<td><?php echo 'CSS count advice - $advice'; ?></td>
</tr>

<tr class="odd">
<?php $advice = $rateprovider->addCompareArray( 'jsCount', $document['js'] ); ?>
<?php list($img_advice,) = explode( ' ', $advice ); ?>
<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
<td><?php echo 'JS count advice - $advice'; ?></td>
</tr>

<tr class="even">
	<?php $advice = $rateprovider->addCompare( 'hasGzip', $isseter['gzip'] ); ?>
	<?php list($img_advice,) = explode( ' ', $advice ); ?>
	<td><img src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/isset_<?php echo $img_advice === 'success' ? '1' : '0'; ?>.png" /></td>
	<td><?php echo 'Gzip - $advice'; ?></td>
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
		<?php echo 'XML Sitemap - $advice'; ?><br><br>

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
		<?php echo 'XML Sitemap - $advice'; ?>
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
		<?php echo 'Robots txt - $advice'; ?>
	<?php else : ?>
		<?php echo 'Missing'; ?>
<br><br>
		<?php echo 'Robots txt - $advice'; ?>
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
		<?php echo 'Analytics - $advice'; ?><br><br>
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
		<?php echo 'Analytics - $advice'; ?>
	<?php endif; ?>
</td>
</tr>
</tbody>
</table>
<?php endif; ?>
