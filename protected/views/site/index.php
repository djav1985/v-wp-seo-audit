<?php
/**
 * File: index.php
 *
 * @package V_WP_SEO_Audit
 */

?>
<!-- JS is enqueued via WordPress plugin file. Remove direct <script> and rely on enqueued assets. -->

<div class="jumbotron">
	<h1><?php echo Yii::app()->name; ?></h1>
	<p class="lead mb-4">
		<?php echo Yii::app()->name; ?> is a free SEO tool which provides you content analysis of the website.
	</p>
	<form id="website-form">
		<div class="form-row">
			<div class="form-group col-md-6">
				<div class="input-group mb-3">
					<input type="text"  name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo Yii::app()->params['param.placeholder']; ?>">
					<div class="input-group-append">
						<button class="btn btn-primary" type="button" id="submit">
							Analyze
						</button>
					</div>
				</div>

				<div class="alert alert-danger mb-0" id="errors" style="display: none"></div>

				<div class="clearfix"></div>

				<div id="progress-bar" class="progress" style="display: none">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
		</div>
	</form>
</div>

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
