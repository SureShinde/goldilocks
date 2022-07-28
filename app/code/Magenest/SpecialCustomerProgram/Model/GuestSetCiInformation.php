<?php

namespace Magenest\SpecialCustomerProgram\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestSetCiInformation implements \Magenest\SpecialCustomerProgram\Api\GuestSetCiInformationInterface
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
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Magenest\SpecialCustomerProgram\Helper\File $file,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->file = $file;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartId = $quoteIdMask->getQuoteId();
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
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $cartId = $quoteIdMask->getQuoteId();
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
