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
 * @var $plugin \app\modules\plugins\stpmapping\StpMapping
 */
?>

<div class="row">
    <div class="col-md-12">
        <?php if (!empty($tree)): ?>
            <div id="stp_panel" class="panel" style="box-shadow: none;">
                <div class="panel-body" style="overflow-x: auto;">
                    <div class="tree-view tree-view-center">
                        <ul>
                            <?= $tree ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="callout callout-info" style="margin: 10px 0 10px 0;">
                <p><?= $plugin::t('general', 'Specified node does not exists in STP table') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
