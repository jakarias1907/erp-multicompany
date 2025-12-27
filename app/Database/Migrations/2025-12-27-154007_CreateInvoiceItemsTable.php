<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "invoice_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "product_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "description" => ["type" => "VARCHAR", "constraint" => "255", "null" => true],
            "quantity" => ["type" => "DECIMAL", "constraint" => "10,2", "default" => 0],
            "unit_price" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "tax" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "discount" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "total" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addForeignKey("invoice_id", "invoices", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("product_id", "products", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("invoice_items");
    }
    public function down() { $this->forge->dropTable("invoice_items"); }
}