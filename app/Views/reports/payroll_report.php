<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-money-check-alt"></i> <?= $title ?></h3>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('reports/payroll-report') ?>" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Month</label>
                        <input type="month" name="month" class="form-control" value="<?= date('Y-m') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
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
            <a href="javascript:void(0)" id="exportPdf" class="btn btn-danger" target="_blank">
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
    const params = new URLSearchParams(window.location.search);
    $('#exportPdf').attr('href', '<?= base_url('reports/payroll-report-pdf') ?>?' + params.toString());
    $('#exportExcel').attr('href', '<?= base_url('reports/payroll-report-excel') ?>?' + params.toString());
});
</script>
<?= $this->endSection() ?>
