{include file="sections/header.tpl"}
<!-- vpn-logs -->

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary panel-hovered panel-stacked mb30">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-list-alt"></i> {Lang::T('VPN Logs')} - {$router['name']}
                <div class="btn-group pull-right">
                    <a href="{Text::url('')}routers/list" class="btn btn-default btn-xs">
                        <span class="glyphicon glyphicon-arrow-left"></span> {Lang::T('Back to List')}
                    </a>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Router Information Header -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-info-sign"></i> {Lang::T('Router Information')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>{Lang::T('Router Name')}:</strong><br>
                                {$router['name']}
                            </div>
                            <div class="col-md-3">
                                <strong>{Lang::T('VPN Username')}:</strong><br>
                                {$router['vpn_username']}
                            </div>
                            <div class="col-md-3">
                                <strong>{Lang::T('VPN IP')}:</strong><br>
                                {if $router['vpn_ip']}
                                    <code>{$router['vpn_ip']}</code>
                                {else}
                                    <span class="text-muted">{Lang::T('Not assigned')}</span>
                                {/if}
                            </div>
                            <div class="col-md-3">
                                <strong>{Lang::T('Current Status')}:</strong><br>
                                {if $router['ovpn_status'] == 'connected'}
                                    <span class="status-indicator">
                                        <span class="status-dot status-dot-success"></span>
                                        <span class="badge badge-success">
                                            <i class="glyphicon glyphicon-ok-circle"></i> {Lang::T('Connected')}
                                        </span>
                                    </span>
                                {elseif $router['ovpn_status'] == 'disconnected'}
                                    <span class="status-indicator">
                                        <span class="status-dot status-dot-danger"></span>
                                        <span class="badge badge-danger">
                                            <i class="glyphicon glyphicon-remove-circle"></i> {Lang::T('Disconnected')}
                                        </span>
                                    </span>
                                {elseif $router['ovpn_status'] == 'error'}
                                    <span class="status-indicator">
                                        <span class="status-dot status-dot-warning"></span>
                                        <span class="badge badge-warning">
                                            <i class="glyphicon glyphicon-exclamation-sign"></i> {Lang::T('Error')}
                                        </span>
                                    </span>
                                {else}
                                    <span class="status-indicator">
                                        <span class="status-dot status-dot-default"></span>
                                        <span class="badge badge-default">
                                            <i class="glyphicon glyphicon-time"></i> {Lang::T('Pending')}
                                        </span>
                                    </span>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-filter"></i> {Lang::T('Filter Logs')}</h4>
                    </div>
                    <div class="panel-body">
                        <form method="get" action="{Text::url('')}routers/vpn-logs/{$router['id']}" class="form-inline">
                            <div class="form-group">
                                <label>{Lang::T('From Date')}:</label>
                                <input type="date" name="date_from" class="form-control" value="{$filter['date_from']|default:''}">
                            </div>
                            <div class="form-group">
                                <label>{Lang::T('To Date')}:</label>
                                <input type="date" name="date_to" class="form-control" value="{$filter['date_to']|default:''}">
                            </div>
                            <div class="form-group">
                                <label>{Lang::T('Log Type')}:</label>
                                <select name="log_type" class="form-control">
                                    <option value="all" {if $filter['log_type']|default:'' == 'all'}selected{/if}>{Lang::T('All Logs')}</option>
                                    <option value="connection" {if $filter['log_type']|default:'' == 'connection'}selected{/if}>{Lang::T('Connection Logs')}</option>
                                    <option value="audit" {if $filter['log_type']|default:'' == 'audit'}selected{/if}>{Lang::T('Audit Logs')}</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="glyphicon glyphicon-search"></i> {Lang::T('Filter')}
                            </button>
                            <a href="{Text::url('')}routers/vpn-logs/{$router['id']}" class="btn btn-default">
                                <i class="glyphicon glyphicon-refresh"></i> {Lang::T('Reset')}
                            </a>
                            <a href="{Text::url('')}routers/export-vpn-logs/{$router['id']}" class="btn btn-success">
                                <i class="glyphicon glyphicon-download-alt"></i> {Lang::T('Export Logs')}
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Connection Logs -->
                {if !$filter['log_type'] || $filter['log_type'] == 'all' || $filter['log_type'] == 'connection'}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-transfer"></i> {Lang::T('Connection Logs')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th>{Lang::T('Timestamp')}</th>
                                        <th>{Lang::T('Status')}</th>
                                        <th>{Lang::T('VPN IP')}</th>
                                        <th>{Lang::T('Connection Time')}</th>
                                        <th>{Lang::T('Disconnection Time')}</th>
                                        <th>{Lang::T('Duration')}</th>
                                        <th>{Lang::T('Data Sent')}</th>
                                        <th>{Lang::T('Data Received')}</th>
                                        <th>{Lang::T('Details')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $connection_logs}
                                        {foreach $connection_logs as $log}
                                            <tr>
                                                <td>{$log['created_at']}</td>
                                                <td>
                                                    {if $log['connection_status'] == 'connected'}
                                                        <span class="badge badge-success">
                                                            <i class="glyphicon glyphicon-ok-circle"></i> {Lang::T('Connected')}
                                                        </span>
                                                    {elseif $log['connection_status'] == 'disconnected'}
                                                        <span class="badge badge-danger">
                                                            <i class="glyphicon glyphicon-remove-circle"></i> {Lang::T('Disconnected')}
                                                        </span>
                                                    {else}
                                                        <span class="badge badge-warning">
                                                            <i class="glyphicon glyphicon-exclamation-sign"></i> {Lang::T('Error')}
                                                        </span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['vpn_ip']}
                                                        <code>{$log['vpn_ip']}</code>
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['connection_time']}
                                                        {$log['connection_time']}
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['disconnection_time']}
                                                        {$log['disconnection_time']}
                                                    {else}
                                                        <span class="text-muted">{Lang::T('Still connected')}</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['connection_time'] && $log['disconnection_time']}
                                                        {assign var="conn_time" value=$log['connection_time']|strtotime}
                                                        {assign var="disconn_time" value=$log['disconnection_time']|strtotime}
                                                        {assign var="duration" value=$disconn_time - $conn_time}
                                                        {assign var="hours" value=($duration / 3600)|floor}
                                                        {assign var="minutes" value=(($duration % 3600) / 60)|floor}
                                                        {$hours}h {$minutes}m
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['bytes_sent'] > 0}
                                                        {if $log['bytes_sent'] > 1073741824}
                                                            {($log['bytes_sent'] / 1073741824)|string_format:"%.2f"} GB
                                                        {elseif $log['bytes_sent'] > 1048576}
                                                            {($log['bytes_sent'] / 1048576)|string_format:"%.2f"} MB
                                                        {elseif $log['bytes_sent'] > 1024}
                                                            {($log['bytes_sent'] / 1024)|string_format:"%.2f"} KB
                                                        {else}
                                                            {$log['bytes_sent']} B
                                                        {/if}
                                                    {else}
                                                        <span class="text-muted">0 B</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['bytes_received'] > 0}
                                                        {if $log['bytes_received'] > 1073741824}
                                                            {($log['bytes_received'] / 1073741824)|string_format:"%.2f"} GB
                                                        {elseif $log['bytes_received'] > 1048576}
                                                            {($log['bytes_received'] / 1048576)|string_format:"%.2f"} MB
                                                        {elseif $log['bytes_received'] > 1024}
                                                            {($log['bytes_received'] / 1024)|string_format:"%.2f"} KB
                                                        {else}
                                                            {$log['bytes_received']} B
                                                        {/if}
                                                    {else}
                                                        <span class="text-muted">0 B</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['error_details']}
                                                        <button type="button" class="btn btn-xs btn-warning" 
                                                                data-toggle="modal" data-target="#errorModal{$log['id']}">
                                                            <i class="glyphicon glyphicon-eye-open"></i> {Lang::T('View')}
                                                        </button>
                                                        
                                                        <!-- Error Details Modal -->
                                                        <div class="modal fade" id="errorModal{$log['id']}" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                        <h4 class="modal-title">{Lang::T('Error Details')}</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <pre>{$log['error_details']}</pre>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">{Lang::T('Close')}</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {else}
                                                        <span class="text-muted">-</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <p class="text-muted">{Lang::T('No connection logs found')}</p>
                                            </td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {/if}

                <!-- Audit Logs -->
                {if !$filter['log_type'] || $filter['log_type'] == 'all' || $filter['log_type'] == 'audit'}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-book"></i> {Lang::T('Audit Logs')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th>{Lang::T('Timestamp')}</th>
                                        <th>{Lang::T('Action')}</th>
                                        <th>{Lang::T('Admin')}</th>
                                        <th>{Lang::T('IP Address')}</th>
                                        <th>{Lang::T('Status')}</th>
                                        <th>{Lang::T('Details')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $audit_logs}
                                        {foreach $audit_logs as $log}
                                            <tr>
                                                <td>{$log['created_at']}</td>
                                                <td>
                                                    <strong>{$log['action']}</strong>
                                                </td>
                                                <td>
                                                    {if $log['admin_username']}
                                                        {$log['admin_username']}
                                                    {else}
                                                        <span class="text-muted">{Lang::T('System')}</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['ip_address']}
                                                        <code>{$log['ip_address']}</code>
                                                    {else}
                                                        <span class="text-muted">N/A</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['status'] == 'success'}
                                                        <span class="badge badge-success">
                                                            <i class="glyphicon glyphicon-ok"></i> {Lang::T('Success')}
                                                        </span>
                                                    {elseif $log['status'] == 'failed'}
                                                        <span class="badge badge-danger">
                                                            <i class="glyphicon glyphicon-remove"></i> {Lang::T('Failed')}
                                                        </span>
                                                    {else}
                                                        <span class="badge badge-warning">
                                                            <i class="glyphicon glyphicon-time"></i> {Lang::T('Pending')}
                                                        </span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $log['details'] || $log['error_message']}
                                                        <button type="button" class="btn btn-xs btn-info" 
                                                                data-toggle="modal" data-target="#auditModal{$log['id']}">
                                                            <i class="glyphicon glyphicon-eye-open"></i> {Lang::T('View')}
                                                        </button>
                                                        
                                                        <!-- Audit Details Modal -->
                                                        <div class="modal fade" id="auditModal{$log['id']}" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                        <h4 class="modal-title">{Lang::T('Audit Details')}</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        {if $log['details']}
                                                                            <h5>{Lang::T('Details')}:</h5>
                                                                            <pre>{$log['details']}</pre>
                                                                        {/if}
                                                                        {if $log['error_message']}
                                                                            <h5>{Lang::T('Error Message')}:</h5>
                                                                            <div class="alert alert-danger">
                                                                                {$log['error_message']}
                                                                            </div>
                                                                        {/if}
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">{Lang::T('Close')}</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {else}
                                                        <span class="text-muted">-</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <p class="text-muted">{Lang::T('No audit logs found')}</p>
                                            </td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {/if}

                <!-- Info Box -->
                <div class="bs-callout bs-callout-info">
                    <h4><i class="glyphicon glyphicon-info-sign"></i> {Lang::T('About VPN Logs')}</h4>
                    <p><strong>{Lang::T('Connection Logs')}:</strong> {Lang::T('Track VPN connection events, including connection/disconnection times, data transfer statistics, and error details.')}</p>
                    <p><strong>{Lang::T('Audit Logs')}:</strong> {Lang::T('Record all administrative actions performed on this router, including configuration changes, certificate renewals, and user modifications.')}</p>
                </div>

            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}
