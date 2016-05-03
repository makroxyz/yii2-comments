<?php

use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $comments array */
/* @var $commentModel \yii2mod\comments\models\CommentModel */
/* @var $maxLevel null|integer comments max level */
/* @var $encryptedEntity string */
/* @var $pjaxContainerId string */
/* @var $formId string comment form id */
?>
<?php Pjax::begin([
    'enablePushState' => false,
    'timeout' => 10000,
    'id' => $pjaxContainerId
]); ?>
<div class="comments row">
    <div class="col-md-12 col-sm-12">
        <ol class="comments-list list-unstyled">
            <?php echo $this->render('_list', ['comments' => $comments, 'maxLevel' => $maxLevel, 'deleteRoute' => $deleteRoute]) ?>
        </ol>
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="clearfix"></div>
            <?php echo $this->render('_form', [
                'commentModel' => $commentModel,
                'encryptedEntity' => $encryptedEntity,
                'formId' => $formId,
                'createRoute' => $createRoute
            ]); ?>
        <?php endif; ?>
    </div>
</div>
<?php Pjax::end(); ?>

