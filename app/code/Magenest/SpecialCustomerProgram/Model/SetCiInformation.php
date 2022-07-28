<?php

namespace Magenest\SpecialCustomerProgram\Model;

use Magento\Quote\Api\CartRepositoryInterface;

class SetCiInformation implements \Magenest\SpecialCustomerProgram\Api\SetCiInformationInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var \Magenest\SpecialCustomerProgram\Helper\File
     */
    private $file;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Magenest\SpecialCustomerProgram\Helper\File $file
    ) {
        $this->cartRepository = $cartRepository;
        $this->file = $file;
    }

    /**
     * @param array $param
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $param)
    {
        $cartId = $param['cartId'];
        $quote = $this->cartRepository->getActive($cartId);
        $ciImage = $this->file->moveFileFromTmp($param['ci_image']);
        $quote->setData('ci_number', $param['ci_number']);
        $quote->setData('ci_full_name', $param['ci_full_name']);
        $quote->setData('ci_image', $ciImage);
        $quote->setData('special_customer_program', true);
        $this->cartRepository->save($quote);
        return true;
    }

    /**
     * @param array $param
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function remove(array $param)
    {
        $cartId = $param['cartId'];
        $quote = $this->cartRepository->getActive($cartId);
        $this->file->removeFile($quote->getData('ci_image'));
        $quote->setData('ci_number', '');
        $quote->setData('ci_full_name', '');
        $quote->setData('ci_image', '');
        $quote->setData('special_customer_program', false);
        $this->cartRepository->save($quote);
        return true;
    }
}
