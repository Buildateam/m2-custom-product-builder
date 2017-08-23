<?php


namespace Buildateam\CustomProductBuilder\Controller\Product;


class Uploadimage extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $i=1;
        $imageUrl= "http://i.imgur.com/fHyEMsl.jpg";
        $body = json_encode($imageUrl);

        $result = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;
        
    }

}