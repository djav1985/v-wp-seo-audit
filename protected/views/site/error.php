<?php
/**
 * File: error.php
 *
 * @package V_WP_SEO_Audit
 */
/*
 @var $this SiteController */
/* @var $error array */
?>
<h1>
	<?php echo 404 === $code ? Yii::t( 'app', 'Page not found' ) : Yii::t( 'app', 'Error {ErrorNo}', array( '{ErrorNo}' => $code ) ); ?>
</h1>
<p><?php echo CHtml::encode( $message ); ?></p>

