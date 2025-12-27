<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Company Selector -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="companyDropdown" data-toggle="dropdown">
                <i class="fas fa-building"></i> 
                <span class="d-none d-sm-inline"><?= esc(session()->get('current_company_name') ?? 'Select Company') ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="companyDropdown">
                <!-- Company list will be populated dynamically -->
                <a class="dropdown-item" href="#">
                    <i class="fas fa-building mr-2"></i> Company 1
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-building mr-2"></i> Company 2
                </a>
            </div>
        </li>
        
        <!-- Notifications -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">3 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-box-open mr-2"></i> Low stock alert
                    <span class="float-right text-muted text-sm">5 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
        
        <!-- User Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                <i class="fas fa-user"></i> 
                <span class="d-none d-sm-inline"><?= esc(session()->get('full_name') ?? 'User') ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user-circle mr-2"></i> Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url('logout') ?>">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
