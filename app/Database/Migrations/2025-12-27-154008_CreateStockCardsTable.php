<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateStockCardsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "company_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "warehouse_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "product_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "quantity" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "last_updated" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addUniqueKey(["company_id", "warehouse_id", "product_id"]);
        $this->forge->addForeignKey("company_id", "companies", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("warehouse_id", "warehouses", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("product_id", "products", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("stock_cards");
    }
    public function down() { $this->forge->dropTable("stock_cards"); }
}