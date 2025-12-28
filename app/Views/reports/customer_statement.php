<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> <?= $title ?></h3>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('reports/customer-statement') ?>" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Customer *</label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= $customer['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-t') ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <div class="mb-3">
            <a href="javascript:void(0)" id="exportPdf" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="javascript:void(0)" id="exportExcel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });
    const params = new URLSearchParams(window.location.search);
    $('#exportPdf').attr('href', '<?= base_url('reports/customer-statement-pdf') ?>?' + params.toString());
    $('#exportExcel').attr('href', '<?= base_url('reports/customer-statement-excel') ?>?' + params.toString());
});
</script>
<?= $this->endSection() ?>
