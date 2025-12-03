{include file="sections/header.tpl"}
<!-- routers-add -->

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="panel panel-primary panel-hovered panel-stacked mb30">
            <div class="panel-heading">{Lang::T('Add Router')}</div>
            <div class="panel-body">

                <form class="form-horizontal" method="post" role="form" action="{Text::url('')}routers/add-post" id="routerForm">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Status')}</label>
                        <div class="col-md-10">
                            <label class="radio-inline warning">
                                <input type="radio" checked name="enabled" value="1"> {Lang::T('Enable')}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="enabled" value="0"> {Lang::T('Disable')}
                            </label>
                        </div>
                    </div>
                    
                    <!-- Connection Type Selection -->
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Router Location Type')}</label>
                        <div class="col-md-10">
                            <label class="radio-inline">
                                <input type="radio" checked name="connection_type" value="local" id="type_local" onchange="toggleConnectionType()"> 
                                <i class="glyphicon glyphicon-home"></i> {Lang::T('Local Network')}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="connection_type" value="remote" id="type_remote" onchange="toggleConnectionType()"> 
                                <i class="glyphicon glyphicon-cloud"></i> {Lang::T('Remote Location')} <span class="label label-info">VPN</span>
                            </label>
                            <p class="help-block">{Lang::T('Select Local for routers on the same network, or Remote for routers that will connect via OpenVPN')}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Router Name / Location')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="name" name="name" maxlength="32" required>
                            <p class="help-block">{Lang::T('Name of Area that router operated')}</p>
                        </div>
                    </div>

                    <!-- Local Router Fields -->
                    <div id="local_fields">
                        <div class="form-group">
                            <label class="col-md-2 control-label">{Lang::T('IP Address')}</label>
                            <div class="col-md-6">
                                <input type="text" placeholder="192.168.88.1:8728" class="form-control" id="ip_address"
                                    name="ip_address">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">{Lang::T('Username')}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">{Lang::T('Router Secret')}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="password" name="password"
                                onmouseleave="this.type = 'password'" onmouseenter="this.type = 'text'">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">{Lang::T('Description')}</label>
                            <div class="col-md-6">
                                <textarea class="form-control" id="description" name="description"></textarea>
                                <p class="help-block">{Lang::T('Explain Coverage of router')}</p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-2 control-label"></label>
                            <div class="col-md-6">
                                <label><input type="checkbox" checked name="testIt" value="yes"> {Lang::T('Test Connection')}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Remote Router Fields -->
                    <div id="remote_fields" style="display: none;">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-lock"></i> {Lang::T('OpenVPN Credentials')}</h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('VPN Username')}</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="vpn_username" name="vpn_username" 
                                            pattern="[a-zA-Z0-9_-]{literal}{3,32}{/literal}" 
                                            placeholder="router-vpn-001">
                                        <p class="help-block">{Lang::T('3-32 characters: letters, numbers, underscore, hyphen')}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('VPN Password')}</label>
                                    <div class="col-md-6">
                                        <input type="password" class="form-control" id="vpn_password" name="vpn_password" 
                                            minlength="8" onkeyup="checkPasswordStrength()">
                                        <div id="password_strength" class="help-block"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('Confirm VPN Password')}</label>
                                    <div class="col-md-6">
                                        <input type="password" class="form-control" id="vpn_password_confirm" name="vpn_password_confirm" 
                                            minlength="8" onkeyup="checkPasswordMatch()">
                                        <div id="password_match" class="help-block"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-certificate"></i> {Lang::T('Certificate Settings')}</h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('Certificate Validity')}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="cert_validity_days" name="cert_validity_days" 
                                                value="365" min="30" max="3650">
                                            <span class="input-group-addon">{Lang::T('days')}</span>
                                        </div>
                                        <p class="help-block">{Lang::T('Certificate will be valid for this many days (30-3650)')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title"><i class="glyphicon glyphicon-cog"></i> {Lang::T('MikroTik API Credentials')}</h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('API Username')}</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="api_username" name="api_username" 
                                            value="admin">
                                        <p class="help-block">{Lang::T('MikroTik API username for remote management')}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('API Password')}</label>
                                    <div class="col-md-6">
                                        <input type="password" class="form-control" id="api_password" name="api_password"
                                            onmouseleave="this.type = 'password'" onmouseenter="this.type = 'text'">
                                        <p class="help-block">{Lang::T('Password for MikroTik API access')}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">{Lang::T('API Port')}</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" id="api_port" name="api_port" 
                                            value="8728" min="1" max="65535">
                                        <p class="help-block">{Lang::T('MikroTik API port (default: 8728)')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">{Lang::T('Description')}</label>
                            <div class="col-md-6">
                                <textarea class="form-control" id="description_remote" name="description"></textarea>
                                <p class="help-block">{Lang::T('Explain Coverage of router')}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary" id="submitBtn" onclick="return validateForm()" type="submit">
                                <i class="glyphicon glyphicon-save"></i> {Lang::T('Save')}
                            </button>
                            Or <a href="{Text::url('')}routers/list">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{literal}
<script>
function toggleConnectionType() {
    var isRemote = document.getElementById('type_remote').checked;
    var localFields = document.getElementById('local_fields');
    var remoteFields = document.getElementById('remote_fields');
    
    if (isRemote) {
        localFields.style.display = 'none';
        remoteFields.style.display = 'block';
        
        // Disable local fields
        document.getElementById('ip_address').removeAttribute('required');
        document.getElementById('username').removeAttribute('required');
        document.getElementById('password').removeAttribute('required');
        
        // Enable remote fields
        document.getElementById('vpn_username').setAttribute('required', 'required');
        document.getElementById('vpn_password').setAttribute('required', 'required');
        document.getElementById('vpn_password_confirm').setAttribute('required', 'required');
        document.getElementById('api_username').setAttribute('required', 'required');
        document.getElementById('api_password').setAttribute('required', 'required');
        
        // Update form action
        document.getElementById('routerForm').action = '{/literal}{Text::url('')}routers/add-remote-post{literal}';
    } else {
        localFields.style.display = 'block';
        remoteFields.style.display = 'none';
        
        // Enable local fields
        document.getElementById('ip_address').setAttribute('required', 'required');
        document.getElementById('username').setAttribute('required', 'required');
        document.getElementById('password').setAttribute('required', 'required');
        
        // Disable remote fields
        document.getElementById('vpn_username').removeAttribute('required');
        document.getElementById('vpn_password').removeAttribute('required');
        document.getElementById('vpn_password_confirm').removeAttribute('required');
        document.getElementById('api_username').removeAttribute('required');
        document.getElementById('api_password').removeAttribute('required');
        
        // Update form action
        document.getElementById('routerForm').action = '{/literal}{Text::url('')}routers/add-post{literal}';
    }
}

function checkPasswordStrength() {
    var password = document.getElementById('vpn_password').value;
    var strengthDiv = document.getElementById('password_strength');
    
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    var strength = 0;
    var feedback = [];
    
    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');
    
    if (/[a-z]/.test(password)) strength++;
    else feedback.push('lowercase letter');
    
    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('uppercase letter');
    
    if (/[0-9]/.test(password)) strength++;
    else feedback.push('number');
    
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    var strengthText = '';
    var strengthClass = '';
    
    if (strength < 3) {
        strengthText = '<span class="text-danger"><strong>Weak:</strong> Password must contain ' + feedback.join(', ') + '</span>';
    } else if (strength === 3) {
        strengthText = '<span class="text-warning"><strong>Medium:</strong> Consider adding special characters</span>';
    } else {
        strengthText = '<span class="text-success"><strong>Strong:</strong> Password meets all requirements</span>';
    }
    
    strengthDiv.innerHTML = strengthText;
}

function checkPasswordMatch() {
    var password = document.getElementById('vpn_password').value;
    var confirm = document.getElementById('vpn_password_confirm').value;
    var matchDiv = document.getElementById('password_match');
    
    if (confirm.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirm) {
        matchDiv.innerHTML = '<span class="text-success"><i class="glyphicon glyphicon-ok"></i> Passwords match</span>';
    } else {
        matchDiv.innerHTML = '<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> Passwords do not match</span>';
    }
}

function validateForm() {
    var isRemote = document.getElementById('type_remote').checked;
    
    if (isRemote) {
        var password = document.getElementById('vpn_password').value;
        var confirm = document.getElementById('vpn_password_confirm').value;
        
        // Check password strength
        if (password.length < 8) {
            alert('VPN password must be at least 8 characters long');
            return false;
        }
        
        if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
            alert('VPN password must contain uppercase, lowercase, and number');
            return false;
        }
        
        // Check password match
        if (password !== confirm) {
            alert('VPN passwords do not match');
            return false;
        }
        
        return ask(document.getElementById('submitBtn'), 'Continue creating remote router with VPN configuration?');
    } else {
        return ask(document.getElementById('submitBtn'), '{/literal}{Lang::T("Continue the process of adding Routers?")}{literal}');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleConnectionType();
});
</script>
{/literal}

{include file="sections/footer.tpl"}
