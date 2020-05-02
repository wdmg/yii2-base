<?php

namespace wdmg\base\models;

/**
 * Yii2 ActiveRecordML
 *
 * @category        Model
 * @version         1.2.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 * @property int $source_id
 * @property int $parent_id
 * @property string $locale
 * @package wdmg\base\models
 */

use Yii;
use wdmg\base\models\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Yii2 ActiveRecord. This is the extended model class of yii\db\ActiveRecord
 * with multi-languages support.
 *
 * @property int $source_id
 * @property int $parent_id
 * @property string $locale
 * @package wdmg\base\models
 */

class ActiveRecordML extends ActiveRecord
{

    const SCENARIO_CREATE = 'create';

    /**
     * @var array, the list of support locales for multi-language versions, like: `en-US`, `ru-RU`
     * @note This variable will be override if you use the `wdmg\yii2-translations` module.
     */
    public $supportLocales = [];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = parent::rules();

        if ($this->hasAttribute('parent_id')) {
            $rules[] = ['parent_id', 'integer'];
            $rules[] = ['parent_id', 'checkParent'];
        }

        if ($this->hasAttribute('source_id')) {
            $rules[] = ['source_id', 'integer'];
            $rules[] = ['source_id', 'checkSource'];
        }

        if ($this->hasAttribute('locale')) {
            $rules[] = ['locale', 'string', 'max' => 10];
            $rules[] = ['locale', 'checkLocale', 'on' => self::SCENARIO_CREATE];
        }

        if ($this->hasAttribute('parent_id') && $this->hasAttribute('source_id') && $this->hasAttribute('locale')) {
            $rules[] = [['parent_id', 'source_id', 'locale'], 'doublesCheck', 'on' => self::SCENARIO_CREATE];
        }

        if ($this->hasAttribute('alias')) {
            $rules[] = ['alias', 'checkAlias'];
        }

