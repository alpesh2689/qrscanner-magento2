<?php

namespace Coreway\Qrscanner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $scopeConfig;

	/**
	* QR Status Config Path
	*/
	const XML_QR_STATUS = 'msp_securitysuite_twofactorauth/qrscanner/enabled';

	/**
	* QR Access Key Config Path
	*/
	const XML_QR_ACCESS_KEY = 'msp_securitysuite_twofactorauth/qrscanner/access_key';

	/**
	* QR Public Key Config Path
	*/
	const XML_QR_PUBLIC_KEY = 'msp_securitysuite_twofactorauth/qrscanner/public_key';

	/**
	* QR Private Key Config Path
	*/
	const XML_QR_PRIVATE_KEY = 'msp_securitysuite_twofactorauth/qrscanner/private_key';

	public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
		$this->scopeConfig = $scopeConfig;
	}

	/**
	* QR Status function returning config value
	**/

	public function getQRStatus() {

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		return $this->scopeConfig->getValue(self::XML_QR_STATUS, $storeScope);
		
	}

	/**
	* QR Access Key function returning config value
	**/

	public function getQRAccessKey() {

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		return $this->scopeConfig->getValue(self::XML_QR_ACCESS_KEY, $storeScope);

	}

	/**
	* QR Public Key function returning config value
	**/

	public function getQRPublicKey() {

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		return $this->scopeConfig->getValue(self::XML_QR_PUBLIC_KEY, $storeScope);

	}

	/**
	* QR Private Key function returning config value
	**/

	public function getQRPrivateKey() {

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

		return $this->scopeConfig->getValue(self::XML_QR_PRIVATE_KEY, $storeScope);

	}

	/**
	* QR Module Usable Function
	**/

	public function isModuleUsable() {

		$moduleStatus = $this->getQRStatus();

		if ($moduleStatus == 1) {

			$accessKey = $this->getQRAccessKey();
			$publicKey = $this->getQRPublicKey();
			$privateKey = $this->getQRPrivateKey();

			if ($accessKey != "" && $publicKey != "" && $privateKey != "") {

				return true;

			} else {
				
				return false;

			}

		} else {

			return false;

		}

	}

}