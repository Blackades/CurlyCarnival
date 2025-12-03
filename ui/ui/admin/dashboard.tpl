{include file="sections/header.tpl"}

<style>
/* Dashboard Critical Fixes - Inline to prevent override */
.content-wrapper.dashboard-content { padding: 0 !important; }
.dashboard-content .content-header { padding: 10px 15px !important; margin-bottom: 0 !important; }
.dashboard-content .content { padding: 15px !important; background-color: #f5f5f5 !important; }
.dashboard-main { padding: 15px !important; background-color: #f5f5f5 !important; }

/* Info Box Fixes */
.info-box { display: flex !important; align-items: center !important; min-height: 90px !important; padding: 10px !important; margin-bottom: 15px !important; border-radius: 8px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important; }
.info-box-icon { display: flex !important; align-items: center !important; justify-content: center !important; width: 70px !important; min-width: 70px !important; height: 70px !important; font-size: 32px !important; border-radius: 8px !important; margin-right: 15px !important; flex-shrink: 0 !important; }
.info-box-content { flex: 1 !important; display: flex !important; flex-direction: column !important; justify-content: center !important; }
.info-box-text { display: block !important; font-size: 13px !important; font-weight: 500 !important; margin-bottom: 4px !important; }
.info-box-number { display: block !important; font-size: 24px !important; font-weight: 700 !important; line-height: 1.2 !important; margin-bottom: 6px !important; }
.info-box-link { display: inline-flex !important; align-items: center !important; font-size: 12px !important; text-decoration: none !important; }

/* Colored Boxes */
.info-box.bg-aqua { background: linear-gradient(135deg, #00c0ef 0%, #00a7d0 100%) !important; }
.info-box.bg-aqua .info-box-icon { background-color: rgba(0,0,0,0.1) !important; }
.info-box.bg-aqua .info-box-text, .info-box.bg-aqua .info-box-number, .info-box.bg-aqua .info-box-link { color: rgba(255,255,255,0.95) !important; }

.info-box.bg-green { background: linear-gradient(135deg, #00a65a 0%, #008d4c 100%) !important; }
.info-box.bg-green .info-box-icon { background-color: rgba(0,0,0,0.1) !important; }
.info-box.bg-green .info-box-text, .info-box.bg-green .info-box-number, .info-box.bg-green .info-box-link { color: rgba(255,255,255,0.95) !important; }

.info-box.bg-yellow { background: linear-gradient(135deg, #f39c12 0%, #e08e0b 100%) !important; }
.info-box.bg-yellow .info-box-icon { background-color: rgba(0,0,0,0.1) !important; }
.info-box.bg-yellow .info-box-text, .info-box.bg-yellow .info-box-number, .info-box.bg-yellow .info-box-link { color: rgba(255,255,255,0.95) !important; }

.info-box.bg-red { background: linear-gradient(135deg, #dd4b39 0%, #d33724 100%) !important; }
.info-box.bg-red .info-box-icon { background-color: rgba(0,0,0,0.1) !important; }
.info-box.bg-red .info-box-text, .info-box.bg-red .info-box-number, .info-box.bg-red .info-box-link { color: rgba(255,255,255,0.95) !important; }

.dashboard-widgets .widget-row { margin-bottom: 15px !important; margin-left: -7.5px !important; margin-right: -7.5px !important; }
.dashboard-widgets .widget-col { padding-left: 7.5px !important; padding-right: 7.5px !important; }
</style>

<!-- Dashboard Content Wrapper -->
<div class="content-wrapper dashboard-content">
    <section class="content-header">
        <h1 class="dashboard-title">
            {Lang::T('Dashboard')}
            <small>{Lang::T('Control Panel')}</small>
        </h1>
    </section>

    <section class="content dashboard-main">
        {* Alert Messages Container - for system notifications *}
        {if isset($notify)}
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-{$notify.type} alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>{if $notify.type == 'success'}{Lang::T('Success')}{elseif $notify.type == 'warning'}{Lang::T('Warning')}{elseif $notify.type == 'danger'}{Lang::T('Error')}{else}{Lang::T('Info')}{/if}:</strong>
                        {$notify.text}
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

        {* Dynamic Widget Grid Layout *}
        {assign var="dtipe" value="dashboard_`$tipeUser`"}
        {assign var="rows" value="."|explode:$_c[$dtipe]}
        {assign var="pos" value=1}
        
        <div class="dashboard-widgets">
            {foreach $rows as $cols}
                {if $cols == 12}
                    <div class="row widget-row">
                        <div class="col-md-12">
                            {showWidget widgets=$widgets pos=$pos}
                        </div>
                    </div>
                    {assign var="pos" value=$pos+1}
                {else}
                    {assign var="colss" value=","|explode:$cols}
                    <div class="row widget-row">
                        {foreach $colss as $c}
                            <div class="col-md-{$c} widget-col">
                                {showWidget widgets=$widgets pos=$pos}
                            </div>
                            {assign var="pos" value=$pos+1}
                        {/foreach}
                    </div>
                {/if}
            {/foreach}
        </div>
    </section>
</div>

{if $_c['new_version_notify'] != 'disable'}
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            $.getJSON("./version.json?" + Math.random(), function(data) {
                var localVersion = data.version;
                $('#version').html('Version: ' + localVersion);
                $.getJSON(
                    "https://raw.githubusercontent.com/hotspotbilling/phpnuxbill/master/version.json?" +
                    Math
                    .random(),
                    function(data) {
                        var latestVersion = data.version;
                        if (localVersion !== latestVersion) {
                            $('#version').html('Latest Version: ' + latestVersion);
                            if (getCookie(latestVersion) != 'done') {
                                Swal.fire({
                                    icon: 'info',
                                    title: "New Version Available\nVersion: " + latestVersion,
                                    toast: true,
                                    position: 'bottom-right',
                                    showConfirmButton: true,
                                    showCloseButton: true,
                                    timer: 30000,
                                    confirmButtonText: '<a href="{Text::url('community')}#latestVersion" style="color: white;">Update Now</a>',
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal
                                            .resumeTimer)
                                    }
                                });
                                setCookie(latestVersion, 'done', 7);
                            }
                        }
                    });
            });

        });
    </script>
{/if}

{include file="sections/footer.tpl"}