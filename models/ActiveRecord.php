<?php

namespace wdmg\base\models;

/**
 * Yii2 ActiveRecord
 *
 * @category        Model
 * @version         1.3.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 - 2021 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use wdmg\base\behaviors\SluggableBehavior;
use yii\db\Expression;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the extended model class of yii\db\ActiveRecord.
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $status
 * @property bool $in_sitemap
 * @property bool $in_rss
 * @property bool $in_turbo
 * @property bool $in_amp
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $route
 * @property string $url
 * @package wdmg\base\models
 */

class ActiveRecord extends BaseActiveRecord
{
    const STATUS_DRAFT = 0; // Model has draft
    const STATUS_PUBLISHED = 1; // Model has been published

    public $uniqueAttributes = [];

    public $baseRoute;

    public $layout;
    public $url;

    /**
     * ID of parent module
     * @var null|string
     */
    public $moduleId;

    /**
     * Instance of parent module
     * @var null|object
     */
    private $_module;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (is_string($this->moduleId)) {

            if (!($this->_module = Yii::$app->getModule('admin/' . $this->moduleId, false)))
                $this->_module = Yii::$app->getModule($this->moduleId, false);

        } else if (isset(Yii::$app->controller)) {
            $this->_module = Yii::$app->controller->module;
        }

