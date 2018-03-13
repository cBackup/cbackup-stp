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

namespace app\modules\plugins\stpmapping\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use yii\helpers\Json;
use yii\web\Controller;
use app\modules\plugins\stpmapping\models\Stp;
use app\models\OutStp;


/**
 * @package app\modules\plugins\stpmapping\controllers
 */
class StpController extends Controller
{

    /**
     * @var \app\modules\plugins\stpmapping\StpMapping
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [$this->module->params['plugin_access']],
                    ],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::className(),
                'only'  => [
                    'ajax-generate-stp'
                ]
            ]
        ];
    }


    /**
     * Renders the index view
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * Generate Spanning Tree via Ajax
     *
     * @return string
     */
    public function actionAjaxGenerateStp()
    {
        $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];
        $model    = new Stp();

        if (isset($_POST['criteria'])) {

            $model->setAttributes($_POST);

            if ($model->validate()) {

                $stp_tree = (new OutStp())->createStpTree($model->criteria);

                if (!empty($stp_tree)) {
                    $response = ['status' => 'success', 'data' => $model->generateTree($stp_tree)];
                }
                else {
                    $response = ['status' => 'warning', 'msg' => $this->module::t('general', 'Nothing was found by search criteria: {0}', $model->criteria)];
                }

            } else {
                $response = ['status' => 'validation_error', 'errors' => $model->errors];
            }

        }

        return Json::encode($response);
    }

}
