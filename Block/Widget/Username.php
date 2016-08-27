<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Block\Widget;

use Diglin\Username\Helper\Customer;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Widget\AbstractWidget;

/**
 * Class Username
 *
 * @method CustomerInterface getObject()
 * @method Username setObject(CustomerInterface $customer)
 *
 * @package Diglin\Username\Block\Widget
 */
class Username extends AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var Customer
     */
    private $usernameHelper;


    /**
     * Create an instance of the Username widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Customer $usernameHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        Customer $usernameHelper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
        $this->usernameHelper = $usernameHelper;
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/username.phtml');
    }

    /**
     * Check if username attribute enabled in system
     * @return bool
     */
    public function isEnabled()
    {
        return $this->usernameHelper->isEnabled() && ($this->_getAttribute('username') ? (bool)$this->_getAttribute('username')->isVisible() : false);
    }

    /**
     * Check if username attribute marked as required
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('username') ? (bool)$this->_getAttribute('username')->isRequired() : false;
    }

    /**
     * Get current customer from session
     *
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->_customerSession->getCustomerId());
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->usernameHelper->isEditableOnFrontend() || $this->getData('is_editable');
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        if (!$this->getData('username')) {
            if ($this->getObject()->getCustomAttribute('username') instanceof \Magento\Framework\Api\AttributeInterface) {
                $this->setData('username', $this->getObject()->getCustomAttribute('username')->getValue());
            }
        }

        return $this->getData('username');
    }
}
