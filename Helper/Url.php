<?php

namespace Buildateam\CustomProductBuilder\Helper;

class Url
{

    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->urlBuilder = $urlBuilder;
    }
    public function getUrl()
    {
        return $this->urlBuilder->getUrl('json_config', ['_current' => true]);
    }
}
