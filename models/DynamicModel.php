<?php

namespace wdmg\base\models;

/**
 * Yii2 DynamicModel
 *
 * @category        Model
 * @version         1.3.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 - 2021 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

class DynamicModel extends \yii\base\DynamicModel {

    protected $_attributeLabels = [];
    protected $_formName;

    /**
     * Set one attribute label
     *
     * @param $label
     */
    public function setAttributeLabel($attribute, $label) {
        $this->_attributeLabels[$attribute] = $label;
        return $this;
    }

    /**
     * Set all attribute labels
     *
     * @param $labels array of attributes and labels, like ['name' => 'User name']
     */
    public function setAttributeLabels(array $labels = []) {
        $this->_attributeLabels = $labels;
    }

    /**
     * Returns all attribute labels
     *
     * @return array
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), $this->_attributeLabels);
    }

    /**
     * Returns the form name that this model class should use.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function formName()
    {
        return $this->_formName ?: parent::formName();
    }

    /**
     * Set form name in input attribute, like `SomeFormName[user-name]`
     * @param $name
     */
    public function setFormName($name)
    {
        $this->_formName = $name;
    }
}
