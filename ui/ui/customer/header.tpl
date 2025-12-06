<!DOCTYPE html>
<html lang="en" class="modern-app has-sidebar has-header">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$_title} - {$_c['CompanyName']}</title>

    <script>
        var appUrl = '{$app_url}';
    </script>

    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/modern-AdminLTE.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
    <script src="{$app_url}/ui/ui/scripts/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill.customer.css?2025.2.4" />
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill-modern.css?v=1.0.0" />

    <style>

    </style>

    {if isset($xheader)}
        {$xheader}
    {/if}

</head>

<body class="modern-app-body customer-portal">
    <div class="app-wrapper">
        <!-- Main Header -->
        <header class="main-header header-fixed">
            <div class="header-container">
                <!-- Logo Section -->
                <div class="header-brand">
                    <a href="{Text::url('home')}" class="brand-link">
                        <span class="brand-mini"><b>N</b>uX</span>
                        <span class="brand-text">{$_c['CompanyName']}</span>
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle btn-icon" data-toggle="push-menu" role="button" 
                        aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="customer-sidebar">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                    <span class="sr-only">Toggle navigation</span>
                </button>

                <!-- Header Navigation -->
                <nav class="header-nav">
                    <ul class="nav-list">
                        <!-- Theme Toggle -->
                        <li class="nav-item">
                            <a class="nav-link theme-toggle" href="#" aria-label="Toggle between light and dark theme" role="button">
                                <i class="toggle-icon" id="toggleIcon" aria-hidden="true">🌜</i>
                            </a>
                        </li>

                        <!-- Language Selector -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false" 
                               aria-label="Select language" aria-haspopup="true" role="button">
                                <i class="fa fa-flag-o" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-label="Language options">
                                <li class="dropdown-content">
                                    <ul class="language-menu" api-get-text="{Text::url('autoload_user/language&select=',$user_language)}" role="menu"></ul>
                                </li>
                            </ul>
                        </li>

                        <!-- Notifications -->
                        <li class="nav-item dropdown notifications-dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false" 
                               aria-label="View notifications" aria-haspopup="true" role="button">
                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                <span class="badge badge-warning notification-count" api-get-text="{Text::url('autoload_user/inbox_unread')}" 
                                      aria-label="Unread notifications count"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right notifications-menu" role="menu" aria-label="Notifications">
                                <li class="dropdown-content">
                                    <ul class="notification-list" api-get-text="{Text::url('autoload_user/inbox')}" role="menu" 
                                        aria-live="polite" aria-label="Notification list"></ul>
                                </li>
                                <li class="dropdown-footer">
                                    <a href="{Text::url('mail')}" class="btn btn-sm btn-primary" role="menuitem">{Lang::T('Inbox')}</a>
                                </li>
                            </ul>
                        </li>

                        <!-- User Menu -->
                        <li class="nav-item dropdown user-dropdown">
                            <a href="#" class="nav-link dropdown-toggle user-menu-toggle" data-toggle="dropdown" aria-expanded="false">
                                <div class="user-info">
                                    {if $_c['enable_balance'] == 'yes'}
                                        <span class="user-balance">{Lang::moneyFormat($_user['balance'])}</span>
                                    {else}
                                        <span class="user-name">{$_user['fullname']}</span>
                                    {/if}
                                    <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                        onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" 
                                        class="user-avatar" alt="User Avatar">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right user-menu">
                                <li class="user-header">
                                    <div class="user-profile">
                                        <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg"
                                            onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'" 
                                            class="user-profile-image" alt="User Profile">
                                        <div class="user-details">
                                            <h4 class="user-name">{$_user['fullname']}</h4>
                                            <p class="user-contact">
                                                <small>{$_user['phonenumber']}<br>{$_user['email']}</small>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                                <li class="user-body">
                                    <div class="user-actions">
                                        <div class="action-item">
                                            <a href="{Text::url('accounts/change-password')}" class="action-link">
                                                <i class="ion ion-settings"></i>
                                                <span>{Lang::T('Change Password')}</span>
                                            </a>
                                        </div>
                                        <div class="action-item">
                                            <a href="{Text::url('accounts/profile')}" class="action-link">
                                                <i class="ion ion-person"></i>
                                                <span>{Lang::T('My Account')}</span>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                <li class="user-footer">
                                    <a href="{Text::url('logout')}" class="btn btn-default btn-block logout-btn">
                                        <i class="ion ion-power"></i> {Lang::T('Logout')}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Sidebar Navigation -->
        <aside id="customer-sidebar" class="main-sidebar sidebar-fixed" role="navigation" aria-label="Customer navigation">
            <div class="sidebar-container">
                <nav class="sidebar-nav">
                    <ul class="nav-menu" data-widget="tree" role="menubar">
                        <!-- Dashboard -->
                        <li class="nav-item {if $_system_menu eq 'home'}active{/if}">
                            <a href="{Text::url('home')}" class="nav-link">
                                <i class="nav-icon ion ion-monitor"></i>
                                <span class="nav-text">{Lang::T('Dashboard')}</span>
                            </a>
                        </li>
                        
                        {$_MENU_AFTER_DASHBOARD}
                        
                        <!-- Inbox -->
                        <li class="nav-item {if $_system_menu eq 'inbox'}active{/if}">
                            <a href="{Text::url('mail')}" class="nav-link">
                                <i class="nav-icon fa fa-envelope"></i>
                                <span class="nav-text">{Lang::T('Inbox')}</span>
                            </a>
                        </li>
                        
                        {$_MENU_AFTER_INBOX}
                        
                        <!-- Voucher -->
                        {if $_c['disable_voucher'] != 'yes'}
                            <li class="nav-item {if $_system_menu eq 'voucher'}active{/if}">
                                <a href="{Text::url('voucher/activation')}" class="nav-link">
                                    <i class="nav-icon fa fa-ticket"></i>
                                    <span class="nav-text">Voucher</span>
                                </a>
                            </li>
                        {/if}
                        
                        <!-- Payment Gateway Options -->
                        {if $_c['payment_gateway'] != 'none' or $_c['payment_gateway'] == '' }
                            {if $_c['enable_balance'] == 'yes'}
                                <li class="nav-item {if $_system_menu eq 'balance'}active{/if}">
                                    <a href="{Text::url('order/balance')}" class="nav-link">
                                        <i class="nav-icon ion ion-ios-cart"></i>
                                        <span class="nav-text">{Lang::T('Buy Balance')}</span>
                                    </a>
                                </li>
                            {/if}
                            
                            <li class="nav-item {if $_system_menu eq 'package'}active{/if}">
                                <a href="{Text::url('order/package')}" class="nav-link">
                                    <i class="nav-icon ion ion-ios-cart"></i>
                                    <span class="nav-text">{Lang::T('Buy Package')}</span>
                                </a>
                            </li>
                            
                            <li class="nav-item {if $_system_menu eq 'history'}active{/if}">
                                <a href="{Text::url('order/history')}" class="nav-link">
                                    <i class="nav-icon fa fa-file-text"></i>
                                    <span class="nav-text">{Lang::T('Payment History')}</span>
                                </a>
                            </li>
                        {/if}
                        
                        {$_MENU_AFTER_ORDER}
                        
                        <!-- Activation History -->
                        <li class="nav-item {if $_system_menu eq 'list-activated'}active{/if}">
                            <a href="{Text::url('voucher/list-activated')}" class="nav-link">
                                <i class="nav-icon fa fa-list-alt"></i>
                                <span class="nav-text">{Lang::T('Activation History')}</span>
                            </a>
                        </li>
                        
                        {$_MENU_AFTER_HISTORY}
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="content-wrapper main-content">
            <!-- Content Header -->
            <header class="content-header">
                <div class="content-header-container">
                    <h1 class="page-title">{$_title}</h1>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="content-main">
                <div class="content-container">


                {if isset($notify)}
                    <script>
                        // Display SweetAlert toast notification
                        Swal.fire({
                            icon: '{if $notify_t == "s"}success{else}warning{/if}',
                            title: '{$notify}',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                    </script>
{/if}
