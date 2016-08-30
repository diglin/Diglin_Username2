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

use Magento\Config\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Observer
 * @package Diglin\Username\Model
 */
class AddToCollection implements ObserverInterface
{
    /**
     * Add on the fly the username attribute to the customer collection
     *
     * Event: eav_collection_abstract_load_before
     *
     * @param \Magento\Framework\Event\Observer $observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $collection AbstractCollection */
        $collection = $observer->getEvent()->getCollection();
        $entity = $collection->getEntity();
        if (!empty($entity) && $entity->getType() == 'customer') {
            $collection->addAttributeToSelect('username');
        }
    }
}