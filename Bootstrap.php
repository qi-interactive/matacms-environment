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
use mata\db\ActiveQuery;
use yii\web\HttpException;

class Bootstrap extends \mata\base\Bootstrap {

	private static $envQueryCount = -1;

	public function bootstrap($app) {

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_AFTER_INSERT, function(Event $event) {
			$this->processSave($event->sender);
		});

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_AFTER_UPDATE, function(Event $event) {
			$this->processSave($event->sender);
		});

		// When logged into the CMS, latest version should be shown
		if ($this->shouldRun()) {
			Event::on(ActiveQuery::class, ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT, function(Event $event) {

				$activeQuery = $event->sender;
				$modelClass = $activeQuery->modelClass;
				$sampleModelObject = new $modelClass;
				$documentIdBase = $sampleModelObject->getDocumentId();
				$tableAlias = $activeQuery->getQueryTableName($activeQuery)[0];
				
				if (count($modelClass::primaryKey()) > 1) {
					throw new HttpException(500, sprintf("Composite keys are not handled yet. Table alias is %s", $tableAlias));
				}

				$tablePrimaryKey = $modelClass::primaryKey()[0];

				if ($activeQuery->join)
					foreach ($activeQuery->join as $join) {
						$tableToJoin = $join[1];
					 	$this->addItemEnvironmentJoin($activeQuery, $tableToJoin  . ".DocumentId", $documentIdBase);
					}

				$this->addItemEnvironmentJoin($activeQuery, "CONCAT('" . $documentIdBase . "', " . $tableAlias . "." . $tablePrimaryKey . ")", $documentIdBase);

			});
		}

		if (Yii::$app->getRequest()->get(ItemEnvironment::REQ_PARAM_REVISION))
			Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_AFTER_FIND, function(Event $event) {
					$model = $event->sender;
					$this->getRevision($model, Yii::$app->getRequest()->get(ItemEnvironment::REQ_PARAM_REVISION));
			});
	}

	private function addItemEnvironmentJoin($activeQuery, $documentId, $documentIdBase) {
		// && Yii::$app->user->isGuest
		$module = \Yii::$app->getModule("environment");

		if ($module == null)
			throw new \yii\base\InvalidConfigException("'environment' module pointing to matacms\\environment\\Module module needs to be set");

		/**
		 * We need to check if a given [[documentId]] is present in the [[ItemEnvironment]] table.
		 * If is it not, it means that environments are not used for a given [[documentId]]
		 * This check cannot be done with [[BehaviorHelper::hasBehavior]], as we not always have
		 * the model class name, but always have the [[documentId]]
		 */   
		$hasEnvironmentRecords = ItemEnvironment::find()->where(['like', 'DocumentId', $documentIdBase])->limit(1)->one();

		if ($hasEnvironmentRecords == null)
			return;

		$alias = $this->getTableAlias();

		// TODO This encoding happens in Yii, use what they're offering. E.g. it is used in the call on line 91
		$documentId = str_replace("\\", "\\\\\\",  $documentId);
		
		if ($activeQuery->select == null)
			$activeQuery->addSelect(["*", $alias . ".Revision"]);
		else 
			$activeQuery->addSelect($alias . ".Revision");

		$activeQuery->innerJoin("matacms_itemenvironment " . $alias, "`" . $alias . "`.`DocumentId` = " . $documentId);

		 // TODO refactor and use Query!
		 if (Yii::$app->user->isGuest) {
		 	$liveEnvironment = $module->getLiveEnvironment();

		 	$activeQuery->andWhere($alias . ".Revision = (SELECT Revision FROM matacms_itemenvironment " . $alias . "rev WHERE . " . $alias . "rev.`DocumentId` = " . $alias . ".DocumentId 
		 	 			 AND " . $alias . "rev.`Status` = '" . $liveEnvironment . "' ORDER BY " . $alias . ".Revision DESC LIMIT 1)");
		 	} else {
		 		$activeQuery->andWhere($alias . ".Revision = (SELECT Revision FROM matacms_itemenvironment " . $alias . "rev WHERE " . $alias . "rev.`DocumentId` = " . $alias . ".DocumentId 
		 			 			 ORDER BY " . $alias . ".Revision DESC LIMIT 1)");
		 		
		 	}
	}

	private function getTableAlias() {
		return "env" . ++self::$envQueryCount;
	}

	private function shouldRun() {
		return true;
	}

	private function getRevision($model, $revision) {
		if (BehaviorHelper::hasBehavior($model, \mata\arhistory\behaviors\HistoryBehavior::class))
			$model->setRevision($revision);
	}
	
	private function processSave($model) {

		if (is_object($model) == false || 
			BehaviorHelper::hasBehavior($model, \mata\arhistory\behaviors\HistoryBehavior::class) == false)
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
