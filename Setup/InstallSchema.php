<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Training\CustomerComment\Setup
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

namespace Diglin\Username\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSet;
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * InstallSchema constructor.
     */
    public function __construct(
        AttributeSetFactory $attributeSet,
        Config $eavConfig,
        EavSetup $eavSetup
    )
    {
        $this->attributeSet = $attributeSet;
        $this->eavConfig = $eavConfig;
        $this->eavSetup = $eavSetup;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $entityType = $this->eavConfig->getEntityType(Customer::ENTITY);
        $attributeSet = $this->attributeSet->create();
        $groupId = $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId());

        $this->eavSetup->addAttribute(Customer::ENTITY, 'username',
            [
                'label'                 => 'Username',
                'input'                 => 'text',
                'required'              => 0,
                'user_defined'          => 1,
                'unique'                => 0,
                'system'                => 0,
                'group'                 => $groupId,
                'is_used_in_grid'       => 1,
                'is_visible_in_grid'    => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1,
                'validate_rules'        => serialize([
                    'max_text_length' => 30,
                    'min_text_length' => 6
                ])
            ]
        );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('customer_grid_flat'),
                'username',
                [
                    'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default' => '',
                    'comment' => 'Customer Username'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('quote'), 'customer_username',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'   => '255',
                    'nullable' => true,
                    'comment'  => 'Customer Username',
                    'after'    => 'customer_taxvat'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'), 'customer_username',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'   => '255',
                    'nullable' => true,
                    'comment'  => 'Customer Username',
                    'after'    => 'customer_taxvat'
                ]
            );

        $setup->endSetup();
    }
}