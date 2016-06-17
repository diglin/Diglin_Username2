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

namespace Diglin\Username\Model\Config;

/**
 * Customer sharing config model
 *
 * Class Share
 * @package Diglin\Username\Model\Config
 */
class Share extends \Magento\Customer\Model\Config\Share
{

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $value = $this->getValue();
        if ($value == self::SHARE_GLOBAL) {
            if ($this->_customerResource->findUsernameDuplicates()) { // @todo - see how to implement the method findUsernameDuplicates with overwrite or similar (plugin)
                //@codingStandardsIgnoreStart
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot share customer accounts globally because some customer accounts with the same username exist on multiple websites and cannot be merged.')
                );
            }
        }
        return $this;
    }
}
