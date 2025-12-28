<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-boxes"></i> <?= $title ?></h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="<?= base_url('reports/inventory-report-pdf') ?>" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="<?= base_url('reports/inventory-report-excel') ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Current stock levels as of today. Use export buttons to download the report.
        </div>
    </div>
</div>
<?= $this->endSection() ?>
