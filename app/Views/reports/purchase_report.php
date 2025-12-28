<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-truck"></i> <?= $title ?></h3>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="<?= base_url('reports/purchase-report') ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <a href="<?= base_url('reports/purchase-report-pdf') . '?' . http_build_query(['start_date' => $start_date, 'end_date' => $end_date]) ?>" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="<?= base_url('reports/purchase-report-excel') . '?' . http_build_query(['start_date' => $start_date, 'end_date' => $end_date]) ?>" 
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
