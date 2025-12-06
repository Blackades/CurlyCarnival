{include file="sections/header.tpl"}



<!-- Dashboard Content Container (No content-wrapper class to avoid double margins) -->
<div class="dashboard-content">
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
        {if isset($_c[$dtipe]) && $_c[$dtipe] != ''}
            {assign var="rows" value="."|explode:$_c[$dtipe]}
        {else}
            {assign var="rows" value="."|explode:"12.7,5.12"}
        {/if}
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