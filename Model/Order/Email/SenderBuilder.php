<?php

namespace Buildateam\CustomProductBuilder\Model\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilder as BaseSender;

class SenderBuilder extends BaseSender
{
    /**
     * @var TransportBuilderByStore
     */
    private $transportBuilderByStore;

    public function __construct(Template $templateContainer, IdentityInterface $identityContainer, TransportBuilder $transportBuilder, TransportBuilderByStore $transportBuilderByStore = null)
    {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
        $this->transportBuilderByStore = $transportBuilderByStore ?: ObjectManager::getInstance()->get(
            TransportBuilderByStore::class
        );
    }

    public function send()
    {
        $this->sendToCustomer();
        $this->sendCopyWithAllOptions();
    }

    public function sendToCustomer()
    {
        $this->configureEmailTemplate();
        $this->transportBuilder->setTemplateIdentifier('sales_email_order_custom_template');

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    public function sendCopyWithAllOptions()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                $this->transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
                $this->transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
                $this->transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
                $this->transportBuilder->addTo($email);

                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }
}