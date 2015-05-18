<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment;

use Yii;
use yii\base\Event;
use mata\base\MessageEvent;
use mata\arhistory\behaviors\HistoryBehavior;
use mata\helpers\BehaviorHelper;
use matacms\controllers\module\Controller;
use matacms\environment\models\ItemEnvironment;
use matacms\environment\Module;
use yii\db\BaseActiveRecord;

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {
		Event::on(HistoryBehavior::className(), HistoryBehavior::EVENT_REVISION_FETCHED, function(MessageEvent $event) {
			if ($this->shouldRun())  {
				$this->getPublishedRevision($event->getMessage());
			}
		});

		Event::on(Controller::class, Controller::EVENT_MODEL_UPDATED, function(\matacms\base\MessageEvent $event) {
			$this->processSave($event->getMessage());
		});

		Event::on(Controller::class, Controller::EVENT_MODEL_CREATED, function(\matacms\base\MessageEvent $event) {
			$this->processSave($event->getMessage());
		});

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_AFTER_FIND, function(Event $event) {
			if (Yii::$app->getRequest()->get(ItemEnvironment::REQ_PARAM_REVISION)) {
				$model = $event->sender;
				$this->getRevision($model, Yii::$app->getRequest()->get(ItemEnvironment::REQ_PARAM_REVISION));
			}			
		});
	}

	private function shouldRun() {
		return true;
	}

	private function getRevision($model, $revision) {
		if(BehaviorHelper::hasBehavior($model, \mata\arhistory\behaviors\HistoryBehavior::class)) {
			$model->setRevision($revision);
		}
	}

	private function getPublishedRevision($model) {
		// When logged into the CMS, latest version should be shown
		if (Yii::$app->user->isGuest == false)
			return;

		$module = \Yii::$app->getModule("environment");

		if ($module == null)
			throw new \yii\base\InvalidConfigException("'environment' module pointing to matacms\\environment\\Module module needs to be set");

		if ($this->hasEnvironmentBehavior($model) == false)
			return;

		$liveEnvironment = $module->getLiveEnvironment();

		if ($model->getDocumentId()->getPk() == null) {
			\Yii::warning(sprintf("Trying to get environment for model without PK. Make sure you select it : %s", get_class($model)), 
				__METHOD__);
			return;
		}

		$ie = ItemEnvironment::find()->where([
			"DocumentId" => $model->getDocumentId()->getId(),
			"Status" => $liveEnvironment,
			])->orderBy("Revision DESC")->one();

		if ($ie) {
			$model->setRevision($ie->Revision);
		} else {
			foreach ($model->attributes() as $attribute)
				$model->setAttribute($attribute, null);

			$model->markForRemoval();
		}
	}

	private function hasEnvironmentBehavior($model) {
		$module = \Yii::$app->getModule("environment");
		if ($module == null)
			throw new \yii\base\InvalidConfigException("'environment' module pointing to matacms\\environment\\Module module needs to be set");

		return $module->hasEnvironmentBehavior($model);
	}
	
	private function processSave($model) {
		if (is_object($model) == false || $this->hasEnvironmentBehavior($model) == false)
			return;

		$status = Yii::$app->getRequest()->post(ItemEnvironment::REQ_PARAM_ITEM_ENVIRONMENT);

		if ($status == null)
			return;

		$module = \Yii::$app->getModule("environment");
		
		$liveEnvironment = $module->getLiveEnvironment();

		$supersededEnvironment = $module->getSupersededEnvironment();

		if ($status == $liveEnvironment) {
			ItemEnvironment::updateAll(['Status' => $supersededEnvironment], 'DocumentId = :documentId AND Status = :status', [':documentId' => $model->getDocumentId()->getId(), ':status' => $liveEnvironment]);
		}

		$ie = new ItemEnvironment();
		$ie->attributes = [
			"DocumentId" => $model->getDocumentId()->getId(),
			"Revision" => $model->getLatestRevision()->Revision,
			"Status" => $status
		];
		if (!$ie->save())
			throw new \yii\web\ServerErrorHttpException($ie->getTopError());
	}

}
