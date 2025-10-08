<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language ?>">

<head>
    <meta charset="utf-8">
    <title><?php echo $this->title ?></title>
    <script>
        if (top === self) {
            var newURL = 'https://vontainment.com/tools/seo-audit/';
            window.setTimeout(function() {
                top.location.href = newURL;
            }, 0);
        }
    </script>
    <link rel="icon" href="<?php echo Yii::app()->request->getBaseUrl(true) ?>/favicon.ico" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="vontainment.com">
    <meta name="dcterms.rightsHolder" content="vontainment.com">
    <meta name="dc.language" content="<?php echo Yii::app()->language ?>">
    <link href="<?php echo Yii::app()->request->getBaseUrl(true); ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->request->getBaseUrl(true); ?>/css/app.css" rel="stylesheet">

    <?php Yii::app()->clientScript->registerCoreScript('jquery') ?>
    <?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->getBaseUrl(true) . '/js/bootstrap.bundle.min.js') ?>
    <?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->getBaseUrl(true) . '/js/base.js') ?>
    <?php echo Yii::app()->params['template.head'] ?>
</head>

<body>

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


</body>

</html>