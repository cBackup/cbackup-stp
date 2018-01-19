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
 *
 * @var $this          yii\web\View
 * @var $module        app\modules\plugins\stpmapping\StpMapping
 */

use yii\helpers\Html;
use yii\helpers\Url;

app\assets\LaddaAsset::register($this);
app\modules\plugins\stpmapping\assets\StpAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$module = $this->context->module;

$this->title = $module::t('general', 'Stp mapping');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plugins')];
$this->params['breadcrumbs'][] = ['label' => $this->title];

/** Register JS */
$this->registerJs(
    /** @lang JavaScript */
    "

        /** Expand stp box for better visability  */
        $(document).on('click', '#box_expand_btn', function () {
            var tree_box = $('#tree_box');
        
            if (tree_box.hasClass('box-fullscreen')) {
                $(this).find('i').switchClass('fa-compress', 'fa-expand', 0);
            } else {
                $(this).find('i').switchClass('fa-expand', 'fa-compress', 0);
            }
        
            tree_box.toggleClass('box-fullscreen');
        });
        
        /** Prevent tooltip from repeated showing after redirect */
        $(window).focus(function() {
            $('a').focus(function() {
                this.blur();
            });
        });
        
        /** Trigger tree generate method on enter key press */
        $(document).keydown(function(e) {
            if (e.keyCode === 13) {
                $('#generate_tree').trigger('click');
            }
        });

        /** Generate tree via Ajax */
        $(document).on('click', '#generate_tree', function () {

            var ajax_url = $(this).data('ajax-url');
            var criteria = $('#search_criteria').val();
            var btn_lock = Ladda.create(document.querySelector('#' + $(this).attr('id')));
        
            //noinspection JSUnusedGlobalSymbols
            $.ajax({
                type: 'POST',
                url: ajax_url,
                data: {criteria: criteria},
                beforeSend: function() {
                    toastr.clear();
                    $('#search_criteria').closest('.form-group').removeClass('has-error');
                    btn_lock.start();
                },
                success: function (data) {
                    
                    if (isJson(data)) {

                        var json = $.parseJSON(data);
        
                        if (json['status'] === 'success') {
                            $('#stp_tree').html(json['data']).closest('.panel').removeClass('hidden');
                            $('#info_message').hide();
                        } else if (json['status'] === 'validation_error') {
                             $('#search_criteria').closest('.form-group').addClass('has-error');
                             $.each(json['errors'], function(id, msg) {
                                toastr.error(msg, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
                            });
                            return false;
                        } else {
                            $('#stp_tree').html('').closest('.panel').addClass('hidden');
                            $('#info_message').show();
                            showStatus(data);
                        }
                    }
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            }).always(function () {
                btn_lock.stop();
            });

        });
        
    "
);
?>

<div class="row">

    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $module::t('general', 'Search criteria') ?></h3>
            </div>
            <div class="box-body">
                <div class="input-group">
                    <div class="form-group">
                        <?php
                            echo Html::textInput('criteria', '', [
                                'id'          => 'search_criteria',
                                'class'       => 'form-control',
                                'placeholder' => $module::t('general', 'Enter search criteria')
                            ]);
                        ?>
                    </div>
                    <span class="input-group-addon">
                        <?php

                        $message = $module::t('general', 'Available search criteria:<br>node id, node mac, node ip');
                            echo Html::tag('span', '<i class="fa fa-question"></i>', [
                                'data-toggle'    => 'tooltip',
                                'data-placement' => 'top',
                                'data-html'      => 'true',
                                'title'          => Html::tag('div', $message, ['style' => 'text-align:left; white-space:pre; max-width:none;']),
                            ]);
                        ?>
                    </span>
                </div>
            </div>
            <div class="box-footer">
                <?php
                    echo Html::a($module::t('general', 'Generate'), 'javascript:void(0);', [
                        'id'            => 'generate_tree',
                        'class'         => 'btn btn-primary btn-sm pull-right ladda-button',
                        'data-ajax-url' => Url::to(['ajax-generate-stp']),
                        'data-style'    => 'expand-left'
                    ])
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div id="tree_box" class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $module::t('general', 'Network tree visualization') ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" id="box_expand_btn" class="btn btn-box-tool"><i class="fa fa-expand"></i></button>
                </div>
            </div>
            <div class="box-body no-padding">
                <div id="stp_panel" class="panel hidden" style="box-shadow: none;">
                    <div class="panel-body" style="overflow-x: auto;">
                        <div class="tree-view tree-view-center">
                            <ul id="stp_tree"></ul>
                        </div>
                    </div>
                </div>
                <div id="info_message" class="col-md-12">
                    <div class="callout callout-info" style="margin: 10px 0 10px 0;">
                        <p><?=  $module::t('general', 'Please enter search criteria') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
