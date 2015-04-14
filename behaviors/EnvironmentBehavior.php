<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace matacms\environment\behaviors;

use Yii;
use yii\base\Behavior;
use matacms\environment\models\ItemEnvironment;

class EnvironmentBehavior extends Behavior {

  /**
   * When the model should be null, Environment::Bootstrap will set this to true. 
   * In turn matacms\db\ActiveQuery will nullify the object based on the value
   * of _markedToRemove;
   */ 
  private $markedToRemove = false;

  public function markForRemoval() {
    $this->markedToRemove = true;
  }

  public function getMarkedForRemoval() {
    return $this->markedToRemove;
  }

  public function getVersionStatus() {

    $revision = $this->owner->getRevision();

    if ($revision == null)
      return;

    $ie = ItemEnvironment::find()->where([
      "DocumentId" => $this->owner->getDocumentId()->getId(),
      "Revision" => $revision->Revision
      ])->one();

    if ($ie)
      return $ie->Status;
  }

  /**
   * Return the difference between live version and the current version
   */

  public function getRevisionDelta() {
    $currentRevision = $this->owner->getRevision();

    if ($currentRevision == null)
      return null;

    $currentRevision = $this->owner->getRevision()->Revision;

    $publishedRevision = ItemEnvironment::find()->where([
      "DocumentId" => $this->owner->getDocumentId()->getId(),
      "Status" => Yii::$app->getModule("environment")->getLiveEnvironment(),
      ])->orderBy("Revision DESC")->one();

    if ($publishedRevision)
      return  $currentRevision - $publishedRevision->Revision;

    return 0;
  }

  public function hasLiveVersion() {
    return ItemEnvironment::find()->where([
      "DocumentId" => $this->owner->getDocumentId()->getId(),
      "Status" => Yii::$app->getModule("environment")->getLiveEnvironment(),
      ])->orderBy("Revision DESC")->one() != null;
  }
}