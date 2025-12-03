{include file="sections/header.tpl"}
<!-- vpn-status -->

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary panel-hovered panel-stacked mb30">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-cloud"></i> {Lang::T('VPN Status Dashboard')}
                <div class="btn-group pull-right">
                    <a href="{Text::url('')}routers/vpn-status" class="btn btn-success btn-xs" title="{Lang::T('Refresh')}">
                        <span class="glyphicon glyphicon-refresh"></span> {Lang::T('Refresh')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Summary Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="glyphicon glyphicon-cloud" style="font-size: 48px;"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div style="font-size: 32px; font-weight: bold;">{$stats['total']}</div>
                                        <div>{Lang::T('Total Remote Routers')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="glyphicon glyphicon-ok-circle" style="font-size: 48px;"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div style="font-size: 32px; font-weight: bold;">{$stats['connected']}</div>
                                        <div>{Lang::T('Connected')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-danger">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="glyphicon glyphicon-remove-circle" style="font-size: 48px;"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div style="font-size: 32px; font-weight: bold;">{$stats['disconnected']}</div>
                                        <div>{Lang::T('Disconnected')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="glyphicon glyphicon-exclamation-sign" style="font-size: 48px;"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div style="font-size: 32px; font-weight: bold;">{$stats['errors']}</div>
                                        <div>{Lang::T('Errors')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Rate -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-signal"></i> {Lang::T('Connection Rate')}</h4>
                            </div>
                            <div class="panel-body">
                                <h3 class="text-center">{$stats['connection_rate']}%</h3>
                                <div class="progress" style="height: 30px;">
                                    {if $stats['connection_rate'] >= 80}
                                        <div class="progress-bar progress-bar-success" role="progressbar" 
                                             style="width: {$stats['connection_rate']}%; font-size: 16px; line-height: 30px;">
                                            {$stats['connection_rate']}%
                                        </div>
                                    {elseif $stats['connection_rate'] >= 50}
                                        <div class="progress-bar progress-bar-warning" role="progressbar" 
                                             style="width: {$stats['connection_rate']}%; font-size: 16px; line-height: 30px;">
                                            {$stats['connection_rate']}%
                                        </div>
                                    {else}
                                        <div class="progress-bar progress-bar-danger" role="progressbar" 
                                             style="width: {$stats['connection_rate']}%; font-size: 16px; line-height: 30px;">
                                            {$stats['connection_rate']}%
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Router Status Table -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-list"></i> {Lang::T('Remote Router Status')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>{Lang::T('Router Name')}</th>
                                        <th>{Lang::T('VPN Username')}</th>
                                        <th>{Lang::T('VPN IP')}</th>
                                        <th>{Lang::T('Status')}</th>
                                        <th>{Lang::T('Last Check')}</th>
                                        <th>{Lang::T('Uptime')} (30d)</th>
                                        <th>{Lang::T('Certificate')}</th>
                                        <th>{Lang::T('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $routers}
                                        {foreach $routers as $router}
                                            <tr>
                                                <td>
                                                    {if $router['coordinates']}
                                                        <a href="https://www.google.com/maps/dir//{$router['coordinates']}/" target="_blank"
                                                            class="btn btn-default btn-xs" title="{$router['coordinates']}">
                                                            <i class="glyphicon glyphicon-map-marker"></i>
                                                        </a>
                                                    {/if}
                                                    {$router['name']}
                                                </td>
                                                <td>{$router['vpn_username']}</td>
                                                <td>
                                                    {if $router['vpn_ip']}
                                                        <code>{$router['vpn_ip']}</code>
                                                    {else}
                                                        <span class="text-muted">{Lang::T('Pending')}</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $router['ovpn_status'] == 'connected'}
                                                        <span class="label label-success">
                                                            <i class="glyphicon glyphicon-ok-circle"></i> {Lang::T('Connected')}
                                                        </span>
                                                    {elseif $router['ovpn_status'] == 'disconnected'}
                                                        <span class="label label-danger">
                                                            <i class="glyphicon glyphicon-remove-circle"></i> {Lang::T('Disconnected')}
                                                        </span>
                                                    {elseif $router['ovpn_status'] == 'error'}
                                                        <span class="label label-warning">
                                                            <i class="glyphicon glyphicon-exclamation-sign"></i> {Lang::T('Error')}
                                                        </span>
                                                    {else}
                                                        <span class="label label-default">
                                                            <i class="glyphicon glyphicon-time"></i> {Lang::T('Pending')}
                                                        </span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $router['last_vpn_check']}
                                                        {$router['last_vpn_check']}
                                                    {else}
                                                        <span class="text-muted">{Lang::T('Never')}</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if isset($router['uptime_percentage'])}
                                                        {if $router['uptime_percentage'] >= 95}
                                                            <span class="label label-success">{$router['uptime_percentage']}%</span>
                                                        {elseif $router['uptime_percentage'] >= 80}
                                                            <span class="label label-info">{$router['uptime_percentage']}%</span>
                                                        {elseif $router['uptime_percentage'] >= 50}
                                                            <span class="label label-warning">{$router['uptime_percentage']}%</span>
                                                        {else}
                                                            <span class="label label-danger">{$router['uptime_percentage']}%</span>
                                                        {/if}
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $router['certificate_expiry']}
                                                        {assign var="cert_date" value=$router['certificate_expiry']|strtotime}
                                                        {assign var="now" value=$smarty.now}
                                                        {assign var="days_left" value=(($cert_date - $now) / 86400)|floor}
                                                        {if $days_left < 7}
                                                            <span class="label label-danger">
                                                                <i class="glyphicon glyphicon-warning-sign"></i> {$days_left} {Lang::T('days')}
                                                            </span>
                                                        {elseif $days_left < 30}
                                                            <span class="label label-warning">
                                                                <i class="glyphicon glyphicon-time"></i> {$days_left} {Lang::T('days')}
                                                            </span>
                                                        {else}
                                                            <span class="label label-success">
                                                                <i class="glyphicon glyphicon-ok"></i> {$days_left} {Lang::T('days')}
                                                            </span>
                                                        {/if}
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    <a href="{Text::url('')}routers/edit/{$router['id']}" 
                                                       class="btn btn-info btn-xs" title="{Lang::T('Edit')}">
                                                        <i class="glyphicon glyphicon-edit"></i>
                                                    </a>
                                                    <a href="{Text::url('')}routers/vpn-logs/{$router['id']}" 
                                                       class="btn btn-warning btn-xs" title="{Lang::T('View Logs')}">
                                                        <i class="glyphicon glyphicon-list-alt"></i>
                                                    </a>
                                                    <a href="{Text::url('')}routers/test-vpn-connection/{$router['id']}" 
                                                       class="btn btn-success btn-xs" title="{Lang::T('Test Connection')}">
                                                        <i class="glyphicon glyphicon-flash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <p class="text-muted">{Lang::T('No remote routers configured yet')}</p>
                                                <a href="{Text::url('')}routers/add" class="btn btn-primary">
                                                    <i class="glyphicon glyphicon-plus"></i> {Lang::T('Add Remote Router')}
                                                </a>
                                            </td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bs-callout bs-callout-info">
                    <h4><i class="glyphicon glyphicon-info-sign"></i> {Lang::T('About VPN Status')}</h4>
                    <p>{Lang::T('This dashboard shows the real-time status of all remote routers connected via OpenVPN. The system automatically checks connection status every 5 minutes.')}</p>
                    <ul>
                        <li><strong>{Lang::T('Connected')}:</strong> {Lang::T('Router is online and accessible via VPN')}</li>
                        <li><strong>{Lang::T('Disconnected')}:</strong> {Lang::T('Router VPN connection is down')}</li>
                        <li><strong>{Lang::T('Error')}:</strong> {Lang::T('Connection check encountered an error')}</li>
                        <li><strong>{Lang::T('Pending')}:</strong> {Lang::T('Router has not been checked yet')}</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
