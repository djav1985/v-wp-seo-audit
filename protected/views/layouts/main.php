<?php /* @var $this Controller */ ?>
<!-- WordPress Plugin Layout - No <head> section needed, assets are enqueued via plugin file -->
<div class="container mt-3">
    <div class="row">
        <div class="col">
            <?php echo $content ?>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row">
        <div class="col">
            <?php echo Yii::app()->params['template.footer'] ?>
            <?php echo Yii::app()->params['pagepeeker.verify'] ?>
        </div>
    </div>
</div>