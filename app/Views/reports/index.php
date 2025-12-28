<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> <?= $title ?></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Sales Reports -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sales Report</span>
                                <span class="info-box-number">View sales summary</span>
                                <a href="<?= base_url('reports/sales-report') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Reports -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Purchase Report</span>
                                <span class="info-box-number">View purchase summary</span>
                                <a href="<?= base_url('reports/purchase-report') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Reports -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Inventory Report</span>
                                <span class="info-box-number">View stock levels</span>
                                <a href="<?= base_url('reports/inventory-report') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Statement -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Customer Statement</span>
                                <span class="info-box-number">View customer balances</span>
                                <a href="<?= base_url('reports/customer-statement') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Statement -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-industry"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Supplier Statement</span>
                                <span class="info-box-number">View supplier balances</span>
                                <a href="<?= base_url('reports/supplier-statement') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Trial Balance -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Trial Balance</span>
                                <span class="info-box-number">View trial balance</span>
                                <a href="<?= base_url('reports/trial-balance') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Sheet -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-purple">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Balance Sheet</span>
                                <span class="info-box-number">View balance sheet</span>
                                <a href="<?= base_url('reports/balance-sheet') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Income Statement -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-teal">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Income Statement</span>
                                <span class="info-box-number">View profit & loss</span>
                                <a href="<?= base_url('reports/income-statement') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Report -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-indigo">
                            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Attendance Report</span>
                                <span class="info-box-number">View employee attendance</span>
                                <a href="<?= base_url('reports/attendance-report') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Report -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-maroon">
                            <span class="info-box-icon"><i class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Payroll Report</span>
                                <span class="info-box-number">View payroll summary</span>
                                <a href="<?= base_url('reports/payroll-report') ?>" class="small-box-footer">
                                    View Report <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
