<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Helper;

use Magento\Framework\App\Config;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;

/**
 * Class Customer
 * @package Diglin\Username\Helper
 */
class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CFG_ENABLED                           = 'username/general/enabled';
    const CFG_FRONTEND                          = 'username/general/frontend';
    const CFG_INPUT_VALIDATION                  = 'username/general/input_validation';
    const CFG_CASE_SENSITIVE                    = 'username/general/case_sensitive';
    const CFG_INPUT_VALIDATION_CUSTOM           = 'username/general/input_validation_custom';
    const CFG_INPUT_VALIDATION_CUSTOM_MESSAGE   = 'username/general/input_validation_custom_message';
    const CFG_INPUT_MAXLENGTH                   = 'username/general/max_length';
    const CFG_INPUT_MINLENGTH                   = 'username/general/min_length';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var Config
     */
    private $config;

    /**
     * Customer constructor.
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config
    ){
        $this->customerRepository = $customerRepository;

        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
    }

    /**
     * @param $username
     * @param null $websiteId
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function customerUsernameExists($username, $websiteId = null)
    {
        return $this->loadByUsername($username, $websiteId);
    }

    /**
     * @param $username
     * @param null $websiteId
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function loadByUsername($username, $websiteId = null)
    {
        $this->searchCriteriaBuilder->addFilter('username', $username, 'like');

        if (!is_null($websiteId)) {
            $this->searchCriteriaBuilder->addFilter('website_id', $websiteId, 'eq');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $list = $this->customerRepository->getList($searchCriteria);

        if ($list->getTotalCount() > 0) {
            foreach ($list->getItems() as $item) {

                if ($this->isCaseSensitive() && $username != $item->getCustomAttribute('username')->getValue()) {
                    return false;
                }

                return $item;
            }
        }

        return false;
    }

    /**
     * Is Username editable on frontend
     *
     * @return bool
     */
    public function isEditableOnFrontend()
    {
        return $this->config->isSetFlag(self::CFG_FRONTEND);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isSetFlag(self::CFG_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->config->isSetFlag(self::CFG_CASE_SENSITIVE);
    }

    /**
     * @return mixed
     * @throws \Hoa\Compiler\Exception
     * @throws \Hoa\Compiler\Exception\UnexpectedToken
     * @throws \Hoa\Regex\Exception
     */
    public function generateUsername()
    {
        $inputValidation = $this->config->getValue(self::CFG_INPUT_VALIDATION);
        $customValidation = $this->config->getValue(self::CFG_INPUT_VALIDATION_CUSTOM);
        $maxLength = (int) $this->config->getValue(self::CFG_INPUT_MAXLENGTH);
        $minLength = (int) $this->config->getValue(self::CFG_INPUT_MINLENGTH);

        switch ($inputValidation) {
            case 'alpha': // letters
                $regex = '^[a-zA-Z]{%d,%d}$';
                break;
            case 'numeric': // digits
                $regex = '^[\d]{%d,%d}$';
                break;
            case 'alphanumeric': // letters & digits
                $regex = '^[\d\w]{%d,%d}$';
                break;
            case 'custom':

                // Remove potential delimiter for Hoa\Regex class
                $delimiter = substr($customValidation, 0, 1);
                $endDelimiter = -1;
                if ($delimiter != substr($customValidation, $endDelimiter)) {
                    $endDelimiter = -2;
                }

                if ($delimiter == substr($customValidation, $endDelimiter)) {
                    $regex = substr($customValidation, 1, $endDelimiter);
                } else {
                    $regex = $customValidation;
                }

                break;
            case 'default': // letters, digits and _- characters
            default:
                $regex = '^[\w]{%d,%d}$';
                break;
        }

        $regex = sprintf($regex, $minLength, $maxLength);

        // 1. Read the grammar.
        $grammar = new \Hoa\File\Read('hoa://Library/Regex/Grammar.pp');

        // 2. Load the compiler.
        $compiler = \Hoa\Compiler\Llk\Llk::load($grammar);

        // 3. Lex, parse and produce the AST.
        $ast = $compiler->parse($regex);

        $generator = new \Hoa\Regex\Visitor\Isotropic(new \Hoa\Math\Sampler\Random());
        $username = $generator->visit($ast);

        if (!$this->isCaseSensitive()) {
            $username = strtolower($username);
        }

        if ($this->customerUsernameExists($username)) {
            $username = $this->generateUsername();
        }

        return $username;
    }
}
