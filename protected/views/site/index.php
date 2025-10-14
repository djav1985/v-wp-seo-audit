<?php
/**
 * File: index.php
 *
 * @package V_WP_SEO_Audit
 */

echo $this->renderPartial( '//site/request_form' ); ?>


<div class="row">
	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Content analysis</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/content.png" alt="Content analysis" />
		<p>
			View a content  analysis. Check your text/html ratio, headings and etc.
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Meta Tags</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/tags.png" alt="Meta Tags" />
		<p>
			Get full list of meta-tags from web page. View site's title, keywords, og properties and more.
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Link Extractor</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/link.png" alt="Link Extractor" />
		<p>
			Extract links from your website with anchors, url and find out internal and external links percentage.
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Speed Test</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/speed.png" alt="Speed Test" />
		<p>
			Speed-up your website load time by finding most slowest page's parts.
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Get Advice</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/advice.png" alt="Get Advice" />
		<p>
			The system automatically shows you vulnerabilities and gives advice.
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3">Website Review</h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/review.png" alt="Website Review" />
		<p>
			Get a full website review absolutely free.
		</p>
	</div>
</div>

<?php if ( ! empty( $widget )) : ?>
	<hr>
	<h3 class="mb-4">Latest Reviews</h3>
	<?php echo $widget; ?>
<?php endif; ?>
