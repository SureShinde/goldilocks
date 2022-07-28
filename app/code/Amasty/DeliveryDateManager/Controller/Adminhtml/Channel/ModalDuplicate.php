<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\CompositeResolver;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ModalDuplicate extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CompositeResolver
     */
    private $duplicateResolver;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        CompositeResolver $duplicateResolver
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->duplicateResolver = $duplicateResolver;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $message = '';
        $error = false;
        $newModalId = null;

        $id = (int)$this->getRequest()->getParam('id');
        $type = $this->getRequest()->getParam('type');

        try {
            $newModalId = $this->duplicateResolver->execute($id, $type);
        } catch (\Exception $e) {
            $error = true;
            $message = __('We can\'t duplicate entity right now.');
            $this->logger->critical($e);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'id' => $newModalId,
                'message' => $message,
                'error' => $error
            ]
        );

        return $resultJson;
    }
}
