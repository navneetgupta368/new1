<?php
/**
 * Sunarc_Splitorderpro extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the SunArc Technologies License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://sunarctechnologies.com/end-user-agreement/
 *
 * @category  Sunarc
 * @package   Sunarc_Splitorderpro
 * @copyright Copyright (c) 2017
 * @license
 */
namespace Sunarc\Splitorderpro\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        if (!$installer->tableExists('sunarc_splitorderpro_splitattr')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('sunarc_splitorderpro_splitattr')
            )
            ->addColumn(
                'splitattr_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Splitattr ID'
            )
            ->addColumn(
                'split_order_attr',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Splitattr Select Attribute For Split Order'
            )
            ->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Splitattr Priority'
            )
            ->addColumn(
                'attr_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable => false'],
                'Splitattr Attribute Options'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Splitattr Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Splitattr Updated At'
            )
            ->setComment('Splitattr Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('sunarc_splitorderpro_splitattr'),
                $setup->getIdxName(
                    $installer->getTable('sunarc_splitorderpro_splitattr'),
                    ['priority'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['priority'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $adminRolesTableRoles = $installer->getTable('authorization_role');
        $adminUserTableRoles = $installer->getTable('admin_user');
        $connection->addColumn(
            $adminRolesTableRoles,
            'restrict_by_splitattribute',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Restrict By Splitattribute'
        );
        $connection->addColumn(
            $installer->getTable('admin_user'),
            'splitattribute_restrictions',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            [],
            'Splitattribure restrictions'
        );


        $connection->addColumn(
            $installer->getTable('sales_order_item'),
            'split_attribute_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            [],
            'splitorderpro attribute value'
        );
           $connection ->addColumn(
               $installer->getTable('sales_order_item'),
               'split_attribute_code',
               \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
               [],
               'splitorderpro attribute code'
           );
        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            'split_attribute_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            [],
            'split Attribute Information'
        );

        $installer->endSetup();
    }
}
