<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateWarehousesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "company_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true],
            "name" => ["type" => "VARCHAR", "constraint" => "255"],
            "code" => ["type" => "VARCHAR", "constraint" => "50"],
            "address" => ["type" => "TEXT", "null" => true],
            "manager_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "status" => ["type" => "ENUM", "constraint" => ["active", "inactive"], "default" => "active"],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addKey("id", true);
        $this->forge->addKey(["company_id", "code"]);
        $this->forge->addForeignKey("company_id", "companies", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("warehouses");
    }
    public function down() { $this->forge->dropTable("warehouses"); }
}