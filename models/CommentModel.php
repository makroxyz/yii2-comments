<?php

namespace yii2mod\comments\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii2mod\behaviors\PurifyBehavior;
use yii2mod\comments\models\enums\CommentStatus;
use yii2mod\comments\Module;

/**
 * Class CommentModel
 *
 * @property integer $id
 * @property string $entity
 * @property integer $entity_id
 * @property integer $parent_id
 * @property string $content
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $related_to
 * @property integer $status
 * @property integer $level
 * @property integer $created_at
 * @property integer $updated_at
 *
 */
class CommentModel extends ActiveRecord
{
    /**
     * @var null|array|ActiveRecord[] Comment children
     */
    protected $_children;

    /**
     * Declares the name of the database table associated with this AR class.
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * Returns the validation rules for attributes.
     * @return array validation rules
     */
    public function rules()
    {
        return [
            [['entity', 'entity_id', 'content'], 'required'],
            [['content', 'entity', 'related_to'], 'string'],
            ['parent_id', 'validateparent_id'],
            [['entity_id', 'parent_id', 'created_by', 'updated_by', 'status', 'created_at', 'updated_at', 'level'], 'integer'],
        ];
    }

    /**
     * Validate parent_id attribute
     * @param $attribute
     */
    public function validateparent_id($attribute)
    {
        if ($this->{$attribute} !== null) {
            $comment = self::find()->where(['id' => $this->{$attribute}, 'entity' => $this->entity, 'entity_id' => $this->entity_id])->active()->exists();
            if ($comment === false) {
                $this->addError('content', Yii::t('yii2mod.comments', 'Oops, something went wrong. Please try again later.'));
            }
        }
    }


    /**
     * Returns a list of behaviors that this component should behave as.
     * @return array
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::className(),
            ],
            'timestamp' => [
                'class' => TimestampBehavior::className(),
            ],
            'purify' => [
                'class' => PurifyBehavior::className(),
                'attributes' => ['content']
            ]
        ];
    }

    /**
     * Returns the attribute labels.
     * @return array attribute labels (name => label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii2mod.comments', 'ID'),
            'content' => Yii::t('yii2mod.comments', 'Comment'),
            'entity' => Yii::t('yii2mod.comments', 'Entity'),
            'status' => Yii::t('yii2mod.comments', 'Status'),
            'level' => Yii::t('yii2mod.comments', 'Level'),
            'created_by' => Yii::t('yii2mod.comments', 'Created by'),
            'updated_by' => Yii::t('yii2mod.comments', 'Updated by'),
            'related_to' => Yii::t('yii2mod.comments', 'Related to'),
            'created_at' => Yii::t('yii2mod.comments', 'Created date'),
            'updated_at' => Yii::t('yii2mod.comments', 'Updated date'),
        ];
    }

    /**
     * @inheritdoc
     * @return CommentQuery
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->parent_id > 0) {
                $parentNodeLevel = (int)self::find()->select('level')->where(['id' => $this->parent_id])->scalar();
                $this->level = $parentNodeLevel + 1;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Author relation
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        $module = Yii::$app->getModule(Module::$name);
        return $this->hasOne($module->userIdentityClass, ['id' => 'created_by']);
    }

    /**
     * Get comments tree.
     *
     * @param $entity string model class id
     * @param $entity_id integer model id
     * @param null $maxLevel
     * @return array|\yii\db\ActiveRecord[] Comments tree
     */
    public static function getTree($entity, $entity_id, $maxLevel = null)
    {
        $query = self::find()->where([
            'entity_id' => $entity_id,
            'entity' => $entity,
        ])->with(['author']);
        if ($maxLevel > 0) {
            $query->andWhere(['<=', 'level', $maxLevel]);
        }
        $models = $query->orderBy(['parent_id' => SORT_ASC, 'created_at' => SORT_ASC])->all();
        if (!empty($models)) {
            $models = self::buildTree($models);
        }
        return $models;
    }

    /**
     * Build comments tree.
     *
     * @param array $data Records array
     * @param int $rootID parent_id Root ID
     * @return array|ActiveRecord[] Comments tree
     */
    protected static function buildTree(&$data, $rootID = 0)
    {
        $tree = [];
        foreach ($data as $id => $node) {
            if ($node->parent_id == $rootID) {
                unset($data[$id]);
                $node->children = self::buildTree($data, $node->id);
                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * Delete comment.
     *
     * @return boolean Whether comment was deleted or not
     */
    public function deleteComment()
    {
        $this->status = CommentStatus::DELETED;
        return $this->save(false, ['status', 'updated_by', 'updated_at']);
    }

    /**
     * $_children getter.
     *
     * @return null|array|ActiveRecord[] Comment children
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * $_children setter.
     *
     * @param array|ActiveRecord[] $value Comment children
     */
    public function setChildren($value)
    {
        $this->_children = $value;
    }

    /**
     * Check if comment has children comment
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->_children) ? true : false;
    }

    /**
     * @return boolean Whether comment is active or not
     */
    public function getIsActive()
    {
        return $this->status === CommentStatus::ACTIVE;
    }

    /**
     * @return boolean Whether comment is deleted or not
     */
    public function getIsDeleted()
    {
        return $this->status === CommentStatus::DELETED;
    }

    /**
     * Get comment posted date as relative time
     * @return string
     */
    public function getPostedDate()
    {
        return Yii::$app->formatter->asRelativeTime($this->created_at);
    }

    /**
     * Get author name
     * @return mixed
     */
    public function getAuthorName()
    {
        return $this->author->username;
    }

    /**
     * Get comment content
     * @param string $deletedCommentText
     * @return string
     */
    public function getContent($deletedCommentText = null)
    {
        if ($deletedCommentText === null) {
            $deletedCommentText = Yii::t('yii2mod.comments', 'Comment was deleted.');
        }
        return $this->isDeleted ? $deletedCommentText : Yii::$app->formatter->asNtext($this->content);
    }

    /**
     * Get avatar user
     * @param array $imgOptions
     * @return string
     */
    public function getAvatar($imgOptions = [])
    {
        $imgOptions = ArrayHelper::merge($imgOptions, ['class' => 'img-responsive']);
        return Html::img("http://gravatar.com/avatar/{$this->author->id}/?s=48", $imgOptions);
    }


    /**
     * This function used for filter in gridView, for attribute `created_by`.
     * @return array
     */
    public static function getListAuthorsNames()
    {
        return ArrayHelper::map(self::find()->joinWith('author')->all(), 'created_by', 'author.username');
    }
}
