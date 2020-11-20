<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "image_manager".
 *
 * @property int $id
 * @property string $name
 * @property string $class
 * @property int $item_id
 * @property string|null $alt
 */
class ImageManager extends \yii\db\ActiveRecord
{
    public $attachment;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image_manager';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attachment'], 'image',],
            [['item_id'], 'integer'],
            [['name', 'class', 'alt'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'class' => 'Class',
            'item_id' => 'Item ID',
            'alt' => 'Alt',
        ];
    }
}
