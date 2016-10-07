<?php

namespace yii2mod\comments\widgets;

use Yii;
use yii2mod\comments\CommentBehavior;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii2mod\comments\CommentAsset;
use yii2mod\comments\Module;

/**
 * Class Comment
 * @package yii2mod\comments\widgets
 */
class Comment extends Widget
{
    public $module = 'comments';
    /**
     * @var \yii\db\ActiveRecord|null Widget model
     */
    public $model;
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var string relatedTo custom text, for example: cms url: about-us, john comment about us page, etc.
     * By default - className:primaryKey of the current model
     */
    public $relatedTo = '';

    /**
     * @var string the view file that will render the comment tree and form for posting comments.
     */
    public $commentView = '@vendor/yii2mod/yii2-comments/widgets/views/index';

    /**
     * @var string comment form id
     */
//    public $formId = 'comment-form';

    /**
     * @var null|integer maximum comments level, level starts from 1, null - unlimited level;
     */
    public $maxLevel = 7;

    /**
     * @var boolean show deleted comments. Defaults to `false`.
     */
    public $showDeletedComments = false;

    /**
     * @var string entity id attribute
     */
    public $entityIdAttribute = 'id';

    /**
     * @var array comment widget client options
     */
    public $clientOptions = [];
    /**
     * @var array comment widget client events
     */
    public $clientEvents = [];
    
    /**
     * @var string hash(crc32) from class name of the widget model
     */
    protected $entity;

    /**
     * @var integer primary key value of the widget model
     */
    protected $entityId;

    /**
     * @var string encrypted entity key from params: entity, entityId, relatedTo
     */
    protected $encryptedEntityKey;
    
    protected $formId;

    /**
     * @var string pjax container id, generated automatically
     */
    public $pjaxContainerId;
    /**
     * @var behavior 
     */
    private $_behavior;

    /**
     * Initializes the widget params.
     */
    public function init()
    {
        if (empty($this->model)) {
            throw new InvalidConfigException(Yii::t('yii2mod.comments', 'The "model" property must be set.'));
        }
        
        $model = $this->model;
        foreach ($model->getBehaviors() as $behavior) {
            if ($behavior instanceof CommentBehavior) {
                $this->_behavior = $behavior;
                break;
            }
        }
        if ($this->_behavior === null) {
            throw new InvalidConfigException($model::className() . ' must have ' . CommentBehavior::className());
        }
        
//        $this->pjaxContainerId = 'comment-pjax-container-' . $this->getId();
        
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        
        $this->formId = $this->id . '-form';
        
        if ($this->pjaxContainerId == '') {
            $this->pjaxContainerId = $this->getId() . '-pjax';
        }
        $this->entity = $this->_behavior->entity;
        $this->entityId = $model->{$this->_behavior->entityIdAttribute};
        if (empty($this->entityId)) {
            throw new InvalidConfigException(Yii::t('yii2mod.comments', 'The "entityIdAttribute" value for widget model cannot be empty.'));
        }

        if (empty($this->relatedTo)) {
            $this->relatedTo = get_class($this->model) . ':' . $this->entityId;
        }

        $this->encryptedEntityKey = $this->generateEntityKey();

        $this->registerAssets();

        echo Html::beginTag('div', $this->options);
    }

    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule($this->module);
        $commentModelClass = $module->commentModelClass;
        $commentModel = Yii::createObject([
            'class' => $commentModelClass,
            'entity' => $this->entity,
            'entity_id' => $this->entityId
        ]);
        $comments = $commentModelClass::getTree($this->entity, $this->entityId, $this->maxLevel, $this->showDeletedComments);
        echo $this->render($this->commentView, [
            'comments' => $comments,
            'commentModel' => $commentModel,
            'maxLevel' => $this->maxLevel,
            'encryptedEntity' => $this->encryptedEntityKey,
            'pjaxContainerId' => $this->pjaxContainerId,
            'formId' => $this->formId,
			'showDeletedComments' => $this->showDeletedComments,
            'createRoute' => "/$module->id/default/create",
            'deleteRoute' => "/$module->id/default/delete",
        ]);
        echo Html::endTag('div');
    }

    /**
     * Register assets.
     */
    protected function registerAssets()
    {
        $this->clientOptions['containerId'] = '#' . $this->id;
        $this->clientOptions['pjaxContainerId'] = '#' . $this->pjaxContainerId;
        $this->clientOptions['formSelector'] = '#' . $this->formId;
        $this->clientOptions['submitButtonLabel'] = Yii::t('yii2mod.comments', 'Submit');
        $options = Json::encode($this->clientOptions);
        $view = $this->getView();
        CommentAsset::register($view);
        $view->registerJs("jQuery('#{$this->id}').comment($options);");
        $this->registerClientEvents();
    }
    
	/**
     * Register client events.
     */
    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
//            $id = $this->options['id'];
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
//                $js[] = "jQuery(document).on('$event', '#{$this->id}', $handler);";
                $js[] = "jQuery('#{$this->id}').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

	 /**
     * Get encrypted entity key
     *
     * @return string
     */
    protected function generateEntityKey()
    {
        /*return utf8_encode(Yii::$app->getSecurity()->encryptByKey(Json::encode([
            'entity' => $this->entity,
            'entityId' => $this->entityId,
            'relatedTo' => $this->relatedTo
        ]), Module::$name));*/

		return Yii::$app->getSecurity()->encryptByKey(Json::encode([
            'entity' => $this->entity,
            'entity_id' => $this->entityId,
            'related_to' => $this->relatedTo
        ]), $this->module);
    }
}