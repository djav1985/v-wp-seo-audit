<!-- JS is enqueued via WordPress plugin file. Remove direct <script> and rely on enqueued assets. -->

<div class="jumbotron">
	<h1><?php echo Yii::app()->name; ?></h1>
	<p class="lead mb-4">
		<?php
		echo Yii::t(
            'app',
            'Marketing speak - header',
            array(
                '{Brandname}' => Yii::app()->name,
        )
        )
        ?>
    </p>
    <form id="website-form">
        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="input-group mb-3">
                    <input type="text"  name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo Yii::app()->params['param.placeholder']; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="submit">
                            <?php echo Yii::t('app', 'Analyze' ); ?>
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

