<?php

namespace Magenest\SpecialCustomerProgram\ViewModel\Adminhtml\Order\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class SpecialCustomerProgram implements ArgumentInterface
{
    /**
     * @var \Magenest\SpecialCustomerProgram\Helper\File
     */
    private $file;

    /**
     * @param \Magenest\SpecialCustomerProgram\Helper\File $file
     */
    public function __construct(\Magenest\SpecialCustomerProgram\Helper\File $file)
    {
        $this->file = $file;
    }

    /**
     * @param $image
     * @return mixed
     */
    public function getSrcImage($image)
    {
        return $this->file->getFullFileOptions($image)['url'];
    }
}
