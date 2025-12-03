{include file="sections/header.tpl"}
<!-- router-config-summary -->

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-success panel-hovered panel-stacked mb30">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-ok-circle"></i> {Lang::T('Remote Router Configuration Complete')}
            </div>
            <div class="panel-body">
                <div class="alert alert-success">
                    <h4><i class="glyphicon glyphicon-ok"></i> {Lang::T('Success!')}</h4>
                    <p>{Lang::T('Your remote router configuration has been created successfully. Download the configuration package and follow the setup instructions below.')}</p>
                </div>

                <!-- OpenVPN Credentials -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-lock"></i> {Lang::T('OpenVPN Credentials')}</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>{Lang::T('Username')}:</strong></td>
                                        <td>{$router['vpn_username']}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Password')}:</strong></td>
                                        <td>••••••••••••</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Status')}:</strong></td>
                                        <td><span class="label label-success">{Lang::T('Active')}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Certificates -->
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-certificate"></i> {Lang::T('Certificates')}</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>{Lang::T('CA Certificate')}:</strong></td>
                                        <td><i class="glyphicon glyphicon-ok text-success"></i> {Lang::T('Valid')} ({$cert_days_remaining} {Lang::T('days')})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Client Certificate')}:</strong></td>
                                        <td><i class="glyphicon glyphicon-ok text-success"></i> {Lang::T('Generated')}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Expiry Date')}:</strong></td>
                                        <td>{$router['certificate_expiry']}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OpenVPN Service -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-cloud"></i> {Lang::T('OpenVPN Service')}</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td width="25%"><strong>{Lang::T('Status')}:</strong></td>
                                        <td><span class="label label-success">{Lang::T('Running')}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Server')}:</strong></td>
                                        <td>{$vpn_server_ip}:{$vpn_server_port}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Protocol')}:</strong></td>
                                        <td>TCP</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{Lang::T('Expected VPN IP')}:</strong></td>
                                        <td><code>{$expected_vpn_ip}</code></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Download Configuration Package -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-download-alt"></i> {Lang::T('Download Configuration Package')}</h4>
                    </div>
                    <div class="panel-body text-center">
                        <a href="{Text::url('')}routers/download-config/{$router['id']}" class="btn btn-primary btn-lg">
                            <i class="glyphicon glyphicon-compressed"></i> {Lang::T('Download All Files')} (.zip)
                        </a>
                        
                        <div class="btn-group" style="margin-left: 10px;">
                            <button type="button" class="btn btn-default btn-lg dropdown-toggle" data-toggle="dropdown">
                                {Lang::T('Individual Files')} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="{Text::url('')}routers/download-file/{$router['id']}/ovpn"><i class="glyphicon glyphicon-file"></i> {$router['vpn_username']}.ovpn</a></li>
                                <li><a href="{Text::url('')}routers/download-file/{$router['id']}/ca"><i class="glyphicon glyphicon-certificate"></i> ca.crt</a></li>
                                <li><a href="{Text::url('')}routers/download-file/{$router['id']}/cert"><i class="glyphicon glyphicon-certificate"></i> {$router['vpn_username']}.crt</a></li>
                                <li><a href="{Text::url('')}routers/download-file/{$router['id']}/key"><i class="glyphicon glyphicon-lock"></i> {$router['vpn_username']}.key</a></li>
                                <li><a href="{Text::url('')}routers/download-file/{$router['id']}/script"><i class="glyphicon glyphicon-console"></i> mikrotik-setup.rsc</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Setup Instructions -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="glyphicon glyphicon-book"></i> {Lang::T('Setup Instructions')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group" id="accordion">
                            <!-- Step 1 -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#step1">
                                            <i class="glyphicon glyphicon-chevron-right"></i> {Lang::T('Step 1: Upload Certificates to MikroTik')}
                                        </a>
                                    </h4>
                                </div>
                                <div id="step1" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <ol>
                                            <li>{Lang::T('Open WinBox or WebFig and connect to your MikroTik router')}</li>
                                            <li>{Lang::T('Navigate to')} <strong>Files</strong> {Lang::T('menu')}</li>
                                            <li>{Lang::T('Drag and drop the following files')}:
                                                <ul>
                                                    <li><code>ca.crt</code></li>
                                                    <li><code>{$router['vpn_username']}.crt</code></li>
                                                    <li><code>{$router['vpn_username']}.key</code></li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#step2" class="collapsed">
                                            <i class="glyphicon glyphicon-chevron-right"></i> {Lang::T('Step 2: Import and Configure OpenVPN')}
                                        </a>
                                    </h4>
                                </div>
                                <div id="step2" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <ol>
                                            <li>{Lang::T('Open')} <strong>Terminal</strong> {Lang::T('in WinBox')}</li>
                                            <li>{Lang::T('Open the')} <code>mikrotik-setup.rsc</code> {Lang::T('file in a text editor')}</li>
                                            <li>{Lang::T('Copy all the commands')}</li>
                                            <li>{Lang::T('Paste into the Terminal and press Enter')}</li>
                                            <li>{Lang::T('Wait for all commands to execute')}</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#step3" class="collapsed">
                                            <i class="glyphicon glyphicon-chevron-right"></i> {Lang::T('Step 3: Verify Connection')}
                                        </a>
                                    </h4>
                                </div>
                                <div id="step3" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <ol>
                                            <li>{Lang::T('In Terminal, run')}: <code>/interface ovpn-client print</code></li>
                                            <li>{Lang::T('Look for status')}: <strong>"connected"</strong></li>
                                            <li>{Lang::T('Note the assigned VPN IP address (typically 10.8.0.x)')}</li>
                                            <li>{Lang::T('Test connectivity')}: <code>/ping {$vpn_server_ip}</code></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4 -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordion" href="#step4" class="collapsed">
                                            <i class="glyphicon glyphicon-chevron-right"></i> {Lang::T('Step 4: Update Router in phpnuxbill')}
                                        </a>
                                    </h4>
                                </div>
                                <div id="step4" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <p>{Lang::T('Once the VPN connection is established, update this router configuration')}:</p>
                                        <ol>
                                            <li>{Lang::T('Note the VPN IP address from Step 3')}</li>
                                            <li>{Lang::T('Click the button below to update router settings')}</li>
                                            <li>{Lang::T('Enter the VPN IP address')}</li>
                                            <li>{Lang::T('Test the connection')}</li>
                                        </ol>
                                        <a href="{Text::url('')}routers/edit/{$router['id']}" class="btn btn-primary">
                                            <i class="glyphicon glyphicon-edit"></i> {Lang::T('Update Router Settings')}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Action Buttons -->
                <div class="text-center">
                    <a href="{Text::url('')}routers/test-vpn-connection/{$router['id']}" class="btn btn-info btn-lg">
                        <i class="glyphicon glyphicon-flash"></i> {Lang::T('Test VPN Connection')}
                    </a>
                    <a href="{Text::url('')}routers/list" class="btn btn-default btn-lg">
                        <i class="glyphicon glyphicon-list"></i> {Lang::T('Back to Router List')}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
$(document).ready(function() {
    // Toggle chevron icon on collapse
    $('.panel-group').on('show.bs.collapse', function(e) {
        $(e.target).prev('.panel-heading').find('.glyphicon').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
    });
    $('.panel-group').on('hide.bs.collapse', function(e) {
        $(e.target).prev('.panel-heading').find('.glyphicon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
    });
});
</script>
{/literal}

{include file="sections/footer.tpl"}