<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $title ?></h3>
    </div>
    <div class="card-body">
        <p>Create form for Leave - To be implemented</p>
        <a href="<?= base_url('hr/leave') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>
<?= $this->endSection() ?>
