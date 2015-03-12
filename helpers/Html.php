<?php

namespace matacms\environment\helpers; 

use yii\helpers\Html as BaseHtml;

class Html {

	public static function submitButton($content = 'Submit', $options = []) {
		return BaseHtml::submitButton($content, $options);
	}
	
}