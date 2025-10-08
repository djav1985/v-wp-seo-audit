<script type="text/javascript">
    "use strict";
function papulateErrors (obj, errors) {
	for(var e in errors) {
		if(typeof(errors[e]) == 'object')
			papulateErrors(obj, errors[e])
		else
			obj.append(errors[e] + '<br/>');
	}
}

function request() {
    var domain = $("#domain");
    domain.val(domain.val().replace(/^https?:\/\//i,'').replace(/\/$/i, ''));
	var data = $("#website-form").serialize(),
			button = $("#submit"),
			errObj = $("#errors");
	errObj.hide();
	errObj.html('');
	button.attr("disabled", true);

    $("#progress-bar").show();

    $.getJSON('<?php echo $this -> createUrl('parse/index') ?>', data, function(response) {
		button.attr("disabled", false);
        $("#progress-bar").hide();

		// If response's type is string then all is ok, redirect to statistics
		if(typeof(response) == 'string') {
			document.location.href = response;
			return true;
		}
		// If it's object, then display errors
		papulateErrors(errObj, response);
		errObj.show();
	}).error(function(xhr, ajaxOptions, thrownError) {
        $("#progress-bar").hide();

	    papulateErrors(errObj, {
	        'ajax': xhr.statusText + ': ' + xhr.responseText
        });
	    errObj.show();
        button.attr("disabled", false);
	});
}

$(document).ready(function() {
	$("#submit").click(function() {
		request();
		return false;
	});

	$("#website-form input").keypress(function(e) {
		if (e.keyCode === 13) {
			e.preventDefault();
			request();
			return false;
		}
	});
});
</script>

<div class="jumbotron">
    <h1><?php echo Yii::app() -> name ?></h1>
    <p class="lead mb-4">
        <?php echo Yii::t("app", "Marketing speak - header", array(
                "{Brandname}" => Yii::app() -> name)
        ) ?>
    </p>
    <form id="website-form">
        <div class="form-row">
            <div class="form-group col-md-6">
                <div class="input-group mb-3">
                    <input type="text"  name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo Yii::app()->params['param.placeholder'] ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="submit">
                            <?php echo Yii::t("app", "Analyze") ?>
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

