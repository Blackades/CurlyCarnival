<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />

    <script>
        var appUrl = '{$app_url}';
    </script>

    <!-- Icon Fonts -->
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
    
    <!-- Plugin CSS -->
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/select2.min.css" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/select2-bootstrap.min.css" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/plugins/pace.css" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/summernote/summernote.min.css" />
    
    <!-- Modern CSS System -->
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill-modern.css?v=2025.12.07.001" />
    
    <!-- Accessibility -->
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/accessibility/skip-links.css" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/accessibility/focus-indicators.css" />

    <script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="{$app_url}/ui/ui/scripts/aria-enhancements.js"></script>
    <style>

    </style>
    {if isset($xheader)}
        {$xheader}
    {/if}

</head>

<body class="hold-transition modern-skin-dark sidebar-mini {if $_kolaps}sidebar-collapse{/if} admin-layout">
    <!-- Skip Links for Keyboard Navigation -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <a href="#main-sidebar" class="skip-link" style="left: 180px;">Skip to navigation</a>
    
    <div class="wrapper admin-wrapper">
        <!-- Main Header -->
        <header class="main-header admin-header" role="banner">
            <!-- Logo Section -->
            <div class="header-logo">
                <a href="{Text::url('dashboard')}" class="logo brand-link" aria-label="Dashboard">
                    <span class="logo-mini brand-text-sm"><b>N</b>uX</span>
                    <span class="logo-lg brand-text">{$_c['CompanyName']}</span>
                </a>
            </div>
            
            <!-- Top Navigation Bar -->
            <nav class="navbar navbar-static-top header-nav" role="navigation">
                <!-- Mobile Menu Toggle -->
                <button type="button" class="sidebar-toggle mobile-menu-btn btn btn-link" 
                        data-toggle="push-menu" role="button" onclick="return setKolaps()"
                        aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="main-sidebar">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
                
                <!-- Header Menu -->
                <div class="navbar-custom-menu header-menu">
                    <ul class="nav navbar-nav header-nav-list">
                        <!-- Search Section -->
                        <li class="nav-item search-item">
                            <div class="search-wrapper">
                                <button id="openSearch" class="search-btn btn btn-link" 
                                        type="button" aria-label="Open user search dialog" aria-haspopup="dialog" aria-controls="searchOverlay">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                            </div>
                        </li>
                        
                        <!-- Theme Toggle -->
                        <li class="nav-item theme-toggle-item">
                            <a class="nav-link theme-toggle" href="#" aria-label="Toggle between light and dark theme" role="button">
                                <i class="toggle-icon theme-icon" id="toggleIcon" aria-hidden="true">🌜</i>
                            </a>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown user-menu">
                            <a href="#" class="nav-link dropdown-toggle user-menu-toggle" 
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{$app_url}/{$UPLOAD_PATH}{$_admin['photo']}.thumb.jpg"
                                     onerror="this.src='{$app_url}/{$UPLOAD_PATH}/admin.default.png'" 
                                     class="user-image user-avatar" alt="User Avatar">
                                <span class="user-name hidden-xs">{$_admin['fullname']}</span>
                                <i class="fa fa-caret-down user-menu-caret" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu user-dropdown">
                                <!-- User Header -->
                                <li class="user-header dropdown-header">
                                    <img src="{$app_url}/{$UPLOAD_PATH}{$_admin['photo']}.thumb.jpg"
                                         onerror="this.src='{$app_url}/{$UPLOAD_PATH}/admin.default.png'" 
                                         class="img-circle user-avatar-large" alt="User Avatar">
                                    <div class="user-info">
                                        <p class="user-fullname">{$_admin['fullname']}</p>
                                        <small class="user-role">{Lang::T($_admin['user_type'])}</small>
                                    </div>
                                </li>
                                
                                <!-- User Menu Items -->
                                <li class="user-body dropdown-body">
                                    <div class="row user-menu-grid">
                                        <div class="col-xs-7 user-menu-item">
                                            <a href="{Text::url('settings/change-password')}" class="user-menu-link">
                                                <i class="ion ion-settings" aria-hidden="true"></i>
                                                <span>{Lang::T('Change Password')}</span>
                                            </a>
                                        </div>
                                        <div class="col-xs-5 user-menu-item">
                                            <a href="{Text::url('settings/users-view/', $_admin['id'])}" class="user-menu-link">
                                                <i class="ion ion-person" aria-hidden="true"></i>
                                                <span>{Lang::T('My Account')}</span>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                
                                <!-- User Footer -->
                                <li class="user-footer dropdown-footer">
                                    <div class="user-footer-actions">
                                        <a href="{Text::url('logout')}" class="btn btn-default btn-flat logout-btn">
                                            <i class="ion ion-power" aria-hidden="true"></i>
                                            <span>{Lang::T('Logout')}</span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Search Overlay -->
            <div id="searchOverlay" class="search-overlay" role="dialog" aria-labelledby="searchTitle" aria-hidden="true" aria-modal="true">
                <div class="search-container">
                    <h2 id="searchTitle" class="sr-only">Search Users</h2>
                    <div class="search-input-wrapper">
                        <input type="text" id="searchTerm" class="searchTerm form-control"
                               placeholder="{Lang::T('Search Users')}" autocomplete="off"
                               aria-label="Search users by name or username" aria-describedby="searchResults">
                    </div>
                    <div id="searchResults" class="search-results" role="region" aria-live="polite" aria-label="Search results">
                        <!-- Search results will be displayed here -->
                    </div>
                    <div class="search-actions">
                        <button type="button" id="closeSearch" class="btn btn-secondary cancelButton" aria-label="Close search dialog">
                            {Lang::T('Cancel')}
                        </button>
                    </div>
                </div>
            </div>
        </header>
        <!-- Main Sidebar -->
        <aside id="main-sidebar" class="main-sidebar admin-sidebar" role="navigation" aria-label="Main navigation">
            <section class="sidebar sidebar-content">
                <!-- Sidebar Menu -->
                <nav class="sidebar-nav" role="navigation">
                    <ul class="sidebar-menu nav-menu" data-widget="tree" role="menubar">
                        <!-- Dashboard -->
                        <li class="nav-item {if $_system_menu eq 'dashboard' }active{/if}" role="none">
                            <a href="{Text::url('dashboard')}" class="nav-link" role="menuitem">
                                <i class="nav-icon ion ion-monitor" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Dashboard')}</span>
                            </a>
                        </li>
                    {$_MENU_AFTER_DASHBOARD}
                        <!-- Customers -->
                        <li class="nav-item {if $_system_menu eq 'customers' }active{/if}" role="none">
                            <a href="{Text::url('customers')}" class="nav-link" role="menuitem">
                                <i class="nav-icon fa fa-user" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Customer')}</span>
                            </a>
                        </li>
                    {$_MENU_AFTER_CUSTOMERS}
                    {if !in_array($_admin['user_type'],['Report'])}
                        <!-- Services Menu -->
                        <li class="nav-item has-submenu {if $_routes[0] eq 'plan' || $_routes[0] eq 'coupons'}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon fa fa-ticket" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Services')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'list' }active{/if}" role="none">
                                    <a href="{Text::url('plan/list')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Active Customers')}
                                    </a>
                                </li>
                                {if $_c['disable_voucher'] != 'yes'}
                                    <li class="nav-subitem {if $_routes[1] eq 'refill' }active{/if}" role="none">
                                        <a href="{Text::url('plan/refill')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Refill Customer')}
                                        </a>
                                    </li>
                                {/if}
                                {if $_c['disable_voucher'] != 'yes'}
                                    <li class="nav-subitem {if $_routes[1] eq 'voucher' }active{/if}" role="none">
                                        <a href="{Text::url('plan/voucher')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Vouchers')}
                                        </a>
                                    </li>
                                {/if}
                                {if $_c['enable_coupons'] == 'yes'}
                                    <li class="nav-subitem {if $_routes[0] eq 'coupons' }active{/if}" role="none">
                                        <a href="{Text::url('coupons')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Coupons')}
                                        </a>
                                    </li>
                                {/if}
                                <li class="nav-subitem {if $_routes[1] eq 'recharge' }active{/if}" role="none">
                                    <a href="{Text::url('plan/recharge')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Recharge Customer')}
                                    </a>
                                </li>
                                {if $_c['enable_balance'] == 'yes'}
                                    <li class="nav-subitem {if $_routes[1] eq 'deposit' }active{/if}" role="none">
                                        <a href="{Text::url('plan/deposit')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Refill Balance')}
                                        </a>
                                    </li>
                                {/if}
                                {$_MENU_SERVICES}
                            </ul>
                        </li>
                    {/if}
                    {$_MENU_AFTER_SERVICES}
                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                        <!-- Internet Plan Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'services'}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-cube" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Internet Plan')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'hotspot' }active{/if}" role="none">
                                    <a href="{Text::url('services/hotspot')}" class="nav-sublink" role="menuitem">
                                        Hotspot
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'pppoe' }active{/if}" role="none">
                                    <a href="{Text::url('services/pppoe')}" class="nav-sublink" role="menuitem">
                                        PPPOE
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'vpn' }active{/if}" role="none">
                                    <a href="{Text::url('services/vpn')}" class="nav-sublink" role="menuitem">
                                        VPN
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'list' }active{/if}" role="none">
                                    <a href="{Text::url('bandwidth/list')}" class="nav-sublink" role="menuitem">
                                        Bandwidth
                                    </a>
                                </li>
                                {if $_c['enable_balance'] == 'yes'}
                                    <li class="nav-subitem {if $_routes[1] eq 'balance' }active{/if}" role="none">
                                        <a href="{Text::url('services/balance')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Customer Balance')}
                                        </a>
                                    </li>
                                {/if}
                                {$_MENU_PLANS}
                            </ul>
                        </li>
                    {/if}
                    {$_MENU_AFTER_PLANS}
                        <!-- Maps Menu -->
                        <li class="nav-item has-submenu {if in_array($_routes[0], ['maps'])}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon fa fa-map-marker" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Maps')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'customer' }active{/if}" role="none">
                                    <a href="{Text::url('maps/customer')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Customer')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'routers' }active{/if}" role="none">
                                    <a href="{Text::url('maps/routers')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Routers')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'odp' }active{/if}" role="none">
                                    <a href="{Text::url('maps/odp')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('ODPs')}
                                    </a>
                                </li>
                                {$_MENU_MAPS}
                            </ul>
                        </li>
                        <!-- Reports Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'reports'}active{/if} treeview" role="none">
                            {if in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
                                <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                    <i class="nav-icon ion ion-clipboard" aria-hidden="true"></i>
                                    <span class="nav-text">{Lang::T('Reports')}</span>
                                    <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                                </a>
                            {/if}
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'reports' }active{/if}" role="none">
                                    <a href="{Text::url('reports')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Daily Reports')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'activation' }active{/if}" role="none">
                                    <a href="{Text::url('reports/activation')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Activation History')}
                                    </a>
                                </li>
                                {$_MENU_REPORTS}
                            </ul>
                        </li>
                    {$_MENU_AFTER_REPORTS}
                        <!-- Message Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'message'}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-android-chat" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Send Message')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'send' }active{/if}" role="none">
                                    <a href="{Text::url('message/send')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Single Customer')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'send_bulk' }active{/if}" role="none">
                                    <a href="{Text::url('message/send_bulk')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Bulk Customers')}
                                    </a>
                                </li>
                                {$_MENU_MESSAGE}
                            </ul>
                        </li>
                    {$_MENU_AFTER_MESSAGE}
                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                        <!-- Network Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'network'}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-network" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Network')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[0] eq 'routers' and $_routes[1] eq '' }active{/if}" role="none">
                                    <a href="{Text::url('routers')}" class="nav-sublink" role="menuitem">
                                        Routers
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[0] eq 'pool' and $_routes[1] eq 'list' }active{/if}" role="none">
                                    <a href="{Text::url('pool/list')}" class="nav-sublink" role="menuitem">
                                        IP Pool
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[0] eq 'pool' and $_routes[1] eq 'port' }active{/if}" role="none">
                                    <a href="{Text::url('pool/port')}" class="nav-sublink" role="menuitem">
                                        Port Pool
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[0] eq 'odp' and $_routes[1] eq '' }active{/if}" role="none">
                                    <a href="{Text::url('odp')}" class="nav-sublink" role="menuitem">
                                        ODP List
                                    </a>
                                </li>
                                {$_MENU_NETWORK}
                            </ul>
                        </li>
                        {$_MENU_AFTER_NETWORKS}
                        {if $_c['radius_enable']}
                            <!-- Radius Menu -->
                            <li class="nav-item has-submenu {if $_system_menu eq 'radius'}active{/if} treeview" role="none">
                                <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                    <i class="nav-icon fa fa-database" aria-hidden="true"></i>
                                    <span class="nav-text">{Lang::T('Radius')}</span>
                                    <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                                </a>
                                <ul class="nav-submenu treeview-menu" role="menu">
                                    <li class="nav-subitem {if $_routes[0] eq 'radius' and $_routes[1] eq 'nas-list' }active{/if}" role="none">
                                        <a href="{Text::url('radius/nas-list')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Radius NAS')}
                                        </a>
                                    </li>
                                    {$_MENU_RADIUS}
                                </ul>
                            </li>
                        {/if}
                        {$_MENU_AFTER_RADIUS}
                        <!-- Static Pages Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'pages'}active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-document" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T("Static Pages")}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'Order_Voucher' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Order_Voucher')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Order Voucher')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Voucher' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Voucher')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Theme Voucher')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Announcement' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Announcement')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Announcement')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Announcement_Customer' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Announcement_Customer')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Customer Announcement')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Registration_Info' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Registration_Info')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Registration Info')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Payment_Info' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Payment_Info')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Payment Info')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Privacy_Policy' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Privacy_Policy')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Privacy Policy')}
                                    </a>
                                </li>
                                <li class="nav-subitem {if $_routes[1] eq 'Terms_and_Conditions' }active{/if}" role="none">
                                    <a href="{Text::url('pages/Terms_and_Conditions')}" class="nav-sublink" role="menuitem">
                                        {Lang::T('Terms and Conditions')}
                                    </a>
                                </li>
                                {$_MENU_PAGES}
                            </ul>
                        </li>
                    {/if}
                    {$_MENU_AFTER_PAGES}
                        <!-- Settings Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'settings' || $_system_menu eq 'paymentgateway' }active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-gear-a" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Settings')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <li class="nav-subitem {if $_routes[1] eq 'app' }active{/if}" role="none">
                                        <a href="{Text::url('settings/app')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('General Settings')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[1] eq 'localisation' }active{/if}" role="none">
                                        <a href="{Text::url('settings/localisation')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Localisation')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[0] eq 'customfield' }active{/if}" role="none">
                                        <a href="{Text::url('customfield')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Custom Fields')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[1] eq 'miscellaneous' }active{/if}" role="none">
                                        <a href="{Text::url('settings/miscellaneous')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Miscellaneous')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[1] eq 'maintenance' }active{/if}" role="none">
                                        <a href="{Text::url('settings/maintenance')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Maintenance Mode')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[0] eq 'widgets' }active{/if}" role="none">
                                        <a href="{Text::url('widgets')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Widgets')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[1] eq 'notifications' }active{/if}" role="none">
                                        <a href="{Text::url('settings/notifications')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('User Notification')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_routes[1] eq 'devices' }active{/if}" role="none">
                                        <a href="{Text::url('settings/devices')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Devices')}
                                        </a>
                                    </li>
                                {/if}
                                {if in_array($_admin['user_type'],['SuperAdmin','Admin','Agent'])}
                                    <li class="nav-subitem {if $_routes[1] eq 'users' }active{/if}" role="none">
                                        <a href="{Text::url('settings/users')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Administrator Users')}
                                        </a>
                                    </li>
                                {/if}
                                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <li class="nav-subitem {if $_routes[1] eq 'dbstatus' }active{/if}" role="none">
                                        <a href="{Text::url('settings/dbstatus')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Backup/Restore')}
                                        </a>
                                    </li>
                                    <li class="nav-subitem {if $_system_menu eq 'paymentgateway' }active{/if}" role="none">
                                        <a href="{Text::url('paymentgateway')}" class="nav-sublink" role="menuitem">
                                            {Lang::T('Payment Gateway')}
                                        </a>
                                    </li>
                                    {$_MENU_SETTINGS}
                                    <li class="nav-subitem {if $_routes[0] eq 'pluginmanager' }active{/if}" role="none">
                                        <a href="{Text::url('pluginmanager')}" class="nav-sublink" role="menuitem">
                                            <i class="nav-subicon glyphicon glyphicon-tasks" aria-hidden="true"></i>
                                            {Lang::T('Plugin Manager')}
                                        </a>
                                    </li>
                                {/if}
                        </ul>
                    </li>
                    {$_MENU_AFTER_SETTINGS}
                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                        <!-- Logs Menu -->
                        <li class="nav-item has-submenu {if $_system_menu eq 'logs' }active{/if} treeview" role="none">
                            <a href="#" class="nav-link submenu-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">
                                <i class="nav-icon ion ion-clock" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Logs')}</span>
                                <i class="nav-arrow fa fa-angle-left" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu treeview-menu" role="menu">
                                <li class="nav-subitem {if $_routes[1] eq 'list' }active{/if}" role="none">
                                    <a href="{Text::url('logs/phpnuxbill')}" class="nav-sublink" role="menuitem">
                                        PhpNuxBill
                                    </a>
                                </li>
                                {if $_c['radius_enable']}
                                    <li class="nav-subitem {if $_routes[1] eq 'radius' }active{/if}" role="none">
                                        <a href="{Text::url('logs/radius')}" class="nav-sublink" role="menuitem">
                                            Radius
                                        </a>
                                    </li>
                                {/if}
                                <li class="nav-subitem {if $_routes[1] eq 'message' }active{/if}" role="none">
                                    <a href="{Text::url('logs/message')}" class="nav-sublink" role="menuitem">
                                        Message
                                    </a>
                                </li>
                                {$_MENU_LOGS}
                            </ul>
                        </li>
                    {/if}
                    {$_MENU_AFTER_LOGS}
                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                        <!-- Documentation -->
                        <li class="nav-item {if $_routes[1] eq 'docs' }active{/if}" role="none">
                            <a href="{if $_c['docs_clicked'] != 'yes'}{Text::url('settings/docs')}{else}{$app_url}/docs{/if}" 
                               class="nav-link" role="menuitem">
                                <i class="nav-icon ion ion-ios-bookmarks" aria-hidden="true"></i>
                                <span class="nav-text">{Lang::T('Documentation')}</span>
                                {if $_c['docs_clicked'] != 'yes'}
                                    <span class="nav-badge">
                                        <small class="badge badge-success">New</small>
                                    </span>
                                {/if}
                            </a>
                        </li>
                        
                        <!-- Community -->
                        <li class="nav-item {if $_system_menu eq 'community' }active{/if}" role="none">
                            <a href="{Text::url('community')}" class="nav-link" role="menuitem">
                                <i class="nav-icon ion ion-chatboxes" aria-hidden="true"></i>
                                <span class="nav-text">Community</span>
                            </a>
                        </li>
                    {/if}
                    {$_MENU_AFTER_COMMUNITY}
                    </ul>
                </nav>
            </section>
        </aside>

        <!-- Maintenance Mode Notification -->
        {if $_c['maintenance_mode'] == 1}
            <div class="notification-top-bar maintenance-notice" role="alert">
                <div class="maintenance-content">
                    <i class="fa fa-exclamation-triangle maintenance-icon" aria-hidden="true"></i>
                    <p class="maintenance-text">
                        {Lang::T('The website is currently in maintenance mode, this means that some or all functionality may be unavailable to regular users during this time.')}
                        <small class="maintenance-action">
                            &nbsp;&nbsp;<a href="{Text::url('settings/maintenance')}" class="maintenance-link">
                                {Lang::T('Turn Off')}
                            </a>
                        </small>
                    </p>
                </div>
            </div>
        {/if}

        <!-- Main Content Wrapper -->
        <div class="content-wrapper main-content" role="main" id="main-content" aria-label="Main content">
            <!-- Content Header -->
            <section class="content-header page-header">
                <div class="header-container">
                    <h1 class="page-title">
                        {$_title}
                    </h1>
                    <!-- Breadcrumb can be added here in future -->
                </div>
            </section>

            <!-- Main Content -->
            <section class="content page-content">
                <!-- Notification Handler -->
                {if isset($notify)}
                    <script>
                        // Display SweetAlert toast notification
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: '{if $notify_t == "s"}success{else}error{/if}',
                                title: '{$notify}',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                toast: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });
                        });
                    </script>
                {/if}