<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment\helpers;

use Yii;
use yii\bootstrap\Modal;
use yii\helpers\Html as BaseHtml;
use yii\web\View;
use \matacms\environment\models\ItemEnvironment;
use matacms\environment\assets\MomentAsset;

class Html {

	public static function submitButton($content = 'Submit', $options = []) {

		if(!Yii::$app->user->identity->hasRoles())
			return self::submitButtonWithReview($content, $options);

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

	public static function submitButtonWithReview($content = 'Submit', $options = []) {

		$containerId = uniqid("form-submit");

		$retVal = BaseHtml::beginTag("div", [
			"id" => $containerId,
			"class" => "form-group submit-form-group"
			]);

		$retVal .= BaseHtml::hiddenInput(ItemEnvironment::REQ_PARAM_ITEM_ENVIRONMENT, null, ['id' => 'environment-hidden-input']);
		$retVal .= BaseHtml::hiddenInput('reviewer', null, ['id' => 'reviewer-hidden-input']);

		$retVal .= BaseHtml::submitButton("Save", [
			"data-environment" => Yii::$app->getModule("environment")->getStageEnvironment(),
			"class" => "btn btn-primary"
			]);

		$retVal .= BaseHtml::submitButton("SUBMIT FOR REVIEW", [
			"data-environment" => Yii::$app->getModule("environment")->getStageEnvironment(),
			"class" => "btn btn-primary review-btn"
			]);

		$retVal .= BaseHtml::endTag("div");

		$retVal .=  Modal::widget(['header' => '<h3>Choose Reviewer</h3>', 'id' => 'choose-reviewer-modal']);

		$formId = $options['formId'];

		\Yii::$app->view->registerJs("

			var form = $('#$formId'),
			isSubmitted = false,
			isReviewerSet = false,
			isReviewButtonClicked = false;



			var watchReviewButton = function() {
				var reviewerValue = $('#$containerId input#reviewer-hidden-input').val();
				var isReviewerEmpty = (reviewerValue === null || reviewerValue === undefined || reviewerValue == [] || reviewerValue === '');
				console.log('reviewerValue', reviewerValue);
				if(isReviewerEmpty) {
					console.log('Please set reviewer');

					$.ajax('/mata-cms/user/reviewer/get-reviewers?containerId=$containerId').done(function(data) {
						$('#choose-reviewer-modal .modal-body').html(data);
						$('#choose-reviewer-modal').modal('show');
					});

					return false;
				}
				else {
					return true;
				}
			}

			$('#" . $containerId . " button').on('click', function() {
				$(this).siblings('input#environment-hidden-input').val($(this).attr('data-environment'));
				isReviewButtonClicked = false;
			});

			$('#" . $containerId . " button.review-btn').on('click', function(e, data) {
				$(this).siblings('input#environment-hidden-input').val($(this).attr('data-environment'));
				isReviewButtonClicked = true;
				isReviewerSet = data.isReviewerSet;
			});

			form
			.on('beforeSubmit', function(e) {
				if(isReviewButtonClicked && form.data('yiiActiveForm').validated) {
					console.log('beforeSubmit', form.data('yiiActiveForm').validated)
					console.log('watchReviewButton', watchReviewButton())
					return watchReviewButton();
				}
				console.log('end of beforeSubmit');
			})
			.on('submit', function(e) {
				console.log('onSubmit')
				if(isReviewButtonClicked && !isReviewerSet) {
					console.log('review button clicked but reviewer not set')
					return false;
				}
				console.log('submit')
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
