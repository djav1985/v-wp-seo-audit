<?php /* @var $this Controller */ ?>
<!-- WordPress Plugin Layout - No <head> section needed -->
<div class="container mt-3">
    <div class="row">
        <div class="col">

        </div>
    </div>
</div>

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