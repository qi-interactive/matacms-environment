<?php 

namespace matacms\environment;

use Yii;
use yii\base\Event;
use mata\base\MessageEvent;
use mata\arhistory\behaviors\HistoryBehavior;
use matacms\controllers\module\Controller;
use matacms\environment\models\ItemEnvironment;
use matacms\environment\Module;

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

	}

	private function shouldRun() {
		return true;
	}

	private function getPublishedRevision($model) {


		$module = \Yii::$app->getModule("environment");

		if ($module)
			$liveEnvironment = $module->liveEnvironment;
		else 
			$module = Module::DEFAULT_LIVE_ENVIRONMENT;

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

		$ie = new ItemEnvironment();
		$ie->attributes = [
		"DocumentId" => $model->getDocumentId(),
		"Revision" => $model->getLatestRevision()->Revision,
		"Status" => "PREVIEW"
		];

		if (!$ie->save())
			throw new \yii\web\ServerErrorHttpException($ie->getTopError());


	}
}