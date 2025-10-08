<?php
/**
 * @var $thumbnailStack array
 * @var $dataProvider CActiveDataProvider
 * @var $data array
 */
?>

<script type="text/javascript">
    "use strict";

    $(document).ready(function(){
        var urls = {
            <?php foreach($thumbnailStack as $id=>$thumbnail): ?>
            <?php echo $id ?>:<?php echo $thumbnail ?>,
            <?php endforeach; ?>
        };
        dynamicThumbnail(urls);
    });
</script>

<div class="row">
    <?php
    foreach ($data as $website):
        $url = Yii::app()->controller->createUrl("websitestat/generateHTML", array("domain"=>$website->domain));
        ?>
        <div class="col col-12 col-md-6 col-lg-4 mb-4">
            <div class="card mb-3">
                <h5 class="card-header"><?php echo Utils::cropDomain($website->idn) ?></h5>
                <a href="<?php echo $url ?>">
                    <img class="card-img-top" id="thumb_<?php echo $website->id ?>" src="<?php echo Yii::app() -> getBaseUrl(true) ?>/img/loader.gif" alt="<?php echo $website->idn ?>" />
                </a>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <p class="card-text">
                            <?php echo Yii::t("app", "The score is {Score}/100", array("{Score}" => $website -> score)) ?>
                        </p>
                        <a href="<?php echo $url ?>">
                            <div class="progress mb-3">
                                <div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $website -> score ?>%;"></div>
                            </div>
                        </a>

                    </li>
                </ul>

                <div class="card-body">
                    <a class="btn btn-primary" href="<?php echo $url ?>">
                        <?php echo Yii::t("app", "View analysis") ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<?php $this -> widget('LinkPager', array(
    'pages' => $dataProvider->getPagination(),
    'htmlOptions' => array(
        'class' => 'pagination flex-wrap',
    ),
    'prevPageLabel'=>false,
    'nextPageLabel'=>false,
    'firstPageCssClass'=>'page-item',
    'previousPageCssClass'=>'page-item',
    'internalPageCssClass'=>'page-item',
    'nextPageCssClass'=>'page-item',
    'lastPageCssClass'=>'page-item',
    'cssFile' => false,
    'header' => '',
    'hiddenPageCssClass' => 'disabled',
    'selectedPageCssClass' => 'active',
)); ?>


<div class="clearfix"></div>
