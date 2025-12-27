<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateUserActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'company_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'module' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => '45', 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'company_id']);
        $this->forge->createTable('user_activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('user_activity_logs');
    }
}
