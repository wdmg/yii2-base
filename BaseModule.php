<?php

namespace wdmg\base;

/**
 * Yii2 Base module
 *
 * @category        Module
 * @version         1.0.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use yii\base\BootstrapInterface;
use Yii;
use yii\base\Module;

/**
 * Base module definition class
 */
class BaseModule extends Module implements BootstrapInterface
{

    /**
     * @var string the prefix for routing of module
     */
    public $routePrefix = "admin";

    /**
     * @var string, the name of module
     */
    public $name = "Base module";

    /**
     * @var string, the description of module
     */
    public $description = "Base module interface";

    /**
     * @var string the vendor name of module
     */
    private $vendor = "wdmg";

    /**
     * @var string the module version
     */
    private $version = "1.0.0";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 10;

    /**
     * @var array of strings missing translations
     */
    public $missingTranslation;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set controller namespace for console commands
        if (Yii::$app instanceof \yii\console\Application)
            $this->controllerNamespace = 'wdmg\base\commands';

        // Set current version of module
        $this->setVersion($this->version);

        // Register translations
        $this->registerTranslations();

        // Normalize route prefix
        $this->routePrefixNormalize();
    }

    /**
     * Return module vendor
     * @var string of current module vendor
     */
    public function getVendor() {
        return $this->vendor;
    }

    /**
     * Return module version
     * @var string of current module version
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {

        // Log to debuf console missing translations
        if (is_array($this->missingTranslation) && YII_ENV == 'dev')
            Yii::warning('Missing translations: ' . var_export($this->missingTranslation, true), 'i18n');

        $result = parent::afterAction($action, $result);
        return $result;

    }

    // Registers translations for module
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['app/modules/' . $this->id] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/' . $this->vendor . '/yii2-' . $this->id . '/messages',
            'on missingTranslation' => function($event) {

                if (YII_ENV == 'dev')
                    $this->missingTranslation[] = $event->message;

            },
        ];

        // Name and description translation of module
        $this->name = Yii::t('app/modules/' . $this->id, $this->name);
        $this->description = Yii::t('app/modules/' . $this->id, $this->description);
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('app/modules/' . self::id .'/'. $category, $message, $params, $language);
    }

    /**
     * Normalize route prefix
     * @return string of current route prefix
     */
    public function routePrefixNormalize()
    {
        if(!empty($this->routePrefix)) {
            $this->routePrefix = str_replace('/', '', $this->routePrefix);
            $this->routePrefix = '/'.$this->routePrefix;
            $this->routePrefix = str_replace('//', '/', $this->routePrefix);
        }
        return $this->routePrefix;
    }

    /**
     * Build dashboard navigation items for NavBar
     * @return array of current module nav items
     */
    public function dashboardNavItems()
    {
        return [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id .'/'],
            'active' => in_array(\Yii::$app->controller->module->id, $this->id)
        ];
    }

    public function bootstrap($app)
    {
        // Get URL path prefix if exist
        if (isset($this->routePrefix)) {
            $app->getUrlManager()->enableStrictParsing = true;
            $prefix = $this->routePrefix . '/';
        } else {
            $prefix = '';
        }

        // Add module URL rules
        $app->getUrlManager()->addRules([
            $prefix . '<module:' . $this->id . '>/' => '<module>/' . $this->id . '/index',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/' => '<module>/<controller>',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>' => '<module>/<controller>/<action>',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
            [
                'pattern' => $prefix . '<module:' . $this->id . '>/',
                'route' => '<module>/' . $this->id . '/index',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/',
                'route' => '<module>/<controller>',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/',
                'route' => '<module>/<controller>/<action>',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>/',
                'route' => '<module>/<controller>/<action>',
                'suffix' => ''
            ]
        ], true);
    }
}