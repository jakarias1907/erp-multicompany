<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "company_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "invoice_no" => ["type" => "VARCHAR", "constraint" => "50"],
            "customer_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "date" => ["type" => "DATE"],
            "due_date" => ["type" => "DATE"],
            "subtotal" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "tax" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "discount" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "total" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "paid_amount" => ["type" => "DECIMAL", "constraint" => "15,2", "default" => 0],
            "status" => ["type" => "ENUM", "constraint" => ["draft", "sent", "paid", "cancelled"], "default" => "draft"],
            "created_by" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
            "deleted_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addKey(["company_id", "invoice_no"]);
        $this->forge->addForeignKey("company_id", "companies", "id", "CASCADE", "CASCADE");
        $this->forge->addForeignKey("customer_id", "customers", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("invoices");
    }
    public function down() { $this->forge->dropTable("invoices"); }
}