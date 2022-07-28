<?php
namespace Magenest\FbChatbot\Ui\Component\Listing\Columns;

use Magenest\FbChatbot\Model\Menu;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MenuActions extends Column{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'chatbot/menu/edit',
                        ['id' => $item['menu_id']]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                if (!in_array($item[Menu::ID],Menu::MENU_CANNOT_DELETE)){
                    $item[$this->getData('name')]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'chatbot/menu/delete',
                            ['id' => $item['menu_id']]
                        ),
                        'label' => __('Delete'),
                        'hidden' => false,
                    ];
                }
                $item[$this->getData('name')]['duplicate'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'chatbot/menu/save',
                        ['id' => $item['menu_id']]
                    ),
                    'label' => __('Duplicate'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
