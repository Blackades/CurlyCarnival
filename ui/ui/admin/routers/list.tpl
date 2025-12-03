{include file="sections/header.tpl"}
<!-- routers -->

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-hovered mb20 panel-primary">
            <div class="panel-heading">{Lang::T('Routers')}
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" title="save" href="{Text::url('')}routers/maps">
                        <span class="glyphicon glyphicon-map-marker"></span></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="md-whiteframe-z1 mb20 text-center" style="padding: 15px">
                    <div class="col-md-8">

                        <form id="site-search" method="post" action="{Text::url('')}routers/list/">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <span class="fa fa-search"></span>
                                </div>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{Lang::T('Search by Name')}...">
                                <div class="input-group-btn">
                                    <button class="btn btn-success" type="submit">{Lang::T('Search')}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <a href="{Text::url('')}routers/add" class="btn btn-primary btn-block"><i
                                class="ion ion-android-add">
                            </i> {Lang::T('New Router')}</a>
                    </div>&nbsp;
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>{Lang::T('Router Name')}</th>
                                <th>{Lang::T('Type')}</th>
                                <th>{Lang::T('IP Address')}</th>
                                <th>{Lang::T('Username')}</th>
                                <th>{Lang::T('Description')}</th>
                                {if $_c['router_check']}
                                    <th>{Lang::T('Online Status')}</th>
                                    <th>{Lang::T('Last Seen')}</th>
                                {/if}
                                <th>{Lang::T('VPN Status')}</th>
                                <th>{Lang::T('Certificate')}</th>
                                <th>{Lang::T('Last Check')}</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Manage')}</th>
                                <th>ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $d as $ds}
                                <tr {if $ds['enabled'] !=1}class="danger" title="disabled" {/if}>
                                    <td>
                                        {if $ds['coordinates']}
                                            <a href="https://www.google.com/maps/dir//{$ds['coordinates']}/" target="_blank"
                                                class="btn btn-default btn-xs" title="{$ds['coordinates']}"><i
                                                    class="glyphicon glyphicon-map-marker"></i></a>
                                        {/if}
                                        {$ds['name']}
                                    </td>
                                    <td>
                                        {if $ds['connection_type'] == 'remote'}
                                            <span class="badge badge-info"><i class="glyphicon glyphicon-cloud"></i> {Lang::T('Remote')}</span>
                                        {else}
                                            <span class="badge badge-default"><i class="glyphicon glyphicon-hdd"></i> {Lang::T('Local')}</span>
                                        {/if}
                                    </td>
                                    <td style="background-color: black; color: black;"
                                        onmouseleave="this.style.backgroundColor = 'black';"
                                        onmouseenter="this.style.backgroundColor = 'white';">
                                        {if $ds['connection_type'] == 'remote'}
                                            {if $ds['vpn_ip']}{$ds['vpn_ip']}{else}{Lang::T('Pending')}{/if}
                                        {else}
                                            {$ds['ip_address']}
                                        {/if}
                                    </td>
                                    <td style="background-color: black; color: black;"
                                        onmouseleave="this.style.backgroundColor = 'black';"
                                        onmouseenter="this.style.backgroundColor = 'white';">{$ds['username']}</td>
                                    <td>{$ds['description']}</td>
                                    {if $_c['router_check']}
                                        <td>
                                            {if $ds['status'] == 'Online'}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-success"></span>
                                                    <span class="badge badge-success">{Lang::T('Online')}</span>
                                                </span>
                                            {else}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-danger"></span>
                                                    <span class="badge badge-danger">{Lang::T('Offline')}</span>
                                                </span>
                                            {/if}
                                        </td>
                                        <td>{$ds['last_seen']}</td>
                                    {/if}
                                    <td>
                                        {if $ds['connection_type'] == 'remote'}
                                            {if $ds['ovpn_status'] == 'connected'}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-success"></span>
                                                    <span class="badge badge-success"><i class="glyphicon glyphicon-ok-circle"></i> {Lang::T('Connected')}</span>
                                                </span>
                                            {elseif $ds['ovpn_status'] == 'disconnected'}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-danger"></span>
                                                    <span class="badge badge-danger"><i class="glyphicon glyphicon-remove-circle"></i> {Lang::T('Disconnected')}</span>
                                                </span>
                                            {elseif $ds['ovpn_status'] == 'error'}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-warning"></span>
                                                    <span class="badge badge-warning"><i class="glyphicon glyphicon-exclamation-sign"></i> {Lang::T('Error')}</span>
                                                </span>
                                            {else}
                                                <span class="status-indicator">
                                                    <span class="status-dot status-dot-default"></span>
                                                    <span class="badge badge-default"><i class="glyphicon glyphicon-time"></i> {Lang::T('Pending')}</span>
                                                </span>
                                            {/if}
                                        {else}
                                            <span class="text-muted">N/A</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $ds['connection_type'] == 'remote' && $ds['certificate_expiry']}
                                            {assign var="cert_date" value=$ds['certificate_expiry']|strtotime}
                                            {assign var="now" value=$smarty.now}
                                            {assign var="days_left" value=(($cert_date - $now) / 86400)|floor}
                                            {if $days_left < 7}
                                                <span class="badge badge-danger"><i class="glyphicon glyphicon-warning-sign"></i> {$days_left} {Lang::T('days')}</span>
                                            {elseif $days_left < 30}
                                                <span class="badge badge-warning"><i class="glyphicon glyphicon-time"></i> {$days_left} {Lang::T('days')}</span>
                                            {else}
                                                <span class="badge badge-success"><i class="glyphicon glyphicon-ok"></i> {$days_left} {Lang::T('days')}</span>
                                            {/if}
                                        {else}
                                            <span class="text-muted">N/A</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $ds['connection_type'] == 'remote'}
                                            {if $ds['last_vpn_check']}
                                                {$ds['last_vpn_check']}
                                            {else}
                                                <span class="text-muted">{Lang::T('Never')}</span>
                                            {/if}
                                        {else}
                                            <span class="text-muted">N/A</span>
                                        {/if}
                                    </td>
                                    <td>{if $ds['enabled'] == 1}{Lang::T('Enabled')}{else}{Lang::T('Disabled')}{/if}</td>
                                    <td class="table-actions">
                                        <a href="{Text::url('')}routers/edit/{$ds['id']}"
                                            class="btn btn-info btn-xs">{Lang::T('Edit')}</a>
                                        {if $ds['connection_type'] == 'remote'}
                                            <a href="{Text::url('')}routers/vpn-logs/{$ds['id']}"
                                                class="btn btn-warning btn-xs btn-icon" title="{Lang::T('VPN Logs')}"><i class="glyphicon glyphicon-list-alt"></i></a>
                                        {/if}
                                        <a href="{Text::url('')}routers/delete/{$ds['id']}" id="{$ds['id']}"
                                            onclick="return ask(this, '{Lang::T('Delete')}?')"
                                            class="btn btn-danger btn-xs btn-icon"><i class="glyphicon glyphicon-trash"></i></a>
                                    </td>
                                    <td>{$ds['id']}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                {include file="pagination.tpl"}
                <div class="bs-callout bs-callout-info" id="callout-navbar-role">
                    <h4>{Lang::T('Check if Router Online?')}</h4>
                    <p>{Lang::T('To check whether the Router is online or not, please visit the following page')} <a
                            href="{Text::url('')}settings/miscellaneous#router_check" target="_blank"
                            class="btn btn-link">{Lang::T('Cek Now')}</a></p>
                </div>
            </div>
        </div>
    </div>
</div>


{include file="sections/footer.tpl"}