        return $rules;
    }

    /**
     * Checks that the current element should not refer to itself and that the same language version or
     * language version of the parent does not exist. Used in accordance with the scenario when
     * creating a new model element.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function doublesCheck($attribute, $params)
    {
        $hasError = false;
        if (isset($this->parent_id)) {

            if ($this->id == $this->parent_id)
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'The current item should not link to itself.'));
        }

        $hasError = false;
        if (isset($this->parent_id) && isset($this->source_id) && isset($this->locale)) {
            if (!empty($this->parent_id) && !empty($this->source_id) && !empty($this->locale)) {

                if (self::find()->where(['parent_id' => $this->parent_id, 'source_id' => $this->source_id, 'locale' => $this->locale])->count())
                    $hasError = true;

            }
        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'It seems the same language version of this item or child item already exists.'));
        }

        return $hasError;
    }

    /**
     * Checks if the current language version links of item to the language version of parent (source).
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkParent($attribute, $params)
    {
        $hasError = false;
        if (isset($this->parent_id) && isset($this->source_id)) {
            if (!empty($this->parent_id) && !empty($this->source_id)) {

                if (self::find()->where(['id' => $this->parent_id])->andWhere(['!=', 'source_id', null])->count())
                    $hasError = true;

            }
        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'Child item cannot link to the language version of parent.'));
        }

        return $hasError;
    }

    /**
     * Checks if the current language version of item refer to the source version.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkSource($attribute, $params)
    {
        $hasError = false;
        if (isset($this->source_id)) {

            if ($this->id == $this->source_id)
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'The language version must refer to the source version.'));
        }

        return $hasError;
    }

    /**
     * Checks if the same language version of item doesn't exists.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkLocale($attribute, $params)
    {
        $hasError = false;
        if (isset($this->locale) && isset($this->source_id)) {
            if (!empty($this->locale) && !empty($this->source_id)) {

                if (self::find()->where(['locale' => $this->locale, 'source_id' => $this->source_id])->andWhere(['!=', 'id', $this->id])->count())
                    $hasError = true;

                if (self::find()->where(['locale' => $this->locale, 'id' => $this->source_id])->count())
                    $hasError = true;

            }
        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'A language version with the selected language already exists.'));
        }

        return $hasError;
    }

    /**
     * Checks if the alias of the current item is not an alias (duplicate) of the source version.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkAlias($attribute, $params)
    {
        $hasError = false;
        if (isset($this->alias) && isset($this->source_id)) {
            if (!empty($this->alias) && !empty($this->source_id)) {

                if (self::find()->where(['alias' => $this->alias])->andWhere(['!=', 'source_id', $this->source_id])->count())
                    $hasError = true;

                if (self::find()->where(['alias' => $this->alias, 'source_id' => null])->andWhere(['!=', 'id', $this->id])->count())
                    $hasError = true;

            }
        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/base', 'It seems the alias of the current version is a duplicate of the main version.'));
        }

        return $hasError;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        if ($this->hasAttribute('parent_id'))
            $labels[] = ['parent_id' => Yii::t('app/modules/base', 'Parent ID')];

        if ($this->hasAttribute('source_id'))
            $labels[] = ['source_id' => Yii::t('app/modules/base', 'Source ID')];

        if ($this->hasAttribute('locale'))
            $labels[] = ['locale' => Yii::t('app/modules/base', 'Locale')];

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // If the parent of current model was specified but the language version is retained, you must obtain one
        // `id` of the main (source) version of the model and link it the to the current
        if ($this->hasAttribute('source_id') && $this->hasAttribute('parent_id')) {
            if (is_null($this->source_id) && !is_null($this->parent_id)) {
                $source = self::findOne(['parent_id' => $this->parent_id, 'source_id' => null]);
                if (isset($source->id)) {
                    $this->source_id = $source->id;
                }
            }
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->hasAttribute('name') && $this->hasAttribute('title')) {

            if (empty($this->title) && !empty($this->name))
                $this->title = $this->name;

        }

        if ($this->hasAttribute('route')) {

            if (empty(trim($this->route)))
                $this->route = null;
            else
                $this->route = trim($this->route);

        }

        if ($this->hasAttribute('layout')) {

            if (empty(trim($this->layout)))
                $this->layout = null;
            else
                $this->layout = trim($this->layout);

        }

        if ($this->hasAttribute('parent_id')) {

            if ($this->parent_id == 0)
                $this->parent_id = null;
            else
                $this->parent_id = intval($this->parent_id);

            if ($this->hasAttribute('route')) {
                if (!is_null($this->parent_id))
                    $this->route = $this->getRoute();
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * Return the public routes for frontend URL`s
     *
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|null
     */
    public function getRoutes($asArray = false)
    {
        if ($this->hasAttribute('route')) {
            if ($asArray)
                return self::find()->select(['route'])->distinct()->asArray()->all();
            else
                return self::find()->select(['route'])->distinct()->all();
        } else {
            if ($asArray)
                return [];
            else
                return null;
        }
    }

    /**
     * Return base route for frontend URL`s with considering the path of the parent language version.
     *
     * @param null $route
     * @return mixed|string|null
     */
    public function getRoute($route = null)
    {
        if (is_null($route) && !is_null($this->baseRoute)) {
            $route = $this->baseRoute;
        }

        if (isset($this->source_id) && isset($this->parent_id) && isset($this->locale)) {

            if ($parent = self::find()->where(['source_id' => intval($this->parent_id), 'locale' => $this->locale])->one())
                return $parent->getRoute($route) ."/". $parent->alias;

        } elseif (isset($this->parent_id)) {

            if ($parent = self::find()->where(['id' => intval($this->parent_id)])->one())
                return $parent->getRoute($route) ."/". $parent->alias;

        }

        return $route;
    }

    /**
     * Build and return the frontend URL to the view of the current model with multi-language support
     *
     * @param bool $withScheme
     * @param bool $realUrl
     * @return mixed|string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getModelUrl($withScheme = true, $realUrl = false)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
                $translations = Yii::$app->translations->module;
                if ($config = $translations->urlManagerConfig) {

                    if (isset($config['class']))
                        unset($config['class']);

                    // Init UrlManager and configure
                    $urlManager = new \wdmg\translations\components\UrlManager($config);
                    if (($this->hasAttribute('status') && $this->getAttribute('status') == self::STATUS_DRAFT) && $realUrl) {
                        if ($withScheme)
                            return \yii\helpers\Url::to(['default/view', 'route' => $this->route, 'alias' => $this->alias, 'draft' => 'true'], $withScheme);
                        else
                            return \yii\helpers\Url::to(['default/view', 'route' => $this->route, 'alias' => $this->alias, 'draft' => 'true']);
                    } else {
                        if ($withScheme)
                            return $urlManager->createAbsoluteUrl([$this->route . '/' . $this->alias, 'lang' => $this->locale]);
                        else
                            return $urlManager->createUrl([$this->route . '/' . $this->alias, 'lang' => $this->locale]);
                    }
                }
            } else {
                if (($this->hasAttribute('status') && $this->getAttribute('status') == self::STATUS_DRAFT) && $realUrl) {
                    return \yii\helpers\Url::to(['default/view', 'route' => $this->route, 'alias' => $this->alias, 'draft' => 'true'], $withScheme);
                } else {
                    return \yii\helpers\Url::to($this->route . '/' . $this->alias);
                }
            }
        }

        return null;
    }

    /**
     * Returns a list of all language versions of current model
     *
     * @param null $source_id
     * @param bool $asArray
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]|null
     */
    public function getAllVersions($source_id = null, $asArray = false)
    {
        if (is_null($source_id))
            return null;

        if ($this->hasAttribute('source_id')) {

            $models = self::find()->andWhere(['id' => $source_id])->orWhere(['source_id' => $source_id]);

            if ($asArray)
                return $models->asArray()->all();
            else
                return $models;

        }

        return null;
    }

    /**
     * Returns all used languages
     *
     * @param null $id
     * @param bool $asArray
     * @return array
     */
    public function getLanguages($id = null, $asArray = false)
    {

        if (!($models = $this->getAllVersions($id, false))) {
            $models = self::find();
        }

        $languages = [];
        if ($this->hasAttribute('locale')) {

            $models->select('locale')->groupBy('locale');

            if ($asArray)
                $models->asArray();

            $locales = ArrayHelper::getColumn($models->all(), 'locale');
            foreach ($locales as $locale) {
                if (!is_null($locale)) {
                    if (extension_loaded('intl')) {
                        $languages[] = [
                            $locale => mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8"),
                        ];
                    } else {
                        $languages[] = [
                            $locale => $locale,
                        ];
                    }
                }
            }
        }

        return $languages;
    }

    /**
     * Returns a list of all available languages. Including taking into account already used as the language versions.
     * If the `wdmg\yii2-translations` module with the list of active languages is not available,
     * the `$supportLanguages` parameter of the current module will be used.
     *
     * @param bool $allLanguages
     * @return array
     */
    public function getLanguagesList($allLanguages = false)
    {
        $list = [];
        if ($allLanguages) {
            $list = [
                '*' => Yii::t('app/modules/base', 'All languages')
            ];
        }

        $languages = $this->getLanguages(null, false);
        if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
            $locales = Yii::$app->translations->getLocales(false, false, true);
            $languages = array_diff_assoc(ArrayHelper::map($locales, 'locale', 'name'), $languages);
        } elseif (isset($this->supportLocales)) {
            if (is_array($this->supportLocales)) {
                $supportLanguages = [];
                $locales = $this->supportLocales;
                foreach ($locales as $locale) {

                    if (extension_loaded('intl'))
                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                    else
                        $language = $locale;

                    $supportLanguages = ArrayHelper::merge($supportLanguages, [$locale => $language]);
                }

                $languages = array_diff_assoc($supportLanguages, $languages);
            }
        }

        $list = ArrayHelper::merge($list, $languages);

        return $list;
    }
}