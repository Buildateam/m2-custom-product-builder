<?php

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
