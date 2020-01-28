<?php

namespace Sunarc\Splitorderpro\Model\Config\Source;

class Conditions implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        ['value' => '1', 'label' => __('Default')],
        ['value' => '2', 'label' => __('Split if attribute exist')],
        ['value' => '3', 'label' => __('Split according to attribute')],
        ['value' => '4', 'label' => __('Split according to warehouse')]
        ];
    }
}
