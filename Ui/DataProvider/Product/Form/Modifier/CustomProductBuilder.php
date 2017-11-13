<?php
/**
 * Copyright © 2017 Indigo Geeks, Inc. All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions
 * are met:
 *
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * All advertising materials mentioning features or use of this software must display the following acknowledgement:
 * This product includes software developed by the the organization.
 * Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Ui\DataProvider\Product\Form\Modifier;

use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\App\CacheInterface;
use \Magento\Framework\DB\Helper as DbHelper;
use \Magento\Framework\Stdlib\ArrayManager;
use \Magento\Backend\App\Action\Context;

/**
 * Data provider for Custom Product Bulder field of product page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomProductBuilder extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param Context $context
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        Context $context
    )
    {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->createCustomProductBuilderModal($meta);
        $meta = $this->customizeCustomProductBuilderField($meta);

        return $meta;
    }


    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }


    /**
     * Create slide-out panel for modal editor
     *
     * @param array $meta
     * @return array
     */
    protected function createCustomProductBuilderModal(array $meta)
    {
        $params = [
            'handle'  => 'custom_product_builder',
            'store'   => $this->locator->getStore()->getId(),
            'product' => (int)$this->context->getRequest()->getParam('id'),
            'buttons' => 1
        ];

        return $this->arrayManager->set(
            'custom_product_builder_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate'    => false,
                            'componentType' => 'modal',
                            'onCancel'      => 'actionCancel',
                            'options'       => [
                                'title' => __('Custom Product Builder'),
                            ],
                            'imports'       => [
                                'state' => '!index=product_builder_save:responseStatus'
                            ],

                        ],
                    ],
                ],
                'children'  => [
                    'product_builder' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label'            => '',
                                    'componentType'    => 'container',
                                    'component'        => 'Magento_Ui/js/form/components/insert-form',
                                    'dataScope'        => '',
                                    'update_url'       => $this->context->getUrl()->getUrl('mui/index/render_handle', $params),
                                    'render_url'       => $this->context->getUrl()->getUrl('mui/index/render_handle', $params),
                                    'autoRender'       => false,
                                    'ns'               => 'custom_product_builder',
                                    'externalProvider' => 'custom_product_builder.builder_form_data_source',
                                    'toolbarContainer' => '${ $.parentName }',
                                    'formSubmitType'   => 'ajax',
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        );
    }


    protected function customizeCustomProductBuilderField($meta)
    {
        $fieldCode = 'json_configuration';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');

        if (!$elementPath) {
            return $meta;
        }

        //$meta = $this->arrayManager->remove($containerPath.'/children/'.$fieldCode, $meta);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Custom Product Builder'),
                            'dataScope'     => '',
                            'breakLine'     => false,
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'scopeLabel'    => __('[GLOBAL]'),
                        ],
                    ],
                ],
                'children'  => [
                    $fieldCode                      => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement'      => 'hidden',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'componentType'    => 'field',
                                    'filterOptions'    => true,
                                    'chipsEnabled'     => true,
                                    'disableLabel'     => true,
                                    'elementTmpl'      => 'ui/form/element/hidden',
                                    'config'           => [
                                        'dataScope' => $fieldCode,
                                        'sortOrder' => 10,
                                        'visible'   => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'custom_product_builder_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title'              => __('Configure'),
                                    'formElement'        => 'container',
                                    'additionalClasses'  => 'admin__field-small',
                                    'componentType'      => 'container',
                                    'component'          => 'Magento_Ui/js/form/components/button',
                                    'template'           => 'ui/form/components/button/container',
                                    'actions'            => [
                                        [
                                            'targetName' => 'product_form.product_form.custom_product_builder_modal',
                                            'actionName' => 'toggleModal',
                                        ],
                                        [
                                            'targetName' =>
                                                'product_form.product_form.custom_product_builder_modal.product_builder',
                                            'actionName' => 'render'
                                        ],
                                        [
                                            'targetName' =>
                                                'product_form.product_form.custom_product_builder_modal.product_builder',
                                            'actionName' => 'resetForm'
                                        ]
                                    ],
                                    'additionalForGroup' => true,
                                    'provider'           => false,
                                    'source'             => 'product_details',
                                    'displayArea'        => 'insideGroup',
                                    'sortOrder'          => 20,
                                ],
                            ],
                        ]
                    ],

                    'custom_product_builder_import' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title'              => __('Import'),
                                    'formElement'        => 'container',
                                    'additionalClasses'  => 'admin__field-small',
                                    'componentType'      => 'container',
                                    'component'          => 'Magento_Ui/js/form/components/button',
                                    'template'           => 'ui/form/components/button/container',
                                    'additionalForGroup' => true,
                                    'provider'           => false,
                                    'source'             => 'product_details',
                                    'displayArea'        => 'insideGroup',
                                    'sortOrder'          => 30,
                                ],
                            ],
                        ]
                    ],

                    'custom_product_builder_export' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title'              => __('Export'),
                                    'formElement'        => 'container',
                                    'additionalClasses'  => 'admin__field-small',
                                    'componentType'      => 'container',
                                    'component'          => 'Magento_Ui/js/form/components/button',
                                    'template'           => 'ui/form/components/button/container',
                                    'additionalForGroup' => true,
                                    'provider'           => false,
                                    'source'             => 'product_details',
                                    'displayArea'        => 'insideGroup',
                                    'sortOrder'          => 40,
                                    'actions'            => [],
                                    'on_click'           => sprintf("location.href = '%s';", $this->context->getUrl()->getUrl('*/*/')),
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        );

        return $meta;
    }
}
