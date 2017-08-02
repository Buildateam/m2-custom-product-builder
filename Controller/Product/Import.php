<?php
/**
 * Saraiva Platform | Fcamara
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.fcamara.com.br for more information.
 *
 * @category  ${MAGENTO_MODULE_NAMESPACE}
 * @package   ${MAGENTO_MODULE_NAMESPACE}_${MAGENTO_MODULE}
 *
 * @copyright Copyright (c) 2017 Fcamara - Saraiva Platform (http://www.fcamara.com.br)
 *
 * @author    Tiago Daniel <tiago.daniel@fcamara.com.br>
 */

namespace Buildateam\CustomProductBuilder\Controller\Product;

use Magento\Framework\Controller\ResultFactory;


class Import extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonHelper;
    protected $_helper;
    protected $_jsonProductContent;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Buildateam\CustomProductBuilder\Helper\Data $helper
    ) {
        $this->_jsonHelper          = $jsonHelper;
        $this->_resultPageFactory   = $resultFactory;
        $this->_productRepository   = $productRepository;
        $this->_helper              = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $params         = $this->getRequest()->getParams('product_id');
        $product        = $this->_productRepository->getById($params['product_id']);
        $jsonData       = !empty($_FILES['product']['tmp_name']['json_configuration'])
            ? file_get_contents($_FILES['product']['tmp_name']['json_configuration'])
            : $product->getData('json_configuration');

        $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $response       = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');

        if (!empty($jsonData)) {
            $this->_jsonProductContent = $jsonData;
            $validate = $this->_helper->validate($this->_jsonProductContent);

            if (isset($this->_jsonProductContent) && !empty($this->_jsonProductContent) && $validate) {
                $this->setErrorResponse($response, $validate);
                return $response;
            }

            $product->setJsonConfiguration($this->_jsonProductContent);
            $product->save();
            $this->setSuccessResponse($response);

            return $response;

        }

    }

    protected function setErrorResponse($response, $validate)
    {
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success'       => false,
                    'message'       => $validate,
                ]
            )
        );
    }

    protected function setSuccessResponse($response)
    {
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success'       => true,
                    'message'       => 'Custom json configuration added with success!',
                ]
            )
        );
    }

}