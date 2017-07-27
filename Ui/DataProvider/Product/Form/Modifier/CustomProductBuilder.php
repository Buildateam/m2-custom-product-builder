<?php

namespace Buildateam\CustomProductBuilder\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for categories field of product page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomProductBuilder extends AbstractModifier
{

    /**
     * @var ArrayManager
     */
    protected $arrayManager;



    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        // $meta = $this->createCustomProductBuilderModal($meta);
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
                                    //'visible' = false;
                                    'componentType' => 'field',
                                    #'component' => 'Magento_Ui/js/form/components/html',
                                    'filterOptions' => true,
                                    'chipsEnabled' => true,
                                    'disableLabel' => true,
                                    'levelsVisibility' => '1',
                                    'elementTmpl' => 'ui/form/components/button/simple',
                                    'config' => [
                                        'dataScope' => $fieldCode,
                                        'sortOrder' => 10,
                                        'visible'=>false

                                    ],
                                ],
                            ],
                        ],
                    ],
                    'custom_product_builder_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title' => __('Customise'),
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
                                                'product_form.product_form.custom_product_builder_modal.create_category',
                                            'actionName' => 'render'
                                        ],
                                        [
                                            'targetName' =>
                                                'product_form.product_form.custom_product_builder_modal.create_category',
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

                    'custom_product_builder_import' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'title' => __('Import'),
                                    'formElement' => 'container',
                                    'additionalClasses' => 'admin__field-small',
                                    'componentType' => 'container',
                                    'component' => 'Magento_Ui/js/form/components/button',
                                    'template' => 'ui/form/components/button/container',
                                    'additionalForGroup' => true,
                                    'provider' => false,
                                    'source' => 'product_details',
                                    'displayArea' => 'insideGroup',
                                    'sortOrder' => 30,
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
                                    'component' => 'Magento_Ui/js/form/components/button',
                                    'template' => 'ui/form/components/button/container',
                                    'additionalForGroup' => true,
                                    'provider' => false,
                                    'source' => 'product_details',
                                    'displayArea' => 'insideGroup',
                                    'sortOrder' => 40,
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