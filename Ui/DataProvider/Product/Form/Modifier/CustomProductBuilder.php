<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Ui\DataProvider\Product\Form\Modifier;

use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Model\Locator\LocatorInterface;
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
            'handle' => 'custom_product_builder',
            'store' => $this->locator->getStore()->getId(),
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
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'onCancel' => 'actionCancel',
                            'options' => [
                                'title' => __('Custom Product Builder'),
                            ],
                            'imports' => [
                                'state' => '!index=product_builder_save:responseStatus'
                            ],

                        ],
                    ],
                ],
                'children' => [
                    'product_builder' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/insert-form',
                                    'dataScope' => '',
                                    'update_url' => $this->context->getUrl()->getUrl('mui/index/render_handle', $params),
                                    'render_url' => $this->context->getUrl()->getUrl('mui/index/render_handle', $params),
                                    'autoRender' => false,
                                    'ns' => 'custom_product_builder',
                                    'externalProvider' => 'custom_product_builder.builder_form_data_source',
                                    'toolbarContainer' => '${ $.parentName }',
                                    'formSubmitType' => 'ajax',
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

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Custom Product Builder'),
                            'dataScope' => '',
                            'breakLine' => false,
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'component' => 'Magento_Ui/js/form/components/group',
                            'scopeLabel' => __('[GLOBAL]'),
                        ],
                    ],
                ],
                'children' => [
                    $fieldCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'hidden',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'componentType' => 'field',
                                    'filterOptions' => true,
                                    'chipsEnabled' => true,
                                    'disableLabel' => true,
                                    'elementTmpl' => 'ui/form/element/hidden',
                                    'config' => [
                                        'dataScope' => $fieldCode,
                                        'sortOrder' => 10,
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'custom_product_builder_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title' => __('Configure'),
                                    'formElement' => 'container',
                                    'additionalClasses' => 'admin__field-small',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/button',
                                    'template' => 'ui/form/components/button/container',
                                    'actions' => [
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
                                    'provider' => false,
                                    'source' => 'product_details',
                                    'displayArea' => 'insideGroup',
                                    'sortOrder' => 20,
                                ],
                            ],
                        ]
                    ],

                    'custom_product_builder_export' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title' => __('Export'),
                                    'formElement' => 'container',
                                    'additionalClasses' => 'admin__field-small',
                                    'componentType' => 'container',
                                    'component' => 'Buildateam_CustomProductBuilder/js/iebutton',
                                    'template' => 'ui/form/components/button/container',
                                    'additionalForGroup' => true,
                                    'provider' => false,
                                    'source' => 'product_details',
                                    'displayArea' => 'insideGroup',
                                    'sortOrder' => 30,
                                    'actions' => [
                                        [
                                            'onclick' => sprintf("setLocation('%s');", $this->context->getUrl()->getUrl('customproductbuilder/product/exportFile', [
                                                'id' => $this->context->getRequest()->getParam('id')
                                            ]))
                                        ]
                                    ],
                                ],
                            ],
                        ]
                    ],

                    'custom_product_builder_import' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title' => __('Import'),
                                    'formElement' => 'fileUploader',
                                    'componentType' => 'fileUploader',
                                    'component' => 'Magento_Ui/js/form/element/file-uploader',
                                    'template' => 'custom-product-builder/form/element/uploader/uploader',
                                    'dataScope' => 'file',
                                    'fileInputName' => 'json_configuration',
                                    'additionalForGroup' => true,
                                    'provider' => false,
                                    'source' => 'product_details',
                                    'displayArea' => 'insideGroup',
                                    'sortOrder' => 40,
                                    'allowedExtensions' => 'json',
                                    'uploaderConfig' => [
                                        'url' => $this->context->getUrl()->getUrl('customproductbuilder/product/importFile', [
                                            'id' => $this->context->getRequest()->getParam('id')
                                        ])
                                    ]
                                ],
                            ],
                        ]
                    ],
                ]
            ]
        );

        return $meta;
    }
}
