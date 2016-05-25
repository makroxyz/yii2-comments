<?php

namespace yii2mod\comments;

use yii2mod\comments\models\CommentModel;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class CommentBehavior extends Behavior
{
    public $entityIdAttribute = 'id';
    
    private $_entity = null;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    public function setEntity($value)
    {
        if ($value instanceof \Closure) {
            $this->_entity = call_user_func($value, $this->owner);
        } else {
            $this->_entity = $value;
        }
    }

    public function getEntity()
    {
        if ($this->_entity === null) {
            $this->_entity = hash('crc32', get_class($this->owner));
        }
        return $this->_entity;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->owner
            ->hasMany(CommentModel::className(), ['entity_id' => $this->entityIdAttribute])
            ->andWhere(['entity' => $this->entity]);
    }

    public function afterDelete($event)
    {
        foreach ($this->getComments()->each() as $comment) {
            $comment->delete();
        }
    }
}