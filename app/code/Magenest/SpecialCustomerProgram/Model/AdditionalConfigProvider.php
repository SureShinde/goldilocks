<?php

namespace Magenest\SpecialCustomerProgram\Model;

use Magenest\SpecialCustomerProgram\Helper\File;
use Magento\Checkout\Model\Session;

class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var File
     */
    private $fileHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @param File $fileHelper
     */
    public function __construct(
        File $fileHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $session,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->fileHelper = $fileHelper;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $output['uploadImageUrl'] = $this->urlInterface->getUrl('program/image/UploadCiImage');
        $ciImage = $this->session->getQuote()->getData('ci_image');
        if ($ciImage) {
            $image = $this->fileHelper->getFullFileOptions($ciImage)['url'];
            $output['ci_image_src'] = $image;
        } else {
            $output['ci_image_src'] = null;
        }

        return $output;
    }
}
