<?php

namespace wdmg\base\behaviors;

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\SluggableBehavior as BaseSluggableBehavior;
use yii\helpers\Inflector;

/**
 * Yii2 SluggableBehavior
 *
 * @category        Helper
 * @version         1.3.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 - 2023 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

class SluggableBehavior extends BaseSluggableBehavior
{
    /**
     * @var The replacement to use for spaces
     */
    public $replacement = '-';

    /**
     * @var Whether to return the slug in lowercase
     */
    public $lowercase = true;

    /**
     * Transliterator locale (unicode normalization forms for INTL). See [[\yii\helpers\Inflector::transliterate()]]
     *
     * @var null|string
     */
    public $locale = null;

    /**
     * Fallback map for transliterator used by [[\yii\helpers\Inflector::transliterate()]] when INTL isn't available.
     *
     * @var null|array|callable
     */
    public $fallback = null;

    /**
     * Instance of yii\helpers\Inflector()
     *
     * @var object
     */
    private $_inflector;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->replacement === null)
            throw new InvalidConfigException('Either "replacement" property must be specified.');

        if (!is_string($this->replacement))
            throw new InvalidConfigException('Either "lowercase" property must be a string.');

        if ($this->lowercase === null)
            throw new InvalidConfigException('Either "lowercase" property must be specified.');

        if (!is_bool($this->lowercase))
            throw new InvalidConfigException('Either "lowercase" property must be a boolean.');

        if (!is_null($this->locale) && !is_string($this->locale))
            throw new InvalidConfigException('Either "locale" property must be a string.');

        if (!is_null($this->fallback) && !is_array($this->fallback) && !is_callable($this->fallback))
            throw new InvalidConfigException('Either "fallback" property must be a array or callable.');

        // Get Inflector helper
        $this->_inflector = new Inflector();

        // Configure  Inflector helper
        if (!is_null($this->locale) && is_string($this->locale))
            $this->_inflector::$transliterator = $this->locale;

        if (!is_null($this->fallback) && is_array($this->fallback))
            $this->_inflector::$transliteration = $this->fallback;
        elseif (!is_null($this->fallback) && is_callable($this->fallback))
            $this->_inflector::$transliteration = call_user_func($this->fallback, $this->_inflector::$transliteration);

    }

    /**
     * This method is called by [[getValue]] to generate the slug.
     * You may override it to customize slug generation.
     * The default implementation calls [[\yii\helpers\Inflector::slug()]] on the input strings
     * concatenated by the `replacement` properties.
     *
     * @param array $slugParts
     * @return string
     */
    protected function generateSlug($slugParts)
    {
        return $this->_inflector::slug(implode($this->replacement, $slugParts), $this->replacement, $this->lowercase);
    }
}