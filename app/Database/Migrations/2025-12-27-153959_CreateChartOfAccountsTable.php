<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateChartOfAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'account_type' => ['type' => 'ENUM', 'constraint' => ['assets', 'liabilities', 'equity', 'revenue', 'expenses']],
            'parent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'code']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chart_of_accounts');
    }
    public function down() { $this->forge->dropTable('chart_of_accounts'); }
}
