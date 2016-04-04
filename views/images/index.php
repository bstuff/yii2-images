<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel ImageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Yii2 Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
              'label' => 'Картинка',
              'format' => 'raw',
              'value' => function($model) {
                return Html::img($model->getUrl(['x'=>150, 'y'=>150]));
              }
            ],
            'filePath',
            'modelTableName',
            'name',
            'itemId',
            'sortOrder',
            
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
