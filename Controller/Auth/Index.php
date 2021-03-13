<?php
namespace Coreway\Qrscanner\Controller\Auth;

use Magento\Framework\Controller\Result\JsonFactory;

require_once 'vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php';

use \phpseclib\Crypt\RSA;

class Index extends \Magento\Framework\App\Action\Action {

	protected $_customer;
	protected $_customerSession;
	protected $request;
	protected $_objectManager;
	protected $scopeConfig;
	protected $storeManager;
	protected $session;
	protected $resultJsonFactory;
	protected $helperData;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Session\SessionManagerInterface $session,
		JsonFactory $resultJsonFactory,
		\Coreway\Qrscanner\Helper\Data $helperData
	) {
		$this->_customerSession = $customerSession;
		$this->request = $request;
		$this->_objectManager = $objectmanager;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->session = $session;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helperData = $helperData;
		parent::__construct($context);
	}

	public function execute() {
		$resultJSON = $this->resultJsonFactory->create();

		$QRscannerStatus = $this->helperData->isModuleUsable();

		if ($QRscannerStatus == 1) {
			$domain = $_SERVER['HTTP_HOST'];

			$domain1 = $domain;

			$for_ = $this->_request->getParam("for_");
			//$for_ = "loginvalue";
			$site_code = $this->helperData->getQRAccessKey();

			$rsa = new RSA();

			$public_key = $this->helperData->getQRPublicKey();

			$private_key = $this->helperData->getQRPrivateKey();

			$rsa->loadKey(base64_decode($public_key));
			$domain = base64_encode($rsa->encrypt($domain));

			if ($for_ == "registervalue" || $for_ == "loginvalue") {
				if ($for_ == "registervalue") {
					$id = $this->_customerSession->getCustomer()->getId();
					$rsa->loadKey(base64_decode($public_key));
					$id = base64_encode($rsa->encrypt($id));
					$url = 'https://api.qrcodeauth.com/regkeygenerate';
					$data = ['id' => $id, 'key' => $site_code, 'domain' => $domain1];
				} else {
					$url = 'https://api.qrcodeauth.com/logkeygenerate';
					$data = ['site_code' => $site_code, 'domain' => $domain1];
				}
				$options = [
					'http' => [
						'header' => "Content-type: application/x-www-form-urlencoded\r\n",
						'method' => 'POST',
						'content' => http_build_query($data),
					],
				];
				$context = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				$data = json_decode($result);

				$kl = $data->success;

				$this->session->setLogKey($kl);
				return $resultJSON->setData(json_decode($result));
			} elseif ($for_ == "registervalidate" || $for_ == "loginvalidate") {
				$logkey = $this->session->getLogKey();

				$qr_code = $logkey;
				$rsa->loadKey(base64_decode($public_key));
				$qr_code = base64_encode($rsa->encrypt($qr_code));

				if ($for_ == "registervalidate") {
					$url = 'http://api.qrcodeauth.com/regcodeaction';
				} else {
					$url = 'http://api.qrcodeauth.com/logcodeaction';
				}
				$data = ['key' => $site_code, 'code' => $qr_code, 'domain' => $domain];
				$options = [
					'http' => [
						'header' => "Content-type: application/x-www-form-urlencoded\r\n",
						'method' => 'POST',
						'content' => http_build_query($data),
					],
				];
				$context = stream_context_create($options);
				echo "<pre>";
				print_r($context);
				exit;
				$result = file_get_contents($url, false, $context);
				$data = json_decode($result);
				//$_SESSION["validate"] = $data->result;
				$this->session->setValidate($data->result);
				return $resultJSON->setData(json_decode($result));
			} elseif ($for_ == "loginprocess") {
				//$validate = $_SESSION["validate"];
				$validate = $this->session->getValidate();
				if (isset($validate) && $validate != '') {
					$results = $validate;
					$url = 'http://api.qrcodeauth.com/fetchuser';
					$data = ['key' => $site_code, 'code' => $results, 'domain' => $domain];
					$options = [
						'http' => [
							'header' => "Content-type: application/x-www-form-urlencoded\r\n",
							'method' => 'POST',
							'content' => http_build_query($data),
						],
					];
					$context = stream_context_create($options);
					$result = file_get_contents($url, false, $context);
					$data = json_decode($result);
					//var_dump($data);exit;
					//if (count($data)) {
					if (!is_null($data->result)) {
						$rsa->loadKey(base64_decode($private_key));
						$data->result . "<br />";
						$decrypt = $rsa->decrypt(base64_decode($data->result));
						$customerID = $decrypt;
						$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerID);
						$customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
						$customerSession->setCustomerAsLoggedIn($customer);

						$this->_redirect('');
						return;
					}
				}
			} elseif ($for_ == "registerprocess") {
				$validate = $this->session->getValidate();
				if (isset($validate) && $validate == '1') {
					$this->messageManager->addSuccess(__('successfully linked your mobile as password'));
					$this->_redirect('customer/account/');
					return;
				} else if (isset($validate) && $validate == 'T') {
					$this->messageManager->addError(__('Timeout! please reload page to register'));
					$this->_redirect('customer/account/');
					return;
				} else {
					//error;
				}
			}
		}

	}
}
