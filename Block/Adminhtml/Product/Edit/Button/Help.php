<?php
/**
 * PhpStorm
 *
 * NOTICE OF LICENSE
 *
 * Copyright 2018 Profit Soft (http://profit-soft.pro/)
 *
 * Licensed under the Apache License, Version 2.0 (the “License”);
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an “AS IS” BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @package    PhpStorm
 * @author     den4ik <dv.kopylov@profit-soft.pro>
 * @copyright  Copyright (c) 2018 Profit Soft (http://profit-soft.pro/)
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (Apache-2.0)
 */

namespace Buildateam\CustomProductBuilder\Block\Adminhtml\Product\Edit\Button;

/**
 * Class Help
 * @package Buildateam\CustomProductBuilder\Block\Adminhtml\Product\Edit\Button
 */
class Help extends Save
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Help'),
            'class' => 'help',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getTargetName(),
                                'actionName' => $this->getActionName(),
                                'params' => [
                                    'SHOW_HELP'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'on_click' => '',
        ];
    }
}
