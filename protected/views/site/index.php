<?php
/**
 * File: index.php
 *
 * @package V_WP_SEO_Audit
 */

echo $this->renderPartial( '//site/request_form' ); ?>


<div class="row">
	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Content analysis'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/content.png" alt="<?php echo 'Content analysis'; ?>" />
		<p>
			<?php echo 'Marketing speak - content'; ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Meta Tags'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/tags.png" alt="<?php echo 'Meta Tags'; ?>" />
		<p>
			<?php echo 'Marketing speak - metatags'; ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Link Extractor'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/link.png" alt="<?php echo 'Link Extractor'; ?>" />
		<p>
			<?php echo 'Marketing speak - links'; ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Speed Test'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/speed.png" alt="<?php echo 'Speed Test'; ?>" />
		<p>
			<?php echo 'Marketing speak - speed test'; ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Get Advice'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/advice.png" alt="<?php echo 'Get Advice'; ?>" />
		<p>
			<?php echo 'Marketing speak - advice'; ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo 'Website Review'; ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/assets/img/review.png" alt="<?php echo 'Website Review'; ?>" />
		<p>
			<?php echo 'Marketing speak - review'; ?>
		</p>
	</div>
</div>

<?php if ( ! empty( $widget )) : ?>
	<hr>
	<h3 class="mb-4"><?php echo 'Latest Reviews'; ?></h3>
	<?php echo $widget; ?>
<?php endif; ?>
