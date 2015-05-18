<?php

namespace matacms\environment\helpers; 

use Yii;
use yii\helpers\Html as BaseHtml;
use \matacms\environment\models\ItemEnvironment;
use yii\web\View;
use matacms\environment\assets\MomentAsset;

class Html {

	public static function submitButton($content = 'Submit', $options = []) {

		$containerId = uniqid("form-submit");

		$retVal = BaseHtml::beginTag("div", [
			"id" => $containerId,
			"class" => "form-group submit-form-group"
			]);

		$retVal .= BaseHtml::hiddenInput(ItemEnvironment::REQ_PARAM_ITEM_ENVIRONMENT);

		$retVal .= BaseHtml::submitButton("Save", [
			"data-environment" => Yii::$app->getModule("environment")->getStageEnvironment(), 
			"class" => "btn btn-primary"
			]);

		$retVal .= BaseHtml::submitButton("Publish", [
			"data-environment" => Yii::$app->getModule("environment")->getLiveEnvironment(),
			"class" => "btn btn-primary publish-btn"
			]);

		$retVal .= BaseHtml::endTag("div");

		MomentAsset::register(\Yii::$app->controller->View);

		$formId = $options['formId'];

		\Yii::$app->view->registerJs("
			
			var UTCDate = function() {
				return new Date(Date.UTC.apply(Date, arguments));
			}
			
			var UTCToday = function() {
				var today = new Date();
				return UTCDate(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate(), today.getUTCHours(), today.getUTCMinutes(), today.getUTCSeconds(), 0);
			}

			var setButtons = function(publicationDateField) {
				var dateValue = publicationDateField.val();
					
				var isEmpty = (dateValue === null || dateValue === undefined || dateValue == [] || dateValue === '');
				
				if(isEmpty) {
					$('#" . $containerId . " button[data-environment=\"LIVE\"').hide();
				} else {

					var isFutureDate = moment(dateValue).format('YYYY-MM-DD HH:mm') > moment().format('YYYY-MM-DD HH:mm');

					if(isFutureDate) {
						$('#" . $containerId . " button[data-environment=\"LIVE\"').removeClass('publish-btn').addClass('schedule-btn').text('SCHEDULE').show();
					} else {
						$('#" . $containerId . " button[data-environment=\"LIVE\"').removeClass('schedule-btn').addClass('publish-btn').text('PUBLISH').show();
					}							
				}
			}
			
			var watchSubmitButtons = function() {
				var publicationDateField = $('[id*=\"publicationdate\"]');

				if(publicationDateField !== null || publicationDateField !== undefined) {
	
					publicationDateField.on('change dp.change', function() {
						console.log('change');
						setButtons(publicationDateField);
					});
				}
			}

			watchSubmitButtons();
			setButtons($('[id*=\"publicationdate\"]'));			

			$('#" . $containerId . " button').on('click', function() {
				$(this).siblings('input:hidden').val($(this).attr('data-environment'));
			});


			
			var form = $('#$formId'),
			isSubmitted = false;

			form.on('submit', function(e) {
				console.log(0, isSubmitted);
				if(!isSubmitted) {
					isSubmitted = form.yiiActiveForm('submitForm');
					return isSubmitted;
				}	
				$('#$containerId button').attr('disabled', 'disabled');
				return false;			
			});
		", View::POS_READY);


		return $retVal;
	}

}