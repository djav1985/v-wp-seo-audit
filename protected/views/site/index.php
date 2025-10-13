<?php
/**
 * File: index.php
 *
 * @package V_WP_SEO_Audit
 */

echo $this->renderPartial( '//site/request_form' ); ?>


<div class="row">
	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Content analysis' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/content.png" alt="<?php echo Yii::t( 'app', 'Content analysis' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - content' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Meta Tags' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/tags.png" alt="<?php echo Yii::t( 'app', 'Meta Tags' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - metatags' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Link Extractor' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/link.png" alt="<?php echo Yii::t( 'app', 'Link Extractor' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - links' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Speed Test' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/speed.png" alt="<?php echo Yii::t( 'app', 'Speed Test' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - speed test' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Get Advice' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/advice.png" alt="<?php echo Yii::t( 'app', 'Get Advice' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - advice' ); ?>
		</p>
	</div>

	<div class="col-md-6 mb-3">
		<h5 class="mb-3"><?php echo Yii::t( 'app', 'Website Review' ); ?></h5>
		<img class="marketing-img float-left" src="<?php echo Yii::app()->getBaseUrl( true ); ?>/img/review.png" alt="<?php echo Yii::t( 'app', 'Website Review' ); ?>" />
		<p>
			<?php echo Yii::t( 'app', 'Marketing speak - review' ); ?>
		</p>
	</div>
</div>

<?php if ( ! empty( $widget )) : ?>
	<hr>
	<h3 class="mb-4"><?php echo Yii::t( 'app', 'Latest Reviews' ); ?></h3>
	<?php echo $widget; ?>
<?php endif; ?>