        if (isset($this->_module->id)) {
            if (isset(Yii::$app->params[$this->_module->id . ".baseRoute"])) {
                $this->baseRoute = Yii::$app->params[$this->_module->id . ".baseRoute"];
            } else if (isset($this->_module->baseRoute)) {
                $this->baseRoute = $this->_module->baseRoute;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if ($this->hasAttribute('created_at')) {
            $behaviors = ArrayHelper::merge([
                'timestamp' => [
                    'class' => TimestampBehavior::class,
                    'attributes' => [
                        BaseActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ],
                    'value' => new Expression('NOW()'),
                ]
            ], $behaviors);
        }

        if ($this->hasAttribute('updated_at')) {
            $behaviors = ArrayHelper::merge([
                'timestamp' => [
                    'class' => TimestampBehavior::class,
                    'attributes' => [
                        BaseActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                    ],
                    'value' => new Expression('NOW()'),
                ]
            ], $behaviors);
        }

        if ($this->hasAttribute('created_by')) {
            $behaviors = ArrayHelper::merge([
                'blameable' => [
                    'class' => BlameableBehavior::class,
                    'createdByAttribute' => 'created_by',
                ]
            ], $behaviors);
        }

        if ($this->hasAttribute('updated_by')) {
            $behaviors = ArrayHelper::merge([
                'blameable' => [
                    'class' => BlameableBehavior::class,
                    'updatedByAttribute' => 'updated_by',
                ]
            ], $behaviors);
        }

        if ($this->hasAttribute('name') && $this->hasAttribute('alias')) {
            $behaviors = ArrayHelper::merge([
                'sluggable' => [
                    'class' => SluggableBehavior::class,
                    'attribute' => ['name'],
                    'slugAttribute' => 'alias',
                    'locale' => 'Russian-Latin/BGN; Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFKC; [ʹ, ʺ] Remove; [:Punctuation:] Remove;',
                    'replacement' => '-'
                ]
            ], $behaviors);
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        if ($this->hasAttribute('created_at'))
            $rules[] = ['created_at', 'safe'];

        if ($this->hasAttribute('updated_at'))
            $rules[] = ['updated_at', 'safe'];

        if ($this->hasAttribute('created_by'))
            $rules[] = ['created_by', 'safe'];

        if ($this->hasAttribute('updated_by'))
            $rules[] = ['updated_by', 'safe'];

        if ($this->hasAttribute('status'))
            $rules[] = ['status', 'boolean'];

        if ($this->hasAttribute('in_sitemap'))
            $rules[] = ['in_sitemap', 'boolean'];

        if ($this->hasAttribute('in_rss'))
            $rules[] = ['in_rss', 'boolean'];

        if ($this->hasAttribute('in_turbo'))
            $rules[] = ['in_turbo', 'boolean'];

        if ($this->hasAttribute('in_amp'))
            $rules[] = ['in_amp', 'boolean'];

        if ($this->hasAttribute('name'))
            $rules[] = ['name', 'string', 'max' => 255];


        if ($this->hasAttribute('alias')) {
            $rules[] = ['alias', 'string', 'max' => 255];
            $rules[] = ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/base','It allowed only Latin alphabet, numbers and the «-», «_» characters.')];

            if (in_array('alias', $this->uniqueAttributes)) {
                $rules[] = ['alias', 'unique', 'message' => Yii::t('app/modules/base', 'Attribute must be unique.')];
            }
        }

        if ($this->hasAttribute('url'))
            $rules[] = ['url', 'safe'];

        if ($this->hasAttribute('route')) {
            $rules[] = ['route', 'string', 'max' => 255];
            $rules[] = ['route', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/]+$/', 'message' => Yii::t('app/modules/base','It allowed only Latin alphabet, numbers and the «-», «_», «/» characters.')];
        }

        if ($this->hasAttribute('layout')) {
            $rules[] = ['layout', 'string', 'max' => 64];
            $rules[] = ['layout', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/\@]+$/', 'message' => Yii::t('app/modules/base','It allowed only Latin alphabet, numbers and the «@», «-», «_», «/» characters.')];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        if ($this->hasAttribute('id'))
            $labels[] = ['id' => Yii::t('app/modules/base', 'ID')];

        if ($this->hasAttribute('name'))
            $labels[] = ['name' => Yii::t('app/modules/base', 'Name')];

        if ($this->hasAttribute('alias'))
            $labels[] = ['alias' => Yii::t('app/modules/base', 'Alias')];

        if ($this->hasAttribute('in_sitemap'))
            $labels[] = ['in_sitemap' => Yii::t('app/modules/base', 'In sitemap?')];

        if ($this->hasAttribute('in_rss'))
            $labels[] = ['in_rss' => Yii::t('app/modules/base', 'In RSS-feed?')];

        if ($this->hasAttribute('in_turbo'))
            $labels[] = ['in_turbo' => Yii::t('app/modules/base', 'Yandex turbo-pages?')];

        if ($this->hasAttribute('in_amp'))
            $labels[] = ['in_amp' => Yii::t('app/modules/base', 'Google AMP?')];

        if ($this->hasAttribute('status'))
            $labels[] = ['status' => Yii::t('app/modules/base', 'Status')];

        if ($this->hasAttribute('created_at'))
            $labels[] = ['created_at' => Yii::t('app/modules/base', 'Created at')];

        if ($this->hasAttribute('updated_at'))
            $labels[] = ['updated_at' => Yii::t('app/modules/base', 'Updated at')];

        if ($this->hasAttribute('created_by'))
            $labels[] = ['created_by' => Yii::t('app/modules/base', 'Created by')];

        if ($this->hasAttribute('updated_by'))
            $labels[] = ['updated_by' => Yii::t('app/modules/base', 'Updated by')];

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        if ($this->hasAttribute('url')) {
            if (is_null($this->url)) {
                $this->url = $this->getUrl();
            }
        }
    }

    /**
     * Return author who create model
     *
     * @return int|\yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return $this->created_by;
    }

    /**
     * Return author who update model
     *
     * @return int|\yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'updated_by']);
        else
            return $this->updated_by;
    }

    /**
     * Returns all models by condition
     *
     * @param null $cond
     * @param bool $asArray
     * @return array|ActiveRecord[]
     */
    public static function getAll($cond = null, $asArray = false) {
        if (!is_null($cond))
            $models = self::find()->where($cond);
        else
            $models = self::find();

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }

    /**
     * Returns one model by condition
     *
     * @param null $cond
     * @param bool $asArray
     * @return array|ActiveRecord[]
     */
    public static function getOne($cond = null, $asArray = false) {
        if (!is_null($cond))
            $models = self::find()->where($cond);
        else
            $models = self::find();

        if ($asArray)
            return $models->asArray()->one();
        else
            return $models->one();

    }

    /**
     * Returns all published models
     *
     * @param null $cond
     * @param bool $asArray
     * @return array|ActiveRecord[]
     */
    public static function getAllPublished($cond = null, $asArray = false) {
        if (!is_null($cond) && is_array($cond))
            $cond = ArrayHelper::merge($cond, ['status' => self::STATUS_PUBLISHED]);
        elseif (!is_null($cond) && is_string($cond))
            $cond = ArrayHelper::merge([$cond], ['status' => self::STATUS_PUBLISHED]);
        else
            $cond = ['status' => self::STATUS_PUBLISHED];

        return self::getAll($cond, $asArray);

    }

    /**
     * Returns one published model
     *
     * @param null $cond
     * @param bool $asArray
     * @return array|ActiveRecord[]
     */
    public static function getPublished($cond = null, $asArray = false) {
        if (!is_null($cond) && is_array($cond))
            $cond = ArrayHelper::merge($cond, ['status' => self::STATUS_PUBLISHED]);
        elseif (!is_null($cond) && is_string($cond))
            $cond = ArrayHelper::merge([$cond], ['status' => self::STATUS_PUBLISHED]);
        else
            $cond = ['status' => self::STATUS_PUBLISHED];

        return self::getOne($cond, $asArray);

    }

    /**
     * Return base route for build frontend URL`s
     *
     * @return string|null
     */
    public function getRoute()
    {
        if (!is_null($this->baseRoute))
            return $this->baseRoute;

        return null;
    }

    /**
     * Build and return the frontend URL to the view of the current model
     *
     * @param bool $withScheme boolean, absolute or relative URL
     * @param bool $realUrl
     * @return string|null
     */
    public function getModelUrl($withScheme = true, $realUrl = false)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            if ($this->status == self::STATUS_DRAFT && $realUrl)
                return \yii\helpers\Url::to(['default/view', 'alias' => $this->alias, 'draft' => 'true'], $withScheme);
            else
                return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);

        } else {
            return null;
        }
    }

