<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\widgets\Pjax;
use yii2mod\comments\models\enums\CommentStatus;
use yii2mod\editable\EditableColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \yii2mod\comments\models\CommentSearchModel */
/* @var $commentModel \yii2mod\comments\models\CommentModel */

$this->title = Yii::t('yii2mod.comments', 'Comments Management');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comments-index">

    <h1><?php echo Html::encode($this->title) ?></h1>
    <?php Pjax::begin(['timeout' => 5000]); ?>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'contentOptions' => ['style' => 'width: 80px;']
            ],
            [
                'attribute' => 'content',
                'contentOptions' => ['style' => 'max-width: 350px;'],
                'value' => function ($model) {
                    return StringHelper::truncate($model->content, 50);
                }
            ],
            [
                'attribute' => 'related_to',
                'value' => function ($model) {
                    return StringHelper::truncate($model->related_to, 50);
                }
            ],
            [
                'header' => $searchModel->getAttributeLabel('author'),
                'attribute' => 'username',
                'value' => function ($model) {
                    return $model->getAuthorName();
                },
//                'filter' => $commentModel::getListAuthorsNames(),
//                'filterInputOptions' => ['prompt' => Yii::t('yii2mod.comments', 'Select Author'), 'class' => 'form-control'],
            ],
            [
                'class' => EditableColumn::className(),
                'attribute' => 'status',
                'url' => ['edit-comment'],
                'value' => function ($model) {
                    return CommentStatus::getLabel($model->status);
                },
                'type' => 'select',
                'editableOptions' => function ($model) {
                    return [
                        'source' => Json::encode(CommentStatus::listData()),
                        'value' => $model->status,
                    ];
                },
                'filter' => CommentStatus::listData(),
                'filterInputOptions' => ['prompt' => Yii::t('yii2mod.comments', 'Select Status'), 'class' => 'form-control'],
//                'contentOptions' => ['class' => 'col-xs-2']
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at);
                },
                'filter' => false,
                'contentOptions' => ['class' => 'col-xs-1']
            ],
            [
                'header' => 'Actions',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'contentOptions' => ['style' => 'width: 60px;']
            ]
        ],
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
