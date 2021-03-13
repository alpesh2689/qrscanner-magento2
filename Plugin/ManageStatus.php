<?php
 
namespace Coreway\Qrscanner\Plugin;
 
class ManageStatus
{

	/**
     * @var \Coreway\Qrscanner\Helper\Data
     */
    protected $helperData;

    public function __construct(
        \Coreway\Qrscanner\Helper\Data $helperData
    ) {
		$this->helperData = $helperData;
    }

    public function afterRenderLink(\Magento\Framework\View\Element\Html\Links $subject, $result, \Magento\Framework\View\Element\AbstractBlock $link)
    {
    	if ($this->helperData->isModuleUsable() != 1) {
	        if ($link->getNameInLayout() == 'customer-account-navigation-qr') {
	            $result = "";
	        }
    	}
        return $result;
    }

}