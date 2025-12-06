{include file="sections/header.tpl"}

<!-- Admin Dashboard Container -->
<div class="dashboard-container admin-dashboard">
    <!-- Dashboard Header -->
    <header class="dashboard-header content-header" role="banner">
        <div class="header-content">
            <h1 class="dashboard-title page-title">
                <i class="dashboard-icon ion ion-monitor" aria-hidden="true"></i>
                <span class="title-text">{Lang::T('Dashboard')}</span>
                <small class="title-subtitle">{Lang::T('Control Panel')}</small>
            </h1>
            <!-- Breadcrumb can be added here in future -->
        </div>
    </header>

    <!-- Dashboard Main Content -->
    <main class="dashboard-main content" role="main">
        {* System Notifications Container *}
        {if isset($notify)}
            <div class="dashboard-notifications notifications-container" role="region" aria-label="System notifications">
                <div class="notification-wrapper">
                    <div class="alert alert-{$notify.type} alert-dismissible notification-alert alert-modern" role="alert" aria-live="polite">
                        <div class="alert-content alert-body">
                            <div class="alert-icon-wrapper">
                                <i class="alert-icon fa {if $notify.type == 'success'}fa-check-circle{elseif $notify.type == 'warning'}fa-exclamation-triangle{elseif $notify.type == 'danger'}fa-times-circle{else}fa-info-circle{/if}" aria-hidden="true"></i>
                            </div>
                            <div class="alert-message alert-text-content">
                                <div class="alert-title alert-heading">
                                    <strong class="alert-title-text">
                                        {if $notify.type == 'success'}{Lang::T('Success')}{elseif $notify.type == 'warning'}{Lang::T('Warning')}{elseif $notify.type == 'danger'}{Lang::T('Error')}{else}{Lang::T('Info')}{/if}:
                                    </strong>
                                </div>
                                <div class="alert-description">
                                    <span class="alert-text alert-message-text">{$notify.text}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="alert-close alert-dismiss btn-close" data-dismiss="alert" aria-label="Dismiss notification">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        {/if}

        {* Widget Display Function *}
        {function showWidget pos=0}
            {foreach $widgets as $w}
                {if $w['position'] == $pos}
                    {$w['content']}
                {/if}
            {/foreach}
        {/function}

        {* Dashboard Widget Grid System *}
        {assign var="dtipe" value="dashboard_`$tipeUser`"}
        {if isset($_c[$dtipe]) && $_c[$dtipe] != ''}
            {assign var="rows" value="."|explode:$_c[$dtipe]}
        {else}
            {assign var="rows" value="."|explode:"12.7,5.12"}
        {/if}
        {assign var="pos" value=1}
        
        <!-- Dashboard Widgets Container -->
        <div class="dashboard-widgets widgets-container" role="region" aria-label="Dashboard widgets">
            {foreach $rows as $cols}
                {if $cols == 12}
                    <!-- Full Width Widget Row -->
                    <div class="widget-row row dashboard-row">
                        <div class="widget-col col-md-12 dashboard-col">
                            <div class="widget-wrapper full-width-widget" role="region" aria-live="polite">
                                {showWidget widgets=$widgets pos=$pos}
                            </div>
                        </div>
                    </div>
                    {assign var="pos" value=$pos+1}
                {else}
                    {assign var="colss" value=","|explode:$cols}
                    <!-- Multi-Column Widget Row -->
                    <div class="widget-row row dashboard-row multi-col-row">
                        {foreach $colss as $c}
                            <div class="widget-col col-md-{$c} dashboard-col col-{$c}">
                                <div class="widget-wrapper widget-col-{$c}" role="region" aria-live="polite">
                                    {showWidget widgets=$widgets pos=$pos}
                                </div>
                            </div>
                            {assign var="pos" value=$pos+1}
                        {/foreach}
                    </div>
                {/if}
            {/foreach}
        </div>
    </main>
</div>

{* Version Update Notification System *}
{if $_c['new_version_notify'] != 'disable'}
    <script>
        // Version Check and Update Notification
        document.addEventListener('DOMContentLoaded', function() {
            // Check local version
            fetch("./version.json?" + Math.random())
                .then(response => response.json())
                .then(data => {
                    const localVersion = data.version;
                    const versionElement = document.getElementById('version');
                    if (versionElement) {
                        versionElement.innerHTML = 'Version: ' + localVersion;
                    }
                    
                    // Check for latest version
                    fetch("https://raw.githubusercontent.com/hotspotbilling/phpnuxbill/master/version.json?" + Math.random())
                        .then(response => response.json())
                        .then(data => {
                            const latestVersion = data.version;
                            if (localVersion !== latestVersion) {
                                if (versionElement) {
                                    versionElement.innerHTML = 'Latest Version: ' + latestVersion;
                                }
                                
                                // Show update notification if not already dismissed
                                if (getCookie(latestVersion) !== 'done') {
                                    Swal.fire({
                                        icon: 'info',
                                        title: "New Version Available\nVersion: " + latestVersion,
                                        toast: true,
                                        position: 'bottom-right',
                                        showConfirmButton: true,
                                        showCloseButton: true,
                                        timer: 30000,
                                        confirmButtonText: '<a href="{Text::url('community')}#latestVersion" style="color: white; text-decoration: none;">Update Now</a>',
                                        timerProgressBar: true,
                                        customClass: {
                                            popup: 'version-update-toast',
                                            title: 'version-update-title',
                                            confirmButton: 'version-update-btn'
                                        },
                                        didOpen: (toast) => {
                                            toast.addEventListener('mouseenter', Swal.stopTimer);
                                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                                        }
                                    });
                                    setCookie(latestVersion, 'done', 7);
                                }
                            }
                        })
                        .catch(error => {
                            console.warn('Could not check for updates:', error);
                        });
                })
                .catch(error => {
                    console.warn('Could not load version information:', error);
                });
        });
    </script>
{/if}

{include file="sections/footer.tpl"}