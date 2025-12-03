<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">VPN Connection Status</h3>
        <div class="box-tools pull-right">
            <a href="{Text::url('routers/vpn-status')}" class="btn btn-box-tool" title="View Details">
                <i class="fa fa-external-link"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        {if $vpn_total > 0}
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-server"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Routers</span>
                            <span class="info-box-number">{$vpn_total}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Connected</span>
                            <span class="info-box-number">{$vpn_connected}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Disconnected</span>
                            <span class="info-box-number">{$vpn_disconnected}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-6">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Errors</span>
                            <span class="info-box-number">{$vpn_error}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="progress-group">
                        <span class="progress-text">Connection Rate</span>
                        <span class="progress-number"><b>{$vpn_connected}</b>/{$vpn_total}</span>
                        <div class="progress sm">
                            <div class="progress-bar progress-bar-{if $vpn_connection_rate >= 80}success{elseif $vpn_connection_rate >= 50}warning{else}danger{/if}" 
                                 style="width: {$vpn_connection_rate}%"></div>
                        </div>
                        <span class="progress-number">{$vpn_connection_rate}%</span>
                    </div>
                </div>
            </div>
            {if $vpn_pending > 0}
            <div class="row">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <p><i class="fa fa-info-circle"></i> {$vpn_pending} router(s) pending configuration</p>
                    </div>
                </div>
            </div>
            {/if}
        {else}
            <div class="callout callout-info">
                <h4><i class="fa fa-info-circle"></i> No Remote Routers</h4>
                <p>No remote routers configured yet. <a href="{Text::url('routers/add')}">Add a remote router</a> to get started.</p>
            </div>
        {/if}
    </div>
</div>
