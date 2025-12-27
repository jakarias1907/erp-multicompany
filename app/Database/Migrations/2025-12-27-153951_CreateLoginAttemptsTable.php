<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateLoginAttemptsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => '45'],
            'status' => ['type' => 'ENUM', 'constraint' => ['success', 'failed'], 'default' => 'failed'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['username', 'created_at']);
        $this->forge->createTable('login_attempts');
    }

    public function down()
    {
        $this->forge->dropTable('login_attempts');
    }
}