    /**
     * Returns the URL to the view of the current model
     *
     * @return string|null
     */
    public function getUrl($withScheme = true)
    {
        if ($this->url === null)
            $this->url = $this->getModelUrl($withScheme, false);

        return $this->url;
    }

    /**
     * Returns the next record(s) (or `id`) based on primary key attribute
     *
     * @param bool $instance, flag if it is necessary to return an entity instead of a value
     * @param int|false $limit, the limit of records to be returned or `false` to return all records
     * @param string $primaryKey, primary key attribute like `id`, `post_id`
     * @return array|\yii\db\ActiveQuery|BaseActiveRecord[]
     */
    public function getNext($instance = true, $limit = 1, $primaryKey = 'id') {

        $query = $this->find()->where(['>', trim($primaryKey), $this->$primaryKey]);

        if ($limit == 1)
            $next = $query->limit(1)->one();
        elseif ($limit)
            $next = $query->limit(intval($limit))->all();
        else
            $next = $query->all();

        if (!$instance && is_array($next))
            return ArrayHelper::getColumn($next, $primaryKey);
        elseif (!$instance && is_object($next))
            return $next->$primaryKey;
        else
            return $next;

    }

    /**
     * Returns the previous record(s) (or `id`) based on primary key attribute
     *
     * @param bool $instance, flag if it is necessary to return an entity instead of a value
     * @param int|false $limit, the limit of records to be returned or `false` to return all records
     * @param string $primaryKey, primary key attribute like `id`, `post_id`
     * @return array|\yii\db\ActiveQuery|BaseActiveRecord[]
     */
    public function getPrev($instance = true, $limit = 1, $primaryKey = 'id') {

        $query = $this->find()->where(['<', trim($primaryKey), $this->$primaryKey])->orderBy('id DESC');

        if ($limit == 1)
            $prev = $query->limit(1)->one();
        elseif ($limit)
            $prev = $query->limit(intval($limit))->all();
        else
            $prev = $query->all();

        if (!$instance && is_array($prev))
            return ArrayHelper::getColumn($prev, $primaryKey);
        elseif (!$instance && is_object($prev))
            return $prev->$primaryKey;
        else
            return $prev;

    }

    /**
     * Returns the instance (or id) of parent Module of current model
     *
     * @param bool $instance
     * @return object|null
     */
    public function getModule($instance = false)
    {
        if ($instance)
            return (is_object($this->_module)) ? $this->_module : null;

        return (isset($this->_module->id)) ? $this->_module->id : null;
    }
}
