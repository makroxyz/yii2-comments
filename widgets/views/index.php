<?php

use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $comments array */
/* @var $commentModel \yii2mod\comments\models\CommentModel */
/* @var $maxLevel null|integer comments max level */
/* @var $entity string */
/* @var $entityId integer */
/* @var $relatedTo string */
/* @var $pjaxContainerId string */
/* @var $formId string comment form id */
/* @var $showDeletedComments boolean show deleted comments. */
/* @var $readonly boolean */
?>
<?php Pjax::begin(['enablePushState' => false, 'timeout' => 20000, 'id' => $pjaxContainerId]); ?>
<div class="comments row">
    <div class="col-md-12 col-sm-12">
        <div class="title-block clearfix">
            <h3 class="h3-body-title">
                <?php echo Yii::t('yii2mod.comments', "Comments ({0})", $commentModel->getCommentsCount($showDeletedComments ? false : true)); ?>
            </h3>
            <div class="title-separator"></div>
        </div>
        <ol class="comments-list list-unstyled">
            <?php echo $this->render('_list', ['comments' => $comments, 'maxLevel' => $maxLevel, 'deleteRoute' => $deleteRoute, 'readonly' => $readonly]) ?>
        </ol>
        <?php if (!Yii::$app->user->isGuest && !$readonly): ?>
            <div class="clearfix"></div>
            <?php echo $this->render('_form', [
                'commentModel' => $commentModel,
                'entity' => $entity,
                'entityId' => $entityId,
                'relatedTo' => $relatedTo,
                'formId' => $formId,
                'createRoute' => $createRoute
            ]); ?>
        <?php endif; ?>
    </div>
</div>
<?php Pjax::end(); ?>
