<?php
/**
 * Diglin GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Diglin
 * @package     Diglin_Username
 * @copyright   Copyright (c) 2008-2015 Diglin GmbH - Switzerland (http://www.diglin.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Diglin\Username\Model\Event;

use Magento\Customer\Model\AttributeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Observer
 * @package Diglin\Username\Model
 */
class ChangeEavAttribute implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        ScopeConfigInterface $config,
        AttributeFactory $attributeFactory
    ) {
        $this->config = $config;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Change the attribute of username after the configuration
     * has been changed
     *
     * Event: admin_system_config_changed_section_username
     *
     * @param \Magento\Framework\Event\Observer $observer Observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $minLength = $this->config->getValue('username/general/min_length');
        $maxLength = $this->config->getValue('username/general/max_length');
        $inputValidation = $this->config->getValue('username/general/input_validation');

        if ($minLength > $maxLength) {
            throw new \Exception (
                __('Sorry but you cannot set a minimum length value %s bigger than the maximum length value %s. Please, change the values.',
                    $minLength,
                    $maxLength)
            );
        }

        /* @var $attributeUsernameModel \Magento\Customer\Model\Attribute */
        $attributeUsernameModel = $this->attributeFactory->create();
        $attributeUsernameModel->loadByCode('customer', 'username');

        if ($attributeUsernameModel->getId()) {
            $rules = $attributeUsernameModel->getValidateRules();
            $rules['max_text_length'] = $maxLength;
            $rules['min_text_length'] = $minLength;

            if ($inputValidation != 'default' && $inputValidation != 'custom') {
                $rules['input_validation'] = $inputValidation;
            } else {
                $rules['input_validation'] = ''; // validation is done at save level of the customer model
            }

            $usedInGrid = $this->config->getValue('username/general/grid');
            $attributeUsernameModel->setData('is_used_in_grid', $usedInGrid);

            $attributeUsernameModel->setValidateRules($rules);
            $attributeUsernameModel->save();
        }
    }
}