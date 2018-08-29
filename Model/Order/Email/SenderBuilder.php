<?php
namespace Buildateam\CustomProductBuilder\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilder as BaseSender;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\MessageInterface;

class SenderBuilder extends BaseSender
{
    /**
     * SenderBuilder constructor.
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        ObjectManagerInterface $objectManager
    ) {
        /** @var MessageInterface $message */
        $message = $objectManager->create(MessageInterface::class);
        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = $objectManager->create(
            TransportBuilder::class,
            ["message" => $message]
        );
        /** @var TransportBuilderByStore $transportBuilderByStore */
        $transportBuilderByStore = $objectManager->create(
            TransportBuilderByStore::class,
            ["message" => $message]
        );
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
    }

    /**
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send()
    {
        $this->sendToCustomer();
        $this->sendCopyWithAllOptions();
    }

    /**
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendToCustomer()
    {
        $this->configureEmailTemplate();
        if ($this->templateContainer->getTemplateId() == 'sales_email_order_template') {
            $this->transportBuilder->setTemplateIdentifier('sales_email_order_custom_template');
        } elseif ($this->templateContainer->getTemplateId() == 'sales_email_order_guest_template') {
            $this->transportBuilder->setTemplateIdentifier('sales_email_order_guest_custom_template');
        }

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendCopyWithAllOptions()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                $this->configureEmailTemplate();
                $this->transportBuilder->setFrom(
                    $this->identityContainer->getEmailIdentity(),
                    $this->identityContainer->getStore()->getId()
                );
                $this->transportBuilder->addTo($email);

                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }
}