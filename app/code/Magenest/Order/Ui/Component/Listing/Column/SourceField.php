<?php

namespace Magenest\Order\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SourceField extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $sourceField = json_decode($item['source_field'], true);
                if (!empty($sourceField)) {
                    $html = '';
                    foreach ($sourceField as $key => $value) {
                        $html .= '<p><strong>' . $key . '</strong>:&nbsp;' . $value . '</p>';
                    }
                    $item['source_field'] = $html;
                }
            }
        }
        return $dataSource;
    }
}
