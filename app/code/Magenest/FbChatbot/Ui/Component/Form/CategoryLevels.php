<?php

namespace Magenest\FbChatbot\Ui\Component\Form;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class CategoryLevels extends AbstractSource implements SourceInterface, OptionSourceInterface{


    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllOptions()
    {
        $result = [];
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setStore($this->_storeManager->getStore());
        if ($categoryCollection){
            foreach ($categoryCollection as $category) {
                $result [] = ['value' => $category->getLevel(), 'label' => __('Level ') . $category->getLevel()];
                $result = array_map("unserialize", array_unique(array_map("serialize", $result)));
            }
        }

        return $result;
    }
}
