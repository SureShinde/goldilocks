<?php
/**
 * <!--
 * *
 *  * Copyright © 2020 Magenest. All rights reserved.
 *  * See COPYING.txt for license details.
 *
 * -->
 */

namespace Magenest\OptimizeImage\Plugin\File;


use Magenest\OptimizeImage\Helper\Data;

class OptimizeImageAfterSave
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * OptimizeImageAfterSave constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ){
        $this->data = $data;
    }

    public function afterSave(\Magento\Framework\File\Uploader $subject, $result)
    {
        if(
            $this->data->isEnable() &&
            !empty($result) && isset($result['type']) && isset($result['path']) && isset($result['file']) &&
            strpos($result['type'], 'image') !== false
        ){
            $filePath = rtrim($result['path'], '/') . '/' . $result['file'];
            $this->data->optimizeFile($filePath);
            $result['size'] = filesize($filePath);
        }

        return $result;
    }
}
