<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment;

use mata\base\Module as BaseModule;

class Module extends BaseModule {

	/**
	 *  Name of the live environment
	 */ 
	public $liveEnvironment;

	/**
	 *  Name of the stage environment, which is not accessible by users who are not logged in.
	 */ 
	public $stageEnvironment;

	const DEFAULT_LIVE_ENVIRONMENT = "LIVE";
	const DEFAULT_STAGE_ENVIRONMENT = "DRAFT";
	const DEFAULT_SUPERSEDED_ENVIRONMENT = "SUPERSEDED";
	
	public function init() {

		if ($this->liveEnvironment == null)
			$this->liveEnvironment = self::DEFAULT_LIVE_ENVIRONMENT;
	}

	public function getNavigation() {
		return false;
	}

	public function getLiveEnvironment() {
		return $this->liveEnvironment ?: Module::DEFAULT_LIVE_ENVIRONMENT;
	}

	public function getStageEnvironment() {
		return $this->stageEnvironment ?: Module::DEFAULT_STAGE_ENVIRONMENT;		
	}

	public function getSupersededEnvironment() {
		return Module::DEFAULT_SUPERSEDED_ENVIRONMENT;		
	}

	public function hasEnvironmentBehavior($model) {
		foreach ($model->getBehaviors() as $behavior) {
			if (is_a($behavior, \matacms\environment\behaviors\EnvironmentBehavior::class))
				return true;
		}

		return false;
	}

}
