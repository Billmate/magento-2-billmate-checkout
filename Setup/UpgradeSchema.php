<?php
namespace Billmate\BillmateCheckout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $table = $installer->getTable('sales_order');
        if (!$connection->tableColumnExists($table, 'billmate_invoice_id')){
            $columns = [
                'billmate_invoice_id' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Billmate Invoice ID',
                ],
            ];
            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
        }
        $installer->endSetup();
    }
}
?>