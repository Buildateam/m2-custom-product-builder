<?php
namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Dummy;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/** Dummy controller, for checking session */
class Index extends Action
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * Index constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData([]);
        return $response;
    }
}
