<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Supplier Statement - <?= esc($supplier['name']) ?></h3>
        <div class="card-tools">
            <a href="<?= base_url('master/supplier') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5>Supplier Information</h5>
                <table class="table table-sm">
                    <tr><th width="150">Code:</th><td><?= esc($supplier['code']) ?></td></tr>
                    <tr><th>Name:</th><td><?= esc($supplier['name']) ?></td></tr>
                    <tr><th>Email:</th><td><?= esc($supplier['email'] ?? '-') ?></td></tr>
                    <tr><th>Phone:</th><td><?= esc($supplier['phone'] ?? '-') ?></td></tr>
                    <tr><th>Payment Term:</th><td><?= $supplier['payment_term'] ?> days</td></tr>
                </table>
            </div>
        </div>
        
        <h5>Transaction History</h5>
        <p class="text-muted"><em>Transaction history will be available once Bill module is implemented</em></p>
    </div>
</div>
<?= $this->endSection() ?>
