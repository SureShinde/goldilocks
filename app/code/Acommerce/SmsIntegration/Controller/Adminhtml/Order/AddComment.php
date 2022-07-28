<?php
 namespace Acommerce\SmsIntegration\Controller\Adminhtml\Order;

 class AddComment extends  \Magento\Sales\Controller\Adminhtml\Order\AddComment
 {
     public function execute()
     {
         $order = $this->_initOrder();
         if ($order) {
             try {
                 $data = $this->getRequest()->getPost('history');
                 if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                     throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                 }

                 $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                 $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                 $notifysms = isset($data['is_customer_notified_sms']) ? $data['is_customer_notified_sms'] : false;

                 $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                 $history->setIsVisibleOnFront($visible);
                 $history->setIsCustomerNotified($notify);
                 $history->save();

                 $comment = trim(strip_tags($data['comment']));

                 $order->save();
                 /** @var OrderCommentSender $orderCommentSender */
                 $orderCommentSender = $this->_objectManager
                     ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                 $orderCommentSender->send($order, $notify, $comment);
                 if($data['status'] == 'ready_for_pickup'){
                     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                     $helper = $objectManager->get('Acommerce\SmsIntegration\Helper\Data')->smsReadyPickUpOrder($order, 'ready_for_pickup', $comment);
                 }elseif ($notifysms){
                     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                     $helper = $objectManager->get('Acommerce\SmsIntegration\Helper\Data')->smsCommentOnOrder($order, 'comment_order', $comment);
                 }
                 return $this->resultPageFactory->create();
             } catch (\Magento\Framework\Exception\LocalizedException $e) {
                 $response = ['error' => true, 'message' => $e->getMessage()];
             } catch (\Exception $e) {
                 $response = ['error' => true, 'message' => __('We cannot add order history.')];
             }
             if (is_array($response)) {
                 $resultJson = $this->resultJsonFactory->create();
                 $resultJson->setData($response);
                 return $resultJson;
             }
         }
         return $this->resultRedirectFactory->create()->setPath('sales/*/');
     }
 }

