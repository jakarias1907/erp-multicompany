<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateJournalEntryLinesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'journal_entry_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'debit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'credit' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'description' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('journal_entry_id', 'journal_entries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('account_id', 'chart_of_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('journal_entry_lines');
    }
    public function down() { $this->forge->dropTable('journal_entry_lines'); }
}
