<?php
namespace Magenest\Popup\Block\Popup;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Button extends Template
{
    /** @var  PopupFactory */
    protected $_popupFactory;

    /** @var Display */
    protected $_display;

    /** @var Popup */
    private $popupResources;

    /** @var \Magenest\Popup\Model\Popup */
    private $cachedPopup = null;

    /**
     * Button constructor.
     * @param PopupFactory $popupFactory
     * @param Display $display
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        PopupFactory $popupFactory,
        Display $display,
        Context $context,
        $data = []
    ) {
        $this->_popupFactory = $popupFactory;
        $this->_display = $display;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magenest\Popup\Model\Popup
     * @throws \Exception
     */
    private function getPopup()
    {
        if (empty($this->cachedPopup)) {
            $this->cachedPopup = $this->_display->getPopup();
        }

        return $this->cachedPopup;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkEnableButton()
    {
        $popup = $this->getPopup();
        return !empty($popup->getPopupStatus()) && !empty($popup->getEnableFloatingButton());
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function setButtonId()
    {
        $displayPopup = $this->getPopup()->getFloatingButtonDisplayPopup();
        return $displayPopup == 0 ? 'floating-button-before' : 'floating-button-after';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function setDisplayButton()
    {
        $displayPopup = $this->getPopup()->getFloatingButtonDisplayPopup();
        return $displayPopup == 0 ? "display: none;" : "display: block;";
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getButtonContent()
    {
        return $this->getPopup()->getFloatingButtonContent();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTextButtonColor()
    {
        return $this->getPopup()->getFloatingButtonTextColor();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTextButtonHoverColor()
    {
        return $this->getPopup()->getFloatingButtonTextHoverColor();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getBackgroundButtonColor()
    {
        return $this->_display->getPopup()->getFloatingButtonBackgroundColor();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getHoverButtonColor()
    {
        return $this->_display->getPopup()->getFloatingButtonHoverColor();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getPositionButton()
    {
        $position = $this->getPopup()->getFloatingButtonPosition();
        if ($position == 0) {
            $positionStyle = 'right: 50%; bottom: 0; transform: translate(0, -85%); max-width: 100vw; left: unset;';
        } elseif ($position == 1) {
            $positionStyle = 'right: unset; bottom: 0; transform: translate(0, -85%); max-width: 100vw; left: 0;';
        } else {
            $positionStyle = 'right: 0; bottom: 0; transform: translate(0, -85%); max-width: 100vw; left: unset;';
        }
        return $positionStyle;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function styleButton()
    {
        $color = 'color: ' . $this->getTextButtonColor() . ';';
        $background = 'background-color: ' . $this->getBackgroundButtonColor() . ';';
        $position = $this->getPositionButton();
        $displayButton = $this->setDisplayButton();

        return $color . $background . $position . $displayButton. 'position: fixed; z-index: 9;';
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return false|string[]
     * @throws \Exception
     */
    public function getVisibleStore()
    {
        $visibleStore = $this->getPopup()->getVisibleStores();
        $storeIds = str_replace(',', ' ', $visibleStore);
        return explode(' ', $storeIds);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkStoreButton()
    {
        $storeId = $this->getStoreId();
        $visibleStore = $this->getVisibleStore();
        return in_array("0", $visibleStore) || in_array($storeId, $visibleStore);
    }
}
