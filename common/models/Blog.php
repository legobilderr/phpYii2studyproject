<?php

namespace common\models;

use common\components\behaviors\StatusBehavior;
use GuzzleHttp\Psr7\UploadedFile;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string|null $text
 * @property string $url
 * @property int $status_id
 * @property int $sort
 * @property int $date_create
 * @property int $date_update
 * @property string $image
 */
class Blog extends \yii\db\ActiveRecord
{
    const STATUS_LIST=['off', 'on'];
    public $tags_array;
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['url'], 'unique'],
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', 'max' => 99, 'min' => 1],
            [['title', 'url'], 'string', 'max' => 150],
            [['image'],'string','max'=>100],
            [['file'], 'image'],
            [['tags_array','date_create','date_update'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'ЧПУ',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Тэги',
            'image' => 'Картинка',
            'file' => 'Картинка',
            'tagsAsString' => 'Tэги данного поста',
            'author.username' => 'Имя автора',
            'date_create' => 'Создано',
            'date_update' => 'Обновлено',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior'=>[
                'class' => StatusBehavior::className(),
                'statusList' => self::STATUS_LIST,
            ]
        ];
    }



    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getTagsAsString()
    {
        $arr = ArrayHelper::map($this->tags, 'name', 'name');
        return implode(', ', $arr);
    }

    public function getSmallImage()
    {
        if($this->image){
            $path =  str_replace('admin.','',Url::home(true)).'uploads/images/blog/50x50/'.$this->image;
        }else{
            $path=str_replace('admin.','',Url::home(true)).'uploads/images/No_image_available.svg';
        }
        return $path;
    }

    public function getBlogTag()
    {
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }

    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        $this->tags_array = ArrayHelper::map($this->tags, 'name', 'name');
    }


    public function beforeSave($insert)
    {
        if($file = \yii\web\UploadedFile::getInstance($this,'file')){
            $dir = Yii::getAlias('@images').'/blog/';
            if(!is_dir($dir.$this->image)){
                if (file_exists($dir.$this->image)){
                    unlink($dir.$this->image);
                }
                if (file_exists($dir.'50x50/'.$this->image)){
                    unlink($dir.'50x50/'.$this->image);
                }
                if (file_exists($dir.'800x/'.$this->image)){
                    unlink($dir.'800x/'.$this->image);
                }
            }
            $this->image=strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(6) . '.' .
                $file->extension;
            $file->saveAs($dir.$this->image);
            $imag= Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('50','50' , Yii\image\drivers\Image::INVERSE);
            $imag->crop('50','50');
            if(!file_exists($dir.'50x50/')){
                FileHelper::createDirectory($dir.'50x50/');
            }
            $imag->save($dir.'50x50/'.$this->image,90);

            $imag= Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('800',null, Yii\image\drivers\Image::INVERSE);
            if(!file_exists($dir.'800x/')){
                FileHelper::createDirectory($dir.'800x/');
            }
            $imag->save($dir.'800x/'.$this->image,90);


        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub

        if (is_array($this->tags_array)){
            $old_tags=ArrayHelper::map($this->tags,'name','id');
            foreach ($this->tags_array as $one_new_tag) {
                if (isset($old_tags[$one_new_tag])) {
                    unset($old_tags[$one_new_tag]);
                }
                else {
                    $this->createNewTag($one_new_tag);
                }
            }
                BlogTag::deleteAll(['and',['blog_id'=>$this->id],['tag_id'=>$old_tags]]);
            }else{
                BlogTag::deleteAll(['blog_id'=>$this->id]);
            }
        }


        private function createNewTag($one_new_tag)
        {
            if (!$tag = Tag::find()->andWhere(['name' => $one_new_tag])->one()) {
                $tag = new Tag();
                $tag->name = $one_new_tag;
                if (!$tag->save()) {
                    $tag = null;
                }
            }
            if ($tag instanceof Tag) {
                $blog_tag = new BlogTag();
                $blog_tag->blog_id = $this->id;
                $blog_tag->tag_id = $tag->id;
                if ($blog_tag->save()) {
                    return $blog_tag->id;
                }
                return false;
            }
        }

    }

