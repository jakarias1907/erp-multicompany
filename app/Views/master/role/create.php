<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header"><h3 class="card-title">Create Role</h3></div>
    <div class="card-body"><?= $this->include('master/role/_form') ?></div>
</div>
<?= $this->endSection() ?>
