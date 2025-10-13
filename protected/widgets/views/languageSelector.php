<li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
		<?php echo Yii::t( 'app', 'Language'); ?> <b class="caret"></b>
	</a>
	<div class="dropdown-menu">
		<?php foreach ($languages as $lang_id => $language) : ?>
			<?php
			if ($lang_id == Yii::app()->language) {
                continue;
            }
			$url = Yii::app()->controller->createAbsoluteUrl( '', array_merge( $_GET, array( 'language' => $lang_id ) ) );
			?>
			<?php
			Yii::app()->clientScript->registerLinkTag(
				'alternate',
				null,
				$url,
				null,
				array(
				'hreflang' => $lang_id,
			)
				);
			?>
			<?php
			echo CHtml::link(
				$language,
				$url,
				array(
				'class' => 'dropdown-item',
			)
				)
			?>
		<?php endforeach; ?>
		<div class="dropdown-divider"></div>
		<a href="<?php echo Yii::app()->request->url; ?>" class="dropdown-item disabled">
			<?php echo Utils::v( $languages, Yii::app()->language, Yii::app()->language); ?>
		</a>
	</div>
</li>
