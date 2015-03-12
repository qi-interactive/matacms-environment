<?php

/*
 * This file is part of the mata project.
 *
 * (c) mata project <http://github.com/mata/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace matacms\environment;

use mata\base\Module as BaseModule;

/**
 * This is the main module class for the Yii2-user.
 *
 * @property array $modelMap
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Module extends BaseModule {

	/**
	 *  Name of the live environment
	 */ 
	public $liveEnvironment;

	const DEFAULT_LIVE_ENVIRONMENT = "LIVE";


	public function init() {

		if ($this->liveEnvironment == null)
			$this->liveEnvironment = self::DEFAULT_LIVE_ENVIRONMENT;
	}

	public function getNavigation() {
		return false;
	}
}