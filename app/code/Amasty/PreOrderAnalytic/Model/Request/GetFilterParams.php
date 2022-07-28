<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Model\Request;

use Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange\GetDefaultFrom;
use Amasty\PreOrderAnalytic\Model\AnalyticForm\DateRange\GetDefaultTo;
use Magento\Framework\App\RequestInterface;

class GetFilterParams
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var GetDefaultFrom
     */
    private $getDefaultFrom;

    /**
     * @var GetDefaultTo
     */
    private $getDefaultTo;

    public function __construct(RequestInterface $request, GetDefaultFrom $getDefaultFrom, GetDefaultTo $getDefaultTo)
    {
        $this->request = $request;
        $this->getDefaultFrom = $getDefaultFrom;
        $this->getDefaultTo = $getDefaultTo;
    }

    public function execute(): array
    {
        $fromDate = $this->request->getParam('from');
        $toDate = $this->request->getParam('to');

        $params = [];
        if (!$fromDate && !$toDate) {
            $params['created_at']['from'] = $this->getDefaultFrom->execute();
            $params['created_at']['to'] = $this->getDefaultTo->execute();
        } else {
            if ($fromDate) {
                $params['created_at']['from'] = (string) $fromDate;
            }
            if ($toDate) {
                $params['created_at']['to'] = (string) $toDate;
            }
        }

        return $params;
    }
}
