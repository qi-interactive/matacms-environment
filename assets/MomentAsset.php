<?php
 
/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment\assets;

use yii\web\AssetBundle;

class MomentAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/moment';

    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js = ['moment.js', 'min/locales.js'];
        } else {
            $this->js = ['min/moment-with-locales.min.js'];
        }
    }
    
}
