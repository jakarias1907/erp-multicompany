<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateStockTransfersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "company_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "transfer_no" => ["type" => "VARCHAR", "constraint" => "50"],
            "from_warehouse_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "to_warehouse_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "date" => ["type" => "DATE"],
            "status" => ["type" => "ENUM", "constraint" => ["pending", "approved", "completed", "cancelled"], "default" => "pending"],
            "approved_by" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "created_by" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addKey(["company_id", "transfer_no"]);
        $this->forge->addForeignKey("company_id", "companies", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("stock_transfers");
    }
    public function down() { $this->forge->dropTable("stock_transfers"); }
}