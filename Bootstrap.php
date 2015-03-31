<?php 

namespace matacms\environment;

use Yii;
use yii\base\Event;
use mata\base\MessageEvent;
use mata\arhistory\behaviors\HistoryBehavior;
use matacms\controllers\module\Controller;
use matacms\environment\models\ItemEnvironment;
use matacms\environment\Module;
use yii\db\BaseActiveRecord;

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {

		Event::on(HistoryBehavior::className(), HistoryBehavior::EVENT_REVISION_FETCHED, function(MessageEvent $event) {

			if ($this->shouldRun()) 
				$this->getPublishedRevision($event->getMessage());
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

		foreach ($model->getBehaviors() as $behavior) {
			if (is_a($behavior, \mata\arhistory\behaviors\HistoryBehavior::class)) {
				$model->setRevision($revision);
				break;
			}
		}
		
	}

	private function getPublishedRevision($model) {

		$module = \Yii::$app->getModule("environment");

		if ($module)
			$liveEnvironment = $module->liveEnvironment;
		else 
			$liveEnvironment = Module::DEFAULT_LIVE_ENVIRONMENT;

		// When logged into the CMS, latest version should be shown
		if (Yii::$app->user->isGuest == false)
			return;

		$ie = ItemEnvironment::find()->where([
			"DocumentId" => $model->DocumentId,
			"Status" => $liveEnvironment,
			])->orderBy("Revision DESC")->one();

		if ($ie) {
			$model->setRevision($ie->Revision);
		} else {
			foreach ($model->attributes() as $attribute)
				$model->setAttribute($attribute, null);
		}
	}

	private function processSave($model) {

		$status = Yii::$app->getRequest()->post(ItemEnvironment::REQ_PARAM_ITEM_ENVIRONMENT);

		if ($status == null)
			return;

		$ie = new ItemEnvironment();
		$ie->attributes = [
		"DocumentId" => $model->getDocumentId(),
		"Revision" => $model->getLatestRevision()->Revision,
		"Status" => $status
		];

		if (!$ie->save())
			throw new \yii\web\ServerErrorHttpException($ie->getTopError());

	}
}