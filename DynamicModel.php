<?php

namespace wdmg\base;
use wdmg\helpers\ArrayHelper;

/**
 * Yii2 DynamicModel
 *
 * @category        Model
 * @version         1.1.7
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

class DynamicModel extends \yii\base\DynamicModel {

    protected $_labels = [];

    public function setAttributeLabel($label) {
        $this->_labels = ArrayHelper::merge($this->_labels, $label);
    }

    public function setAttributeLabels($labels) {
        $this->_labels = $labels;
    }

    public function attributeLabels() {
        return $this->_labels;
    }
}
