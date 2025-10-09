<?php if ( empty( $website ) || ! is_array( $website ) ) : ?>
    <div class="alert alert-danger mt-5 mb-5">
        <?php echo Yii::t( 'app', 'No report available. The domain could not be analyzed or the record was not created. Please try again or check your domain input.' ); ?>
    </div>
    <?php return; ?>
<?php endif; ?>
<script type="text/javascript">
    "use strict";

    jQuery(function($) {
        dynamicThumbnail({
            <?php echo 'main_' . $website['id']; ?>: <?php echo $thumbnail; ?>
        });

        var pie_data = [];
		pie_data[0] = {
			label: '<?php echo Yii::t( 'app', 'External Links'); ?> : <?php echo Yii::t( 'app', 'nofollow'); ?> <?php echo Utils::proportion( $linkcount, $links['external_nofollow']); ?>%',
			data: <?php echo $links['external_nofollow']; ?>,
			color: '#6A93BA'
		};
		pie_data[1] = {
			label: '<?php echo Yii::t( 'app', 'External Links'); ?> : <?php echo Yii::t( 'app', 'dofollow'); ?> <?php echo Utils::proportion( $linkcount, $links['external_dofollow']); ?>%',
			data: <?php echo $links['external_dofollow']; ?>,
			color: '#315D86'
		};
		pie_data[2] = {
			label: '<?php echo Yii::t( 'app', 'Internal Links'); ?> <?php echo Utils::proportion( $linkcount, $links['internal']); ?>%',
			data: <?php echo $links['internal']; ?>,
			color: '#ddd'
		};

		drawPie();
		window.onresize = function(event) {
			drawPie();
		};

		function drawPie() {
			$.plot($("#links-pie"), pie_data, {
				series: {
					pie: {
						show: true
					}
				},
				legend: {
					container: "#legend"
				}
			});
		}

		$('.collapse-task').click(function() {
			var p = $(this).parent(".task-list");
			p.find(".over-max").hide();
			$(this).hide();
			p.find('.expand-task').show();
		});

		$('.expand-task').click(function() {
			var p = $(this).parent(".task-list");
			p.find(".over-max").show();
			$(this).hide();
			p.find('.collapse-task').show();
		});

		$('#update_stat').click(function() {
			var href = $(this).attr("href");
			if (href.indexOf("#") < 0) {
				return true;
			}
			$('#domain').val('<?php echo $website['domain']; ?>');
		});

                $('body').on("click", ".pdf_review", function() {
			$(this).hide();
			$(this).closest(".form-container").find(".download_form").fadeIn();
			return false;
		});

		<?php if ( Yii::app()->params['psi.show'] ) : ?>
			WrPsi(
			<?php
			echo CJSON::encode(
				array(
						'i18nEnterFullscreen' => Yii::t( 'app', 'Enter fullscreen mode' ),
						'i18nExitFullscreen'  => Yii::t( 'app', 'Exit fullscreen mode' ),
						'runInstantly'        => Yii::app()->params['psi.run_instantly'],
						'url'                 => ! empty( $website['final_url'] ) ? $website['final_url'] : 'http://' . $website['domain'],
						'locale'              => Yii::app()->language,
					)
				  )
			?>
					);
		<?php endif; ?>
        });
</script>

<div class="jumbotron">
	<div class="row">
		<div class="col-md-4 col-lg-5 col-sm-12">
			<img class="img-responsive img-thumbnail mb-20" id="thumb_main_<?php echo $website['id']; ?>" src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/loader.gif" alt="<?php echo $website['idn']; ?>" />
		</div>
		<div class="col-md-8 col-lg-7 col-sm-12 text-left">
			<h1 class="text-break">
				<?php echo Yii::t( 'app', 'Analyse of {Domain}', array( '{Domain}' => $website['idn'] )); ?>
			</h1>

			<p>
				<i class="fas fa-clock"></i>&nbsp;<small><?php echo Yii::t( 'app', 'Generated on'); ?> <?php
				echo Yii::t(
					'app',
					'Generated format',
					array(
																											'{Month}' => Yii::t( 'app', $generated['M'] ),
																											'{Day}' => $generated['d'],
																											'{Year}' => $generated['Y'],
																											'{Hour}' => $generated['H'],
																											'{Minute}' => $generated['i'],
																											'{Ante}' => $generated['A'],
																										)
					);
				?>
				</small>
			</p>


			<?php if ( $diff > Yii::app()->params['analyzer.cache_time'] ) : ?>
				<p>
					<?php
					echo Yii::t(
						'app',
						'Old statistics? UPDATE!',
						array(
						'{UPDATE}' => '<a href="' . $updUrl . '" class="btn btn-success" id="update_stat">' . Yii::t( 'app', 'UPDATE' ) . '</a>',
					)
						)
					?>
				</p>
			<?php endif; ?>

			<?php echo Yii::app()->params['param.addthis']; ?>


			<p class="mt-3">
				<strong><?php echo Yii::t( 'app', 'The score is {Score}/100', array( '{Score}' => $website['score'] )); ?></strong>
			</p>
			<div class="progress-score progress mb-3">
				<div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $website['score']; ?>%;"></div>
			</div>

			<a href="https://vontainment.com/scripts/seo/websitestat/generatePDF?domain=<?php echo $website['domain']; ?>" class="btn btn-primary">
				<?php echo Yii::t( 'app', 'Download PDF version' ); ?>
			</a>

		</div>
	</div>
