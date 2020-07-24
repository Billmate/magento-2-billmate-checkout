<?php
namespace Billmate\BillmateCheckout\Setup;

use Magento\Framework\DB\Ddl\Table;
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
        $quoteTable = 'quote';
        if (version_compare($context->getVersion(), '1.0.7') >= 0 || $run) {
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

        if ($setup->getConnection()->tableColumnExists($setup->getTable($quoteTable), 'first_callback_received') === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable($quoteTable),
                'first_callback_received',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'true if first Billmate callback has been received'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.6.12','<')) {
            $this->addInvoiceFeeRows($setup);
        }

        if (version_compare($context->getVersion(), '1.6.14','<')) {
            $this->addTestPaymentSource($setup);
        }

        $setup->endSetup();
    }

    protected function addInvoiceFeeRows($setup)
    {
        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $invoiceTable = 'sales_invoice';
        $creditmemoTable = 'sales_creditmemo';

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'bm_invoice_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'=>'10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Billmate invoice fee'

                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'bm_invoice_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'=>'10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Billmate invoice fee'

                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($invoiceTable),
                'bm_invoice_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'=>'10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Billmate invoice fee'

                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable($creditmemoTable),
                'bm_invoice_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length'=>'10,2',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' =>'Billmate invoice fee'

                ]
            );
    }

    protected function addTestPaymentSource($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'),
                'bm_test_mode',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '1',
                    'nullable' => false,
                    'default' => 0,
                    'comment' =>'Billmate Test Mode'

                ]
            );
    }
}
