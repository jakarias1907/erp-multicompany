<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateJournalEntriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'journal_no' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'date' => ['type' => 'DATE'],
            'reference' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'posted'], 'default' => 'draft'],
            'posted_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'posted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['company_id', 'journal_no']);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('journal_entries');
    }
    public function down() { $this->forge->dropTable('journal_entries'); }
}
