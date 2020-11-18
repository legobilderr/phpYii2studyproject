<?php

use common\models\Blog;
use common\models\Tag;
use kartik\file\FileInput;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;

/* @var $this yii\web\View */
/* @var $model common\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
            'options'=>['enctype'=>'multipart/form-data'],
    ]); ?>


    <div class="row">
    <?= $form->field($model, 'file',['options'=>['class'=>'col-xs-6']])->widget(FileInput::classname(),[
            'options'=>['accept'=>'image/*'],
            'pluginOptions' => [
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'browseClass' => 'btn btn-primary btn-block',
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' =>  'Выбрать фото'
            ],
    ]) ?>

    <?= $form->field($model, 'title',['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url',['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_id',['options'=>['class'=>'col-xs-6']])->dropDownList(Blog::STATUS_LIST) ?>

    <?= $form->field($model, 'sort',['options'=>['class'=>'col-xs-6']])->textInput() ?>

    <?= $form->field($model, 'tags_array',['options'=>['class'=>'col-xs-6']])->widget(Select2::classname(), [
        'data' => ArrayHelper::map( Tag::find()->all(),'name','name'),
        'language' => 'ru',
        'options' => ['placeholder' => 'Выбрать тэги  ...','multiple'=>true],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
            'maximumInputLength' => 10
    ],
    ]);?>
    </div>
    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'formatting'=> ['p', 'blockquote', 'h2','h1'],
            'plugins' => [
                'clips',
                'fullscreen',
            ],
            'clips' => [
                ['Lorem ipsum...', 'Lorem...'],
                ['red', '<span class="label-red">red</span>'],
                ['green', '<span class="label-green">green</span>'],
                ['blue', '<span class="label-blue">blue</span>'],
            ],
            'imageUpload' => Url::to(['/site/save-redactor-img','sub'=>'blog']),
        ],
    ]);?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
