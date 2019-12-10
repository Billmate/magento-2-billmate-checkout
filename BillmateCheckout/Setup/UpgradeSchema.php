<?php
namespace Billmate\BillmateCheckout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $version = $context->getVersion();
        $run = false;
        if ($version == ""){
            $run = true;
        }
        if (version_compare($context->getVersion(), '1.0.7') >= 0 || $run) {
            $quoteTable = 'quote';
            $orderTable = 'sales_order';
            $setup->getConnection()->addColumn(
                $setup->getTable($orderTable),
                'order_comment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    'default' => "",
                    'nullable' => true,
                    'comment' => 'Order Comment'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($quoteTable),
                'order_comment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    'default' => "",
                    'nullable' => true,
                    'comment' => 'Order Comment'
                ]
            );
        }
        $setup->endSetup();
    }
}
?>