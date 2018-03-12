<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $comment \yii2mod\comments\models\CommentModel */
/* @var $comments array */
/* @var $maxLevel null|integer comments max level */
?>
<?php if (!empty($comments)) : ?>
    <?php foreach ($comments as $comment) : ?>
        <li class="comment" id="comment-<?php echo $comment->id ?>">
            <div class="comment-content well well-sm" data-comment-content-id="<?php echo $comment->id ?>">
                <div class="comment-author-avatar">
                    <?php echo $comment->getAvatar(['alt' => $comment->getAuthorName()]); ?>
                </div>
                <div class="comment-details">
                    <?php if ($comment->isActive): ?>
                        <div class="comment-action-buttons pull-right">
                            <?php if (Yii::$app->getUser()->can('admin')): ?>
                                <?php echo Html::a(Html::icon('trash') . " " . Yii::t('yii2mod.comments', 'Delete'), '#', ['class' => 'btn btn-danger btn-xs', 'data' => ['action' => 'delete', 'url' => Url::to([$deleteRoute, 'id' => $comment->id]), 'comment-id' => $comment->id]]); ?>
                            <?php endif; ?>
                            <?php if (!Yii::$app->user->isGuest && !$readonly && ($comment->level < $maxLevel || is_null($maxLevel))): ?>
                                <?php echo Html::a(Html::icon('share-alt') . " " . Yii::t('yii2mod.comments', 'Reply'), '#', ['class' => 'btn btn-primary btn-xs comment-reply', 'data' => ['action' => 'reply', 'comment-id' => $comment->id]]); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php echo Html::tag('meta', null, ['content' => Yii::$app->formatter->asDatetime($comment->created_at, 'php:c'), 'itemprop' => 'dateCreated']); ?>
                    <?php echo Html::tag('meta', null, ['content' => Yii::$app->formatter->asDatetime($comment->updated_at, 'php:c'), 'itemprop' => 'dateModified']); ?>
                    <div class="comment-author-name" itemprop="creator" itemscope itemtype="http://schema.org/Person">
                        <span itemprop="name"><?php echo $comment->getAuthorName(); ?></span>
                        <span class="comment-date text-muted">
                            <?php echo $comment->getPostedDate(); ?>
                        </span>
                    </div>
                    <div class="comment-body" itemprop="text">
                        <?php echo $comment->getContent(); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <?php if ($comment->hasChildren()): ?>
                <ul class="children list-unstyled">
                    <?php echo $this->render('_list', ['comments' => $comment->children, 'maxLevel' => $maxLevel, 'deleteRoute' => $deleteRoute, 'readonly' => $readonly]) ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
