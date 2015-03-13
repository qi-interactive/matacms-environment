<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace matacms\environment\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModuleAsset extends AssetBundle
{
	public $sourcePath = '@vendor/matacms/matacms-environment/web';

	public $js = [
	];

	public $depends = [
	];
}
