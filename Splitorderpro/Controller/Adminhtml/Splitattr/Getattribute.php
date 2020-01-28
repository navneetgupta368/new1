<?php

namespace Sunarc\Splitorderpro\Controller\Adminhtml\Splitattr;

class Getattribute extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $isAjax = $this->getRequest()->getParam('isAjax');
        if (!($isAjax)) {
            return false;
        }
        $isEdit=0;
        $option=$this->getRequest()->getParam('option');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
        $attributeOption=[];
        $attributeOptionId=[];
        $attribute = $eavConfig->getAttribute("catalog_product", $option);
        $allOptions = $attribute->getSource()->getAllOptions(true, true);

        foreach ($allOptions as $instance) {
            if ($attribute->usesSource()) {
                $option_id=$instance['value'];
                $attributeOptionId[] = $option_id;
            }
            $myArray = $attribute->getSource()->getOptionText($instance['value']);
            ;
            $attributeOption[]=$myArray;
            $attrValue=array_combine($attributeOptionId, $attributeOption);
        }
        array_filter($attrValue);
        $attrOpt=array_values(array_filter($attributeOption));
        $attrId=array_values(array_filter($attributeOptionId));
        $request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        $currentPageXML = $request->getFullActionName();

        $request = $objectManager->get('\Magento\Framework\App\Request\Http');
        $action=$this->getRequest()->getActionName();
        $actionName= $request->getActionName();
        if ($actionName=='edit') {
            $isEdit=1;
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode(['option' => $attrOpt,'optionId'=>$attrId, 'edit'=>$isEdit,'status'=>1]));
    }
}
