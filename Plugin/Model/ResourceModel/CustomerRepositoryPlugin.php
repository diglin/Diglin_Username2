<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Plugin\Model\ResourceModel;

use Diglin\Username\Helper\Customer as CustomerHelper;
use Diglin\Username\Helper\Customer;
use Magento\Checkout\Helper\Data;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Customer\Model\Metadata\CustomerMetadata;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CustomerRepositoryPlugin
 * @package Diglin\Username\Plugin\Model\ResourceModel
 */
class CustomerRepositoryPlugin
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var CustomerMetadata
     */
    private $customerMetadata;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var State
     */
    private $state;
    /**
     * @var CustomerHelper
     */
    private $customerHelper;
    /**
     * @var Data
     */
    private $checkoutHelper;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * CustomerRepositoryPlugin constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerMetadata $customerMetadata
     * @param Config $config
     * @param State $state
     * @param CustomerHelper $customerHelper
     * @param Data $checkoutHelper
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerMetadata $customerMetadata,
        Config $config,
        State $state,
        CustomerHelper $customerHelper,
        Data $checkoutHelper,
        ObjectManagerInterface $objectManager
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerMetadata = $customerMetadata;
        $this->config = $config;
        $this->state = $state;
        $this->customerHelper = $customerHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $subject
     * @param $username
     * @param null $websiteId
     * @return array
     */
    public function beforeGet(\Magento\Customer\Model\ResourceModel\CustomerRepository $subject, $username, $websiteId = null)
    {
        if (strpos($username, '@') === false) {
            $customerFound = $this->customerHelper->loadByUsername($username, $websiteId);
            if ($customerFound) {
                $username = $customerFound->getEmail();
            }
        }

        return [$username, $websiteId];
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null $passwordHash
     * @return array
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave(\Magento\Customer\Model\ResourceModel\CustomerRepository $subject, \Magento\Customer\Api\Data\CustomerInterface $customer, $passwordHash = null)
    {
        $exception = new InputException();
        $customerDataOriginal = $usernameOriginal = null;

        $usernameAttribute = $customer->getCustomAttribute('username');

        if (is_null($usernameAttribute)) {
            return [$customer, $passwordHash];
        }

        $username = $usernameAttribute->getValue();

        if ($customer->getId()) {
            $customerDataOriginal = $subject->getById($customer->getId());
            $originalUsernameAttribute = $customerDataOriginal->getCustomAttribute('username');
            if ($originalUsernameAttribute) {
                $usernameOriginal = $originalUsernameAttribute->getValue();
            }
        }

        if (!is_null($usernameOriginal) && $usernameOriginal != $username) {

            // @todo return true even in none checkout process which is not expected
//            $guestMethod = $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutHelper->getQuote());
            $guestMethod = false;

            /**
             * Validate Username
             * - No duplicate must exist
             * - Must respect format of the username
             * - check if required or not
             * - Do not allow frontend edition if configuration is enabled
             * - Do not check for guest
             */

            if ($this->state->getAreaCode() == 'frontend' && !$this->customerHelper->isEditableOnFrontend()) {
                $exception->addError(__('Username cannot be edited on frontend.'));
                $customer->setCustomAttribute('username', $customerDataOriginal->getCustomAttribute('username')->getValue());
            } else if ($guestMethod != \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
                $this->validate($customer, $exception);
            }
        } else if (!$customer->getId()) {
            $this->validate($customer, $exception);
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }

        return [$customer, $passwordHash];
    }

    /**
     * Get attribute metadata.
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     */
    private function getAttributeMetadata($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param InputException $exception
     * @return $this
     * @throws InputException
     */
    private function validate(\Magento\Customer\Api\Data\CustomerInterface $customer, InputException $exception)
    {
        $username = $customer->getCustomAttribute('username')->getValue();
        $websiteId = ($customer->getWebsiteId()) ? $customer->getWebsiteId() : null;

        $usernameAttribute = $this->getAttributeMetadata('username');
        if ($usernameAttribute !== null && $usernameAttribute->isRequired() && '' == trim($username)) {
            throw InputException::requiredField('username');
        }

        // Other rules are validated by the parent class because they are basic rules provided by Magento Core
        $inputValidation = $this->config->getValue(CustomerHelper::CFG_INPUT_VALIDATION);
        $useInputValidation = ($inputValidation == 'default' || $inputValidation == 'custom') ? true : false;

        if ($useInputValidation) {
            $validate = '/^*$/';
            switch ($inputValidation) {
                case 'default':
                    $validate = '/^[\w-]*$/';
                    break;
                case 'custom':
                    $validate = $this->config->getValue(CustomerHelper::CFG_INPUT_VALIDATION_CUSTOM);
                    break;
            }

//            if (!$this->config->isSetFlag('username/general/case_sensitive')) {
//                $validate .= 'i';
//            }

            $validate = new \Zend_Validate_Regex($validate);

            if (!$validate->isValid($username)) {
                if ($inputValidation == 'custom') {
                    $message = new \Magento\Framework\Phrase($this->config->getValue(Customer::CFG_INPUT_VALIDATION_CUSTOM_MESSAGE));
                } else {
                    $message = __('Username is invalid! Only letters, digits and \'_-\' values are accepted.');
                }
                $exception->addError($message);
            }
        }

        $customerFound = $this->customerHelper->customerUsernameExists($username, $websiteId);
        if ($customerFound && $customerFound->getId() != $customer->getId()) {
            $message = __('Username already exists');
            $exception->addError($message);
        }

        return $this;
    }
}
