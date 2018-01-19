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

namespace app\modules\plugins\stpmapping\models;

use yii\base\Model;
use yii\helpers\Html;
use app\models\Credential;
use app\models\Node;
use app\models\Network;
use dautkom\netsnmp\NetSNMP;


/**
 * @package app\modules\plugins\stpmapping\models
 */
class Stp extends Model
{

    /**
     * @var \app\modules\plugins\stpmapping\StpMapping
     */
    public $module;

    /**
     * @var string
     */
    public $criteria = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->module = \Yii::$app->getModule('plugins/stpmapping');
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['criteria'], 'required'],
            [['criteria'], 'filter', 'filter'=>'strtolower'],
            [['criteria'], 'ip', 'ipv6' => false, 'when' => function($model) { /** @var $model Stp */
                return (strpos($model->criteria, '.') !== false);
            }],
            [['criteria'], 'filter',
                'filter' => function($value) {
                    return preg_replace('/[^a-z0-9]/i', '', $value);
                },
                'when' => function($model) { /** @var $model Stp */
                    return (strpos($model->criteria, ':') !== false);
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'criteria' => $this->module::t('general', 'Search criteria'),
        ];
    }

    /**
     * Generate Stp tree
     *
     * @param  array $stp_tree
     * @param  int $highlight_id
     * @return string
     */
    public function generateTree($stp_tree, $highlight_id = null)
    {

        $result  = '';

        foreach ($stp_tree as $node) {

            $options = ['target' => '_blank', 'style' => ''];

            $result .= Html::beginTag('li');

                $content = [
                    'location'  => Html::tag('span', $node['location'], ['class' => 'text-bold']),
                    'device'    => $node['device'],
                    'node_ip'   => $node['node_ip'],
                    'root_port' => "{$this->module::t('general', 'Root port:')} {$node['root_port']}",
                ];
				
				/** Add hostname if exists */
                if (!is_null($node['hostname'])) {
                    array_splice($content, 1, 0, ['hostname'  => $node['hostname']]);
                }

                /** Check if node is available */
                if ($this->module->params['check_node_availability'] == '1') {
                    try {
                        $this->checkNodeAvailability($node['id']);
                    } catch (\Exception $e) {
                        $options = [
                            'class'          => 'text-danger',
                            'data-toggle'    => 'tooltip',
                            'data-placement' => 'right',
                            'data-html'      => 'true',
                            'title'          => $e->getMessage(),
                            'target'         => '_blank'
                        ];
                    }
                }

                /** Highlight active node */
                if (!is_null($highlight_id) && $highlight_id == $node['id']) {
                    $options['style'] = 'background-color: #deeefa; border-color: #94a0b4';
                }

                $result .= Html::a(implode('</br>', $content), ['/node/view', 'id' => $node['id']], $options);

                if (array_key_exists('children', $node) && is_array($node['children']) ){
                    $result .= Html::beginTag('ul');
                        $result .= $this->generateTree($node['children'], $highlight_id);
                    $result .= Html::endTag('ul');
                }

            $result.= Html::endTag('li');

        }

        return $result;

    }

    /**
     * Check node availability
     *
     * @param  int $id
     * @return bool
     * @throws \Exception
     */
    public function checkNodeAvailability($id)
    {

        $node = Node::find()->where(['id' => $id])->one();

        $credential_id = $node->credential_id;

        if (!is_null($node->network_id)) {
            $network       = Network::find()->where(['id' => $node->network_id])->asArray()->one();
            $credential_id = is_null($node->credential_id) ? $network['credential_id'] : $node->credential_id;
        }

        $credentials = Credential::find()->where(['id' => $credential_id])->asArray()->one();

        if( empty($credentials) ) {
            throw new \Exception(\Yii::t('network', 'Unable to find credential data'));
        }

        try {
            $snmp_options = [
                $credentials['snmp_read'],
                $credentials['snmp_set'],
                $this->module->params['snmp_timeout'],
                $this->module->params['snmp_retries']
            ];
            $snmp = (new NetSNMP())->init($node->ip, $snmp_options, $credentials['snmp_version']);
            return boolval($snmp->get('1.3.6.1.2.1.1.3.0'));
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }

    }

}
