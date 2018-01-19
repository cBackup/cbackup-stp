<?php
/**
 * cBackup STP Mapping Plugin
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace app\modules\plugins\stpmapping\widgets;

use yii\base\Widget;
use app\models\OutStp;
use app\modules\plugins\stpmapping\models\Stp;
use app\modules\plugins\stpmapping\assets\StpAsset;


/**
 * @package app\modules\plugins\stpmapping\widgets
 */
class StpMappingWidget extends Widget
{

    /**
     * @var int
     */
    public $node_id = '';

    /**
     * Plugin context
     *
     * @var object
     */
    public $plugin;

    /**
     * @var string
     */
    public $tree = '';

    /**
     * Prepare dataset
     *
     * @return void
     */
    public function init()
    {
        /** Access plugin data */
        $this->plugin = \Yii::$app->getModule('plugins/stpmapping');

        /** Register Asset */
        StpAsset::register($this->getView());

        $stp_data = (new OutStp())->createStpTree($this->node_id);

        /** Generate tree visualization if stp data exists */
        if (!empty($stp_data)) {
            $this->tree = (new Stp())->generateTree($stp_data, $this->node_id);
        }
    }

    /**
     * Render stp mapping view
     *
     * @return string
     */
    public function run()
    {
        return $this->getView()->renderAjax('stp_mapping_widget', [
            'plugin' => $this->plugin,
            'tree'   => $this->tree,
        ], $this);
    }

}
