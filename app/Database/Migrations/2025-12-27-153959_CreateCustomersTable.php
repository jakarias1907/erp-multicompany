<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'type' => ['type' => 'ENUM', 'constraint' => ['retail', 'wholesale'], 'default' => 'retail'],
            'contact_person' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'phone' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'credit_limit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'payment_term' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'code']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customers');
    }

    public function down() { $this->forge->dropTable('customers'); }
}
