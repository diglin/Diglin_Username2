<?php

namespace Diglin\Username\Setup;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Diglin\Username\Setup
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * Field Data Converter Factory
     *
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * UpgradeData constructor
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), "2.0.1", '<')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Convert data for the customer_eav_attribute.username from serialized to JSON format
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    protected function convertSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $tableName = 'customer_eav_attribute';
        $identifierFieldName = 'attribute_id';
        $serializedFieldName = 'validate_rules';

        /** @var \Magento\Framework\DB\FieldDataConverter $fieldDataConverter */
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        try {

            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable($tableName),
                $identifierFieldName,
                $serializedFieldName
            );
        } catch (\Magento\Framework\DB\FieldDataConversionException $e) {
            //TODO add exception handler
        }
    }
}