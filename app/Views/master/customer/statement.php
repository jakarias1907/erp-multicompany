<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Customer Statement - <?= esc($customer['name']) ?></h3>
        <div class="card-tools">
            <a href="<?= base_url('master/customer') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5>Customer Information</h5>
                <table class="table table-sm">
                    <tr><th width="150">Code:</th><td><?= esc($customer['code']) ?></td></tr>
                    <tr><th>Name:</th><td><?= esc($customer['name']) ?></td></tr>
                    <tr><th>Email:</th><td><?= esc($customer['email'] ?? '-') ?></td></tr>
                    <tr><th>Phone:</th><td><?= esc($customer['phone'] ?? '-') ?></td></tr>
                    <tr><th>Credit Limit:</th><td><?= formatCurrency($customer['credit_limit']) ?></td></tr>
                </table>
            </div>
        </div>
        
        <h5>Transaction History</h5>
        <p class="text-muted"><em>Transaction history will be available once Invoice module is implemented</em></p>
    </div>
</div>
<?= $this->endSection() ?>