</div>


<h3 id="section_content" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'SEO Content'); ?></h3>
<div class="category-wrapper">
	<!-- Title -->
	<?php $advice = $rateprovider->addCompareArray( 'title', mb_strlen( Utils::html_decode( $meta['title'] ) ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Title'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break">
				<?php echo CHtml::encode( Utils::html_decode( $meta['title'] )); ?>
			</p>
			<p>
				<strong>
					<?php echo Yii::t( 'app', 'Length'); ?> : <?php echo mb_strlen( Utils::html_decode( $meta['title'] ) ); ?>
				</strong>
			</p>
			<p>
				<?php
				echo Yii::t(
					'advice',
					"Title advice - $advice",
					array(
				'{1}' => _RATE_TITLE_GOOD,
				'{2}' => _RATE_TITLE_BEST,
				)
					);
				?>
			</p>
		</div>
	</div>

	<!-- Description -->
	<?php $advice = $rateprovider->addCompareArray( 'description', mb_strlen( Utils::html_decode( $meta['description'] ) ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Description'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break">
				<?php echo CHtml::encode( Utils::html_decode( $meta['description'] )); ?>
			</p>
			<p>
				<strong>
					<strong><?php echo Yii::t( 'app', 'Length'); ?> : <?php echo mb_strlen( Utils::html_decode( $meta['description'] ) ); ?></strong>
				</strong>
			</p>
			<p>
				<?php
				echo Yii::t(
					'advice',
					"Description advice - $advice",
					array(
				'{1}' => _RATE_DESC_GOOD,
				'{2}' => _RATE_DESC_BEST,
				)
					);
				?>
			</p>
		</div>
	</div>

	<!-- Og properties -->
	<?php $advice = $rateprovider->addCompare( 'ogmetaproperties', ! empty( $meta['ogproperties'] ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Og Meta Properties'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Og Meta Properties advice - $advice" ); ?>
			</p>

			<?php if (  ! empty( $meta['ogproperties'] ) ) : ?>
				<div class="table-responsive table-items mb-3 task-list">
					<table class="table table-striped">
						<thead>
							<tr>
								<th><?php echo Yii::t( 'app', 'Property'); ?></th>
								<th><?php echo Yii::t( 'app', 'Content'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $meta['ogproperties'] as $property => $c ) : ?>
								<tr class="over-max">
									<td><?php echo CHtml::encode( $property); ?></td>
									<td class="text-break"><?php echo CHtml::encode( $c); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<button class="expand-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Expand'); ?></button>
					<button class="collapse-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Collapse'); ?></button>
				</div>
			<?php endif; ?>
		</div>
	</div>


	<!-- Headings -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Headings'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive table-items mb-3">
				<table class="table table-striped table-fluid">
					<tbody>
						<tr class="no-top-line">
							<?php foreach ( $content['headings'] as $heading => $headings ) : ?>
								<td><strong><?php echo strtoupper( $heading); ?></strong></td>
							<?php endforeach; ?>
						</tr>
						<tr>
							<?php foreach ( $content['headings'] as $headings ) : ?>
								<td><span class="badge badge-success badge-heading"><?php echo count( $headings); ?></span> </td>
							<?php endforeach; ?>
						</tr>
					</tbody>
				</table>
			</div>

			<?php
			if ( $content['isset_headings'] ) :
				$i = 0;
				?>
				<div class="task-list">
					<ul id="headings">
						<?php
						foreach ( $content['headings'] as $heading => $headings ) :
							if (  ! empty( $headings ) ) :
								foreach ( $headings as $h ) :
									$i++;
									?>
									<li class="text-break<?php echo $i > $over_max ? ' over-max' : ''; ?>">[<?php echo mb_strtoupper( $heading); ?>] <?php echo CHtml::encode( Utils::html_decode( $h )); ?></li>
									<?php
								endforeach;
							endif;
						endforeach;
						?>
					</ul>
					<?php if ( $i > $over_max ) : ?>
						<button class="expand-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Expand'); ?></button>
						<button class="collapse-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Collapse'); ?></button>
					<?php endif; ?>
				</div>

			<?php endif; ?>
		</div>
	</div>

	<!-- Images -->
	<?php $advice = $rateprovider->addCompare( 'imgHasAlt', $content['total_img'] == $content['total_alt'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Images'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'app', 'We found {Count} images on this web page.', array( '{Count}' => $content['total_img'] )); ?>
			</p>
			<p>
				<?php echo Yii::t( 'advice', "Image advice - $advice", array( '{Number}' => $content['total_img'] - $content['total_alt'] ) ); ?>
			</p>
		</div>
	</div>

	<!-- Text/HTML Ratio -->
	<?php $advice = $rateprovider->addCompareArray( 'htmlratio', $document['htmlratio'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Text/HTML Ratio'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'app', 'Ratio'); ?> : <strong><?php echo $document['htmlratio']; ?>%</strong>
			</p>
			<p>
				<?php
				echo Yii::t(
					'advice',
					"HTML ratio advice - $advice",
					array(
					'{GoodNr}' => _RATE_HRATIO_GOOD,
					'{BestNr}' => _RATE_HRATIO_BEST,
					'{BadNr}'  => _RATE_HRATIO_BAD,
				)
					);
				?>
			</p>
		</div>
	</div>

	<!-- Flash -->
	<?php $advice = $rateprovider->addCompare( 'noFlash', ! $isseter['flash'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Flash'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Flash advice - $advice" ); ?>
			</p>
		</div>
	</div>

	<!-- Iframe -->
	<?php $advice = $rateprovider->addCompare( 'noIframe', ! $isseter['iframe'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Iframe'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Iframe advice - $advice" ); ?>
			</p>
		</div>
	</div>
</div>


<h3 id="section_links" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'SEO Links'); ?></h3>
<div class="category-wrapper">
	<!-- Friendly url -->
	<?php $advice = $rateprovider->addCompare( 'isFriendlyUrl', $links['friendly'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'URL Rewrite'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Friendly url advice - $advice" ); ?>
			</p>
		</div>
	</div>

	<!-- Underscore -->
	<?php $advice = $rateprovider->addCompare( 'noUnderScore', ! $links['isset_underscore'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Underscores in the URLs'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Underscore advice - $advice" ); ?>
			</p>
		</div>
	</div>

	<!-- In-page links -->
	<?php $advice = $rateprovider->addCompare( 'issetInternalLinks', $links['internal'] > 0 ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'In-page links'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="mb-3">
				<?php
				echo Yii::t(
					'app',
					'We found a total of {Links} links including {Files} link(s) to files',
					array(
					'{Links}' => $linkcount,
					'{Files}' => $links['files_count'],
				)
					);
				?>
			</p>
			<div class="row">
				<div class="col-md-4">
					<div id="links-pie" style="height: 200px"></div>
				</div>
				<div class="col-md-8 mt-3 mt-md-0" id="legend"></div>
			</div>


			<div class="table-responsive table-items mt-3 task-list">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo Yii::t( 'app', 'Anchor'); ?></th>
							<th><?php echo Yii::t( 'app', 'Type'); ?></th>
							<th><?php echo Yii::t( 'app', 'Juice'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ( $links['links'] as $link ) :
							$i++;
							?>
							<tr <?php echo $i > $over_max ? 'class="over-max"' : null; ?>>
								<td class="text-break">
									<a href="<?php echo $link['Link']; ?>" target="_blank" rel="nofollow">
										<?php echo ! empty( $link['Name'] ) ? CHtml::encode( Utils::html_decode( $link['Name'] ) ) : Yii::t( 'app', '-'); ?>
									</a>
								</td>
								<td><?php echo Yii::t( 'app', $link['Type']); ?></td>
								<td><?php echo Yii::t( 'app', $link['Juice']); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $i > $over_max ) : ?>
					<button class="expand-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Expand'); ?></button>
					<button class="collapse-task btn btn-primary float-right"><?php echo Yii::t( 'app', 'Collapse'); ?></button>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>



<h3 id="section_keywords" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'SEO Keywords'); ?></h3>
<div class="category-wrapper">
	<!-- Tag cloud -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Keywords Cloud'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p class="text-break cloud-container">
				<?php foreach ( $cloud['words'] as $word => $stat ) : ?>
					<span class="grade-<?php echo $stat['grade']; ?>"><?php echo CHtml::encode( Utils::html_decode( $word )); ?></span>
				<?php endforeach; ?>
			</p>
		</div>
	</div>

	<!-- Keywords Consistency -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Keywords Consistency'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<thead class="thead-dark">
						<tr>
							<th><?php echo Yii::t( 'app', 'Keyword'); ?></th>
							<th><?php echo Yii::t( 'app', 'Content'); ?></th>
							<th><?php echo Yii::t( 'app', 'Title'); ?></th>
							<th><?php echo Yii::t( 'app', 'Description'); ?></th>
							<th><?php echo Yii::t( 'app', 'Headings'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cloud['matrix'] as $word => $object ) : ?>
							<tr>
								<td><?php echo CHtml::encode( $word); ?></td>
								<td><?php echo (int) $cloud['words'][ $word ]['count']; ?></td>
								<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) $object['title']; ?>.png" /></td>
								<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) $object['description']; ?>.png" /></td>
								<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) $object['headings']; ?>.png" /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<h3 id="section_usability" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'Usability'); ?></h3>
<div class="category-wrapper">
	<!-- Url -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Url'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'app', 'Domain'); ?> : <?php echo $website['idn']; ?>
			</p>
			<p>
				<?php echo Yii::t( 'app', 'Length'); ?> : <?php echo mb_strlen( $website['idn']); ?>
			</p>
		</div>
	</div>

	<!-- Favicon -->
	<?php $advice = $rateprovider->addCompare( 'issetFavicon', ! empty( $document['favicon'] ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Favicon'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Favicon advice - $advice" ); ?>
			</p>
		</div>
	</div>

	<!-- Language -->
	<?php $advice = $rateprovider->addCompare( 'lang', $document['lang'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Language'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Language advice - $advice", array( '{Language}' => '<strong>' . $document['lang'] . '</strong>' ) ); ?>
			</p>
		</div>
	</div>

	<!-- Dublin Core -->
	<?php $advice = $rateprovider->addCompare( 'lang', $isseter['dublincore'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Dublin Core'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Dublin Core advice - $advice" ); ?>
			</p>
		</div>
	</div>
</div>


<h3 id="section_document" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'Document'); ?></h3>
<div class="category-wrapper">
	<!-- Doctype -->
	<?php $advice = $rateprovider->addCompare( 'doctype', $document['doctype'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Doctype'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php
				if ( $document['doctype'] ) :
					echo $document['doctype'];
				else :
					echo Yii::t( 'app', 'Missing doctype' );
				endif;
				?>
			</p>
		</div>
	</div>

	<!-- Encoding -->
	<?php $advice = $rateprovider->addCompare( 'charset', $document['charset'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Encoding'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'advice', "Encoding advice - $advice", array( '{Charset}' => $document['charset'] ) ); ?>
			</p>
		</div>
	</div>

	<!-- W3C Validity -->
	<?php $advice = $rateprovider->addCompare( 'w3c', $w3c['valid'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'W3C Validity'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<p>
				<?php echo Yii::t( 'app', 'Errors'); ?> : <strong><?php echo (int) $w3c['errors']; ?></strong>
			</p>
			<p>
				<?php echo Yii::t( 'app', 'Warnings'); ?> : <strong><?php echo (int) $w3c['warnings']; ?></strong>
			</p>
		</div>
	</div>

	<!-- Deprecated -->
	<?php $advice = $rateprovider->addCompare( 'noDeprecated', empty( $content['deprecated'] ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Deprecated HTML'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<?php if (  ! empty( $content['deprecated'] ) ) : ?>
					<table class="table table-striped table-items">
						<thead>
							<tr>
								<th><?php echo Yii::t( 'app', 'Deprecated tags'); ?></th>
								<th><?php echo Yii::t( 'app', 'Occurrences'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $content['deprecated'] as $tag => $count ) : ?>
								<tr>
									<td><?php echo htmlspecialchars( '<' . $tag . '>'); ?></td>
									<td><?php echo $count; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<p>
				<?php echo Yii::t( 'advice', "Deprecated advice - $advice" ); ?>
			</p>
		</div>
	</div>

	<!-- Speed Tips -->
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Speed Tips'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<tbody>
						<tr>
							<?php $advice = $rateprovider->addCompare( 'noNestedtables', ! $isseter['nestedtables'] ); ?>
							<td width="50px"><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) ! $isseter['nestedtables']; ?>.png" /></td>
							<td><?php echo Yii::t( 'advice', "Nested tables advice - $advice" ); ?></td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompare( 'noInlineCSS', ! $isseter['inlinecss'] ); ?>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) ! $isseter['inlinecss']; ?>.png" /></td>
							<td><?php echo Yii::t( 'advice', "Inline CSS advice - $advice" ); ?></td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompareArray( 'cssCount', $document['css'] ); ?>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo $advice == 'success' ? '1' : '0'; ?>.png" /></td>
							<td><?php echo Yii::t( 'advice', "CSS count advice - $advice", array( '{MoreNr}' => _RATE_CSS_COUNT ) ); ?></td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompareArray( 'jsCount', $document['js'] ); ?>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo $advice == 'success' ? '1' : '0'; ?>.png" /></td>
							<td><?php echo Yii::t( 'advice', "JS count advice - $advice", array( '{MoreNr}' => _RATE_JS_COUNT ) ); ?></td>
						</tr>

						<tr>
							<?php $advice = $rateprovider->addCompare( 'hasGzip', $isseter['gzip'] ); ?>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo $advice == 'success' ? '1' : '0'; ?>.png" /></td>
							<td><?php echo Yii::t( 'advice', "Gzip - $advice" ); ?></td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<h3 id="section_mobile" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'Mobile'); ?></h3>
<div class="category-wrapper">
	<div class="row pt-3 pb-3 row-advice">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-neutral"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Mobile Optimization'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<div class="table-responsive">
				<table class="table table-striped">
					<tbody>

						<tr class="no-top-line">
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) $isseter['appleicons']; ?>.png" /></td>
							<td><?php echo Yii::t( 'app', 'Apple Icon' ); ?></td>
						</tr>

						<tr>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) $isseter['viewport']; ?>.png" /></td>
							<td><?php echo Yii::t( 'app', 'Meta Viewport Tag' ); ?></td>
						</tr>

						<tr>
							<td><img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/isset_<?php echo (int) ! $isseter['flash']; ?>.png" /></td>
							<td><?php echo Yii::t( 'app', 'Flash content' ); ?></td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

<h3 id="section_optimization" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'Optimization'); ?></h3>
<div class="category-wrapper">
	<!-- Sitemap -->
	<?php $advice = $rateprovider->addCompare( 'hasSitemap', ! empty( $misc['sitemap'] ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'XML Sitemap'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if (  ! empty( $misc['sitemap'] ) ) : ?>
				<p>
					<?php echo Yii::t( 'advice', "XML Sitemap - $advice" ); ?>
				</p>
				<div class="table-responsive">
					<table class="table table-striped table-items">
						<tbody>
							<?php foreach ( $misc['sitemap'] as $sitemap ) : ?>
								<tr>
									<td class="text-break">
										<?php echo CHtml::encode( $sitemap ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

			<?php else : ?>
				<p>
					<strong><?php echo Yii::t( 'app', 'Missing' ); ?></strong>
				</p>
				<p>
					<?php echo Yii::t( 'advice', "XML Sitemap - $advice" ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Robots -->
	<?php $advice = $rateprovider->addCompare( 'hasRobotsTxt', $isseter['robotstxt'] ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Robots.txt'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if ( $isseter['robotstxt'] ) : ?>
				<p><?php echo 'http://' . $website['domain'] . '/robots.txt'; ?></p>
				<p><?php echo Yii::t( 'advice', "Robots txt - $advice" ); ?></p>
			<?php else : ?>
				<p><strong><?php echo Yii::t( 'app', 'Missing' ); ?></strong></p>
				<p><?php echo Yii::t( 'advice', "Robots txt - $advice" ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Analytics support -->
	<?php $advice = $rateprovider->addCompare( 'hasAnalytics', ! empty( $misc['analytics'] ) ); ?>
	<div class="row pt-3 pb-3 row-advice row-advice-<?php echo $advice; ?>">
		<div class="col-md-4">
			<div class="float-left mr-3 mr-md-5 adv-icon adv-icon-<?php echo $advice; ?>"></div>
			<p class="lead">
				<?php echo Yii::t( 'app', 'Analytics'); ?>
			</p>
		</div>
		<div class="col-md-8">
			<?php if (  ! empty( $misc['analytics'] ) ) : ?>
				<p><?php echo Yii::t( 'advice', "Analytics - $advice" ); ?></p>
				<div class="table-responsive">
					<table class="table table-striped table-items">
						<tbody>
							<?php foreach ( $misc['analytics'] as $analytics ) : ?>
								<tr>
									<td>
										<img src="<?php echo Yii::app()->getBaseUrl( true); ?>/img/analytics/<?php echo $analytics; ?>.png" />
										&nbsp;&nbsp;
										<?php echo CHtml::encode( AnalyticsFinder::getProviderName( $analytics ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p><strong><?php echo Yii::t( 'app', 'Missing' ); ?></strong></p>
				<p><?php echo Yii::t( 'advice', "Analytics - $advice" ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ( Yii::app()->params['psi.show'] ) : ?>
	<h4 id="section_page_speed" class="mt-5 mb-3"><?php echo Yii::t( 'app', 'Page speed'); ?></h4>
	<div class="category-wrapper">
		<div class="row pagespeed">
			<div class="col-md-6 mb-3">
				<h5><?php echo Yii::t( 'app', 'Device' ); ?></h5>

				<div class="form-check">
					<input type="radio" name="psi__strategy" id="psi_strategy_desktop" class="form-check-input" value="desktop" <?php echo Utils::isPsiActive( 'device', 'desktop' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_strategy_desktop">
						<?php echo Yii::t( 'app', 'Desktop' ); ?>
					</label>
				</div>

				<div class="form-check">
					<input type="radio" name="psi__strategy" id="psi_strategy_mobile" class="form-check-input" value="mobile" <?php echo Utils::isPsiActive( 'device', 'mobile' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_strategy_mobile">
						<?php echo Yii::t( 'app', 'Mobile' ); ?>
					</label>
				</div>
			</div>

			<div class="col-md-6 mb-3">
				<h5><?php echo Yii::t( 'app', 'Categories' ); ?></h5>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_performance" data-psi-category="performance" value="performance" <?php echo Utils::isPsiActive( 'categories', 'performance' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_performance">
						<?php echo Yii::t( 'app', 'Performance' ); ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_accessibility" data-psi-category="accessibility" value="accessibility" <?php echo Utils::isPsiActive( 'categories', 'accessibility' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_accessibility">
						<?php echo Yii::t( 'app', 'Accessibility' ); ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_bestpr" data-psi-category="best-practices" value="best-practices" <?php echo Utils::isPsiActive( 'categories', 'best-practices' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_bestpr">
						<?php echo Yii::t( 'app', 'Best Practices' ); ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_seo" data-psi-category="seo" value="seo" <?php echo Utils::isPsiActive( 'categories', 'seo' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_seo">
						<?php echo Yii::t( 'app', 'SEO' ); ?>
					</label>
				</div>

				<div class="form-check">
					<input type="checkbox" class="form-check-input" id="psi_category_pwa" data-psi-category="pwa" value="pwa" <?php echo Utils::isPsiActive( 'categories', 'pwa' ) ? ' checked' : null; ?>>
					<label class="form-check-label" for="psi_category_pwa">
						<?php echo Yii::t( 'app', 'PWA' ); ?>
					</label>
				</div>
			</div>
			<button class="psi__analyze-btn btn btn-primary mt-3">
				<?php echo Yii::t( 'app', 'Analyze' ); ?>
			</button>
		</div>

		<div class="row">
			<div class="col">
				<div class="psi__iframe-wrapper"></div>
			</div>
		</div>
	<?php endif; ?>
	</div>
	<div class="mt-5" id="update_form">
		<?php echo $this->renderPartial( '//site/request_form' ); ?>
	</div>
