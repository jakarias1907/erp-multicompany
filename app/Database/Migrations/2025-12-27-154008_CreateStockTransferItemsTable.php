<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateStockTransferItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "transfer_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "product_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "quantity" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "notes" => ["type" => "TEXT", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addForeignKey("transfer_id", "stock_transfers", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("product_id", "products", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("stock_transfer_items");
    }
    public function down() { $this->forge->dropTable("stock_transfer_items"); }
}