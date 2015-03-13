<?php

namespace matacms\environment\helpers; 

use yii\helpers\Html as BaseHtml;
use \matacms\environment\models\ItemEnvironment;
use yii\web\View;

class Html {

	public static function submitButton($content = 'Submit', $options = []) {

		$containerId = uniqid("form-submit");

		$retVal = BaseHtml::beginTag("div", [
			"id" => $containerId,
			"class" => "form-group"
			]);

		$retVal .= BaseHtml::hiddenInput(ItemEnvironment::REQ_PARAM_ITEM_ENVIRONMENT);

		$retVal .= BaseHtml::submitButton("Save", [
			"data-environment" => "PREVIEW", 
			"class" => "btn btn-primary"
			]);

		$retVal .= BaseHtml::submitButton("Publish", [
			"data-environment" => "LIVE",
			"class" => "btn btn-primary"
			]);

		$retVal .= BaseHtml::endTag("div");

		\Yii::$app->view->registerJs("
			$('#" . $containerId . " button').on('click', function() {
			    $(this).siblings('input:hidden').val($(this).attr('data-environment'))
			})
		", View::POS_READY);


		return $retVal;
	}

}