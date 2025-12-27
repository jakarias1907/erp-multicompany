<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sku' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'unit_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'price' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'cost' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'stock_alert_level' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'description' => ['type' => 'TEXT', 'null' => true],
            'image' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'sku']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('products');
    }

    public function down() { $this->forge->dropTable('products'); }
}
