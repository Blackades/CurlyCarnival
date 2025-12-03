<div class="box box-warning">
    <div class="box-header">
        <h3 class="box-title">VPN Certificate Status</h3>
        <div class="box-tools pull-right">
            <a href="{Text::url('routers/list')}" class="btn btn-box-tool" title="View Routers">
                <i class="fa fa-external-link"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        {if $cert_total_active > 0 || $cert_expired > 0 || $cert_revoked > 0}
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-6">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-certificate"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Certificates</span>
                            <span class="info-box-number">{$cert_total_active}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-6">
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Expiring Soon</span>
                            <span class="info-box-number">{$cert_expiring_soon}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-6">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-exclamation-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Expired</span>
                            <span class="info-box-number">{$cert_expired}</span>
                        </div>
                    </div>
                </div>
            </div>

            {if $cert_expired > 0}
            <div class="row">
                <div class="col-md-12">
                    <div class="callout callout-danger">
                        <h4><i class="fa fa-exclamation-triangle"></i> Attention Required!</h4>
                        <p>{$cert_expired} certificate(s) have expired and need immediate renewal.</p>
                    </div>
                </div>
            </div>
            {/if}

            {if count($expiring_certs) > 0}
            <div class="row">
                <div class="col-md-12">
                    <h4 class="text-bold">Certificates Expiring Within 30 Days</h4>
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>Router</th>
                                    <th>Client Name</th>
                                    <th>Expiry Date</th>
                                    <th>Days Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $expiring_certs as $cert}
                                <tr>
                                    <td>{$cert.router_name}</td>
                                    <td><code>{$cert.client_name}</code></td>
                                    <td>{date('M d, Y', strtotime($cert.expiry_date))}</td>
                                    <td>
                                        <span class="badge badge-{if $cert.days_remaining <= 7}danger{elseif $cert.days_remaining <= 14}warning{else}info{/if}">
                                            {$cert.days_remaining} days
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{Text::url('routers/edit')}/{$cert.router_id}" 
                                           class="btn btn-xs btn-primary" 
                                           title="Renew Certificate">
                                            <i class="fa fa-refresh"></i> Renew
                                        </a>
                                    </td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    {if $cert_expiring_soon > 5}
                    <p class="text-center">
                        <small class="text-muted">Showing 5 of {$cert_expiring_soon} expiring certificates</small>
                    </p>
                    {/if}
                </div>
            </div>
            {elseif $cert_expired == 0}
            <div class="row">
                <div class="col-md-12">
                    <div class="callout callout-success">
                        <p><i class="fa fa-check-circle"></i> All certificates are valid for more than 30 days.</p>
                    </div>
                </div>
            </div>
            {/if}
        {else}
            <div class="callout callout-info">
                <h4><i class="fa fa-info-circle"></i> No Certificates</h4>
                <p>No VPN certificates found. <a href="{Text::url('routers/add')}">Add a remote router</a> to generate certificates.</p>
            </div>
        {/if}
    </div>
</div>
