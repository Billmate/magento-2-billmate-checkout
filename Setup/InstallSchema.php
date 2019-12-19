<?php
namespace Billmate\BillmateCheckout\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class InstallSchema implements InstallSchemaInterface{
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
		try {
			$installer = $setup;

			$installer->startSetup();

			$table = $installer->getTable('sales_order');
			
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

			$installer->endSetup();
		}
		catch(Exception $e){}
	}
}

?>