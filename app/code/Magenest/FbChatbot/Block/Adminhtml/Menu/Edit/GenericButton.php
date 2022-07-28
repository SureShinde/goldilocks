<?php
namespace Magenest\FbChatbot\Block\Adminhtml\Menu\Edit;

use Magenest\FbChatbot\Api\MenuRepositoryInterface;
use Magenest\FbChatbot\Model\Menu;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        MenuRepositoryInterface $menuRepository
    ) {
        $this->context = $context;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Return the current sales Menu Id.
     *
     * @return int|null
     */
    public function getMenuId()
    {
        try {
            $id = $this->menuRepository->getById(
                $this->context->getRequest()->getParam('id')
            )->getId();
            if (!in_array($id,Menu::MENU_CANNOT_DELETE))
                return $id;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

}
