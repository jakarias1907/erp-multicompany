<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $title ?></h3>
        <div class="card-tools">
            <?php if (hasPermission('accounts', 'create')): ?>
            <a href="<?= base_url('finance/account/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Account
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div id="accountTree"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<script>
$(document).ready(function() {
    $('#accountTree').jstree({
        'core' : {
            'data' : {
                'url' : '<?= base_url('finance/account/tree-data') ?>',
                'dataType' : 'json'
            },
            'themes' : {
                'responsive': false
            }
        },
        'types' : {
            'asset' : {
                'icon' : 'fas fa-dollar-sign text-success'
            },
            'liability' : {
                'icon' : 'fas fa-credit-card text-danger'
            },
            'equity' : {
                'icon' : 'fas fa-balance-scale text-info'
            },
            'revenue' : {
                'icon' : 'fas fa-arrow-up text-primary'
            },
            'expense' : {
                'icon' : 'fas fa-arrow-down text-warning'
            }
        },
        'plugins' : ['types']
    });
});
</script>
<?= $this->endSection() ?>
