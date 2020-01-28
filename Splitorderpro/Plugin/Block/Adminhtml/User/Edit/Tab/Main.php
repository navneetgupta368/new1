<?php

namespace Sunarc\Splitorderpro\Plugin\Block\Adminhtml\User\Edit\Tab;

class Main
{
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    ) {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $_objectManager->get('Magento\Framework\Registry');
        $model = $registry->registry('permissions_user');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $form = $subject->getForm();
        if (is_object($form)) {
            if ($objectManager->create('Sunarc\Splitorderpro\Helper\Data')->canRestrictBySplitAttribute($model)) {
                $fieldset = $form->addFieldset('splitattribute_restrictions_fieldset', ['legend' => __('Restrict user')]);
                // Gather our existing categories
                $currentCategories = $this->_getExistingCategories($model);
                $fieldset->addField(
                    'splitattribute_restrictions',
                    'multiselect',
                    [
                        'name' => 'splitattribute_restrictions[]',
                        'label' => __('Restrict by Fulfilment Location'),
                        'id' => 'splitattribute_restrictions',
                        'title' => __('Restrict user'),
                        'value' => $currentCategories,//for selected value
                        'values' => $objectManager->create('Sunarc\Splitorderpro\Helper\Data')->getProductAttributeValuesForForm()//for all options
                    ]
                );
                 $subject->setForm($form);
            }
        }
        return $proceed();
    }
    /**
     * @param $model
     * @return array
     */
    private function _getExistingCategories($model)
    {
        // Get our collection
        $user = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Magento\User\Model\User'
        )->getCollection()
            ->addFieldToSelect('splitattribute_restrictions')
            ->addFieldToFilter('user_id', $model->getUserId());
        // Setup our placeholder for the array of categories needed to set back on the value of the multiselect
        $itemList = [];
        foreach ($user as $_item) {
            $itemList = $_item['splitattribute_restrictions'];
        }
        return $itemList;
    }
}
