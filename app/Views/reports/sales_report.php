<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> <?= $title ?></h3>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="<?= base_url('reports/sales-report') ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Customer (Optional)</label>
                                <select name="customer_id" class="form-control select2">
                                    <option value="">All Customers</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" <?= ($customer_id == $customer['id']) ? 'selected' : '' ?>>
                                            <?= $customer['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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

                <!-- Export Buttons -->
                <div class="mb-3">
                    <a href="<?= base_url('reports/sales-report-pdf') . '?' . http_build_query(['start_date' => $start_date, 'end_date' => $end_date, 'customer_id' => $customer_id]) ?>" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="<?= base_url('reports/sales-report-excel') . '?' . http_build_query(['start_date' => $start_date, 'end_date' => $end_date, 'customer_id' => $customer_id]) ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>

                <!-- Report Preview -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Select date range and click "Filter" to view the report. Then use export buttons to download in PDF or Excel format.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});
</script>
<?= $this->endSection() ?>
