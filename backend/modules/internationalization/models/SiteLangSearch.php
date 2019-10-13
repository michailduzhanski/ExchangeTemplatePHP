<?php

namespace backend\modules\internationalization\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SiteLang;

/**
 * SiteLangSearch represents the model behind the search form of `common\models\SiteLang`.
 */
class SiteLangSearch extends SiteLang
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'locale', 'name'], 'safe'],
            [['default_lang'], 'boolean'],
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
        $query = SiteLang::find();

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
            'default_lang' => $this->default_lang,
        ]);

        $query->andFilterWhere(['ilike', 'code', $this->code])
            ->andFilterWhere(['ilike', 'locale', $this->locale])
            ->andFilterWhere(['ilike', 'name', $this->name]);

        return $dataProvider;
    }
}
