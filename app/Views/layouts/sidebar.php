<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url('dashboard') ?>" class="brand-link">
        <i class="fas fa-briefcase brand-image ml-2"></i>
        <span class="brand-text font-weight-light">ERP System</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Master Data -->
                <li class="nav-item <?= strpos(uri_string(), 'master/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'master/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Master Data
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('master/company') ?>" class="nav-link <?= uri_string() == 'master/company' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Companies</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('master/user') ?>" class="nav-link <?= uri_string() == 'master/user' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('master/role') ?>" class="nav-link <?= uri_string() == 'master/role' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Roles & Permissions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('master/product') ?>" class="nav-link <?= uri_string() == 'master/product' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('master/customer') ?>" class="nav-link <?= uri_string() == 'master/customer' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('master/supplier') ?>" class="nav-link <?= uri_string() == 'master/supplier' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Suppliers</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Finance & Accounting -->
                <li class="nav-item <?= strpos(uri_string(), 'finance/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'finance/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>
                            Finance
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('finance/account') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chart of Accounts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('finance/journal') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Journal Entry</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('finance/invoice') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Invoices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('finance/bill') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bills</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('finance/ledger') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>General Ledger</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Inventory -->
                <li class="nav-item <?= strpos(uri_string(), 'inventory/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'inventory/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Inventory
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('inventory/warehouse') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Warehouses</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('inventory/stock') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('inventory/stock/movements') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock Movements</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('inventory/stock/adjustment') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock Adjustment</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('inventory/stock/transfer') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock Transfer</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Sales -->
                <li class="nav-item <?= strpos(uri_string(), 'sales/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'sales/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>
                            Sales
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('sales/quotation') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Quotations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('sales/sales-order') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sales Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('sales/delivery') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Delivery Orders</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Purchase -->
                <li class="nav-item <?= strpos(uri_string(), 'purchase/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'purchase/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-shopping-bag"></i>
                        <p>
                            Purchase
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('purchase/pr') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Purchase Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('purchase/po') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Purchase Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('purchase/gr') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Goods Receipt</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- HR & Payroll -->
                <li class="nav-item <?= strpos(uri_string(), 'hr/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'hr/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            HR & Payroll
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('hr/employee') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employees</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('hr/attendance') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Attendance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('hr/leave') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('hr/payroll') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Payroll</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Reports -->
                <li class="nav-item <?= strpos(uri_string(), 'reports/') === 0 ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'reports/') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            Reports
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('reports/sales') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sales Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('reports/purchase') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Purchase Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('reports/inventory') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Inventory Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('reports') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Reports</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>
