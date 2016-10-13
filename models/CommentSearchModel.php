<?php

namespace yii2mod\comments\models;

use yii\data\ActiveDataProvider;

/**
 * Class CommentSearchModel
 * @package yii2mod\comments\models
 */
class CommentSearchModel extends CommentModel
{
    public $username;
    
    /**
     * @return array validation rules
     */
    public function rules()
    {
        return [
            [['id', 'createdBy', 'content', 'status', 'relatedTo', 'username'], 'safe'],
        ];
    }

    /**
     * Setup search function for filtering and sorting.
     *
     * @param $params
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public function search($params, $pageSize = 20)
    {
        $query = self::find()->innerJoinWith('author');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize
            ]
        ]);

        $dataProvider->setSort([
            'defaultOrder' => ['id' => SORT_DESC],
        ]);

        // load the search form data and validate
        if (!($this->load($params))) {
            return $dataProvider;
        }

        //adjust the query by adding the filters
        $query->andFilterWhere([self::tableName() . '.id' => $this->id]);
        $query->andFilterWhere([self::tableName() . '.created_by' => $this->created_by]);
        $query->andFilterWhere([self::tableName() . '.status' => $this->status]);
        $query->andFilterWhere(['like', 'LOWER(username)', strtolower($this->username)]);
        $query->andFilterWhere([self::tableName() . '.like', 'LOWER(content)', strtolower($this->content)]);
        $query->andFilterWhere([self::tableName() . '.like', 'related_to', $this->related_to]);

        return $dataProvider;
    }
}