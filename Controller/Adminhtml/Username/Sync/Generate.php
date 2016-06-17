<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Controller\Adminhtml\Username\Sync;

use Diglin\Username\Controller\Adminhtml\Username\Sync;
use Diglin\Username\Model\Generate\Flag;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\LoggerInterface;

/**
 * Class Generate
 * @package Diglin\Username\Controller\Adminhtml\Username\Sync
 */
class Generate extends Sync
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Config $eavConfig,
        ResourceConnection $resource
    ) {

        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection('core_read');

        parent::__construct($context);
    }

    public function execute()
    {
        session_write_close();

        $flag = $this->_getSyncFlag();
        $flag
            ->setState(Flag::STATE_RUNNING)
            ->save();

        $flag->setFlagData(array());

        try {
            $usernameAttribute = $this->eavConfig->getAttribute('customer', 'username');

            /**
             * Get customer entity_ids having a username
             */
            $select = $this->connection
                ->select()
                ->from($this->resource->getTableName('customer_entity_varchar'), 'entity_id')
                ->where('attribute_id = ?', $usernameAttribute->getId())
                ->where('value IS NOT NULL');

            $ids = $this->connection->fetchCol($select);

            /**
             * Get additional data from customers who doesn't have a username
             */
            $select = $this->connection
                ->select()
                ->from(array('c' => $this->resource->getTableName('customer_entity')), array('email', 'entity_id', 'entity_type_id'))
                ->group('c.entity_id');

            if (!empty($ids)) {
                $select
                    ->joinLeft(array('cev' => $this->resource->getTableName('customer_entity_varchar')), 'c.entity_id = cev.entity_id')
                    ->where('cev.entity_id NOT IN (' . implode(',', $ids) . ')');
            }

            // @todo - add support for Customer Website Share option (check that the username doesn't already exist in other websites)
            // @todo - add support for username depending on the username type supported in the configuration (only letters, digits, etc)

            // Create username for old customers to prevent problem when creating an order as a guest
            $customers = $this->connection->fetchAll($select);
            $totalItemsDone = 0;

            $flagData['total_items'] = count($customers);
            $flag->setFlagData($flagData)
                ->save();

            foreach ($customers as $customer) {
                $customer['attribute_id'] = $usernameAttribute->getId();
                $email = $customer['email'];
                $pos = strpos($email, '@');
                $customer['value'] = substr($email, 0, $pos) . substr(uniqid(), 0, 5) . $customer['entity_id'];

                unset($customer['email']);
                unset($customer['value_id']);

                // I know - direct sql query here is not good but there is no DBA for replace query
                $this->connection->query('REPLACE INTO '
                    . $this->connection->getTableName('customer_entity_varchar')
                    . ' SET entity_id = :entity_id, entity_type_id = :entity_type_id, attribute_id = :attribute_id, value = :value',
                    $customer);

                $flagData['total_items_done'] = $totalItemsDone;
                $flag->setFlagData($flagData)
                    ->save();
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $flag->setHasErrors(true);
        }
        $flag->setState(Flag::STATE_FINISHED)->save();
    }

}