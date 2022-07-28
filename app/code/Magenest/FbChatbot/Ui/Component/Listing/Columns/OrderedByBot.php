<?php
namespace Magenest\FbChatbot\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class OrderedByBot extends Column {

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['ordered_bot'] = $item['ordered_bot'] == 1 ? "Yes" : "No";
            }
        }
        return parent::prepareDataSource($dataSource);
    }
}
