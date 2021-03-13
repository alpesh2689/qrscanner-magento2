<?php
namespace Coreway\Qrscanner\Controller\Login;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class Index extends \Magento\Framework\App\Action\Action
{
	
	protected $_customer;
	protected $_customerSession;
	protected $request;
	protected $_objectManager;
	protected $scopeConfig;
	protected $storeManager;
	protected $helperData;

	public function _construct(
	    \Magento\Customer\Model\Customer $customer,
	    \Magento\Customer\Model\Session $customerSession,
	    \Magento\Framework\App\Request\Http $request,
	    \Magento\Framework\ObjectManagerInterface $objectmanager,
	    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	    \Magento\Store\Model\StoreManagerInterface $storeManager
	) {
	    $this->_customer = $customer;
	    $this->_customerSession = $customerSession;
	    $this->request = $request;
	    $this->_objectManager = $objectmanager;
	    $this->scopeConfig = $scopeConfig;
	    $this->storeManager = $storeManager;
	}
	
	public function execute()
    {
    	if ($this->_request->getParam("customerid")) {
			$customerID = $this->_request->getParam("customerid");
    		$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerID);
            $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
            $customerSession->setCustomerAsLoggedIn($customer);
        }
    	$this->_redirect('');
    	return;
    }
}
