<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment\behaviors;

use Yii;
use yii\base\Behavior;
use matacms\environment\models\ItemEnvironment;

class EnvironmentBehavior extends Behavior {

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
