<?php
namespace Magenest\Sidebar\Block;

use Magento\Framework\Serialize\Serializer\Json as JsonFramework;
use Magento\Framework\View\Element\Template;

class ToolbarFilters extends Template
{
    protected $_template = "Magenest_Sidebar::toolbar-filters.phtml";

    /** @var JsonFramework  */
    protected $jsonFramework;

    /**
     * @param JsonFramework $jsonFramework
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        JsonFramework $jsonFramework,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonFramework = $jsonFramework;
    }

    /**
     * @return bool|string
     */
    public function getJsonConfig()
    {
        return $this->jsonFramework->serialize([
            "options" => $this->getAttributes()
        ]);
    }

    /**
     * @return array[]
     */
    private function getAttributes()
    {
        return [
            ['attribute_code' => 'freeship', 'label' => __("Free ship")],
            ['attribute_code' => 'express', 'label' => __("Express")],
            ['attribute_code' => 'preorder', 'label' => __("Pre-order")],
        ];
    }
}
