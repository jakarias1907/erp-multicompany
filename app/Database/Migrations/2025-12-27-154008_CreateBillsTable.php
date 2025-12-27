<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateBillsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "company_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "bill_no" => ["type" => "VARCHAR", "constraint" => "50"],
            "supplier_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "date" => ["type" => "DATE"],
            "due_date" => ["type" => "DATE"],
            "subtotal" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "tax" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "total" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "paid_amount" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "status" => ["type" => "ENUM", "constraint" => ["draft", "approved", "paid", "cancelled"], "default" => "draft"],
            "created_by" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
            "deleted_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addKey(["company_id", "bill_no"]);
        $this->forge->addForeignKey("company_id", "companies", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("supplier_id", "suppliers", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("bills");
    }
    public function down() { $this->forge->dropTable("bills"); }
}