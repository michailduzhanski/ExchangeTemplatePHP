<?php

namespace backend\modules\internationalization\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SiteLangDictionary;

/**
 * SiteLangDictionarySearch represents the model behind the search form of `common\models\SiteLangDictionary`.
 */
class SiteLangDictionarySearch extends SiteLangDictionary
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'text_group'], 'integer'],
            [['category', 'language', 'text'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SiteLangDictionary::find()->where(['language' => Yii::$app->language]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'text_group' => $this->text_group,
        ]);

        $query->andFilterWhere(['ilike', 'category', $this->category])
            ->andFilterWhere(['ilike', 'language', $this->language])
            ->andFilterWhere(['ilike', 'text', $this->text]);

        return $dataProvider;
    }
}
