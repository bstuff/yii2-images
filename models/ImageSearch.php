<?php

namespace bstuff\yii2images\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ImageSearch represents the model behind the search form about ``.
 */
class ImageSearch extends Image
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'itemId'], 'integer'],
			[['filePath', 'modelTableName', 'name'], 'string'],
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
		$query = Image::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id' => $this->id,
			'itemId' => $this->itemId,
		]);

    $query->andFilterWhere(['like', 'filePath', $this->filePath])
        ->andFilterWhere(['like', 'name', $this->name]);
        ->andFilterWhere(['like', 'modelTableName', $this->modelTableName]);

		return $dataProvider;
	}
}
