{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" role="form" action="{$_url}paymentgateway/mpesa">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">{Lang::T('M-Pesa - Payment Gateway')}</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Consumer Key</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesa_consumer_key" name="mpesa_consumer_key"
                                value="{$_c['mpesa_consumer_key']}">
                            <a href="https://developer.safaricom.co.ke/MyApps" target="_blank"
                                class="help-block">Get your credentials from Safaricom Daraja Portal</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Consumer Secret</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="mpesa_consumer_secret" name="mpesa_consumer_secret"
                                value="{$_c['mpesa_consumer_secret']}">
                            <a href="https://developer.safaricom.co.ke/MyApps" target="_blank"
                                class="help-block">Get your credentials from Safaricom Daraja Portal</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Business Shortcode</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesa_shortcode" name="mpesa_shortcode"
                                value="{$_c['mpesa_shortcode']}">
                            <small class="form-text text-muted">Your M-Pesa Paybill or Till Number</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Passkey</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="mpesa_passkey" name="mpesa_passkey"
                                value="{$_c['mpesa_passkey']}">
                            <small class="form-text text-muted">Lipa Na M-Pesa Online Passkey from Daraja Portal</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Environment</label>
                        <div class="col-md-6">
                            <select class="form-control" name="mpesa_environment">
                                <option value="sandbox" {if $_c['mpesa_environment'] == 'sandbox'}selected{/if}>Sandbox (Testing)</option>
                                <option value="production" {if $_c['mpesa_environment'] == 'production'}selected{/if}>Production (Live)</option>
                            </select>
                            <small class="form-text text-muted">Use Sandbox for testing, Production for live transactions</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Callback URL</label>
                        <div class="col-md-6">
                            <input type="text" readonly class="form-control" onclick="this.select()"
                                value="{$callback_url}">
                            <small class="form-text text-muted">Register this URL in your Daraja Portal under Lipa Na M-Pesa Online</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary waves-effect waves-light"
                                type="submit">{Lang::T('Save Changes')}</button>
                        </div>
                    </div>

                    <div class="bs-callout bs-callout-info" id="callout-mpesa-mikrotik">
                        <h4>Mikrotik Walled Garden Configuration</h4>
                        <pre>/ip hotspot walled-garden
add dst-host=safaricom.co.ke
add dst-host=*.safaricom.co.ke</pre>
                        <small class="form-text text-muted">Add these rules to allow M-Pesa API access through your hotspot</small>
                    </div>

                    <div class="bs-callout bs-callout-warning" id="callout-mpesa-telegram">
                        <h4>Important Reminders</h4>
                        <ul>
                            <li><strong>Telegram Bot:</strong> Set up Telegram Bot to receive error notifications and payment confirmations</li>
                            <li><strong>Testing:</strong> Always test in Sandbox mode before switching to Production</li>
                            <li><strong>Callback URL:</strong> Ensure the callback URL is registered in your Daraja Portal</li>
                            <li><strong>Phone Numbers:</strong> Customer phone numbers must be valid Kenyan numbers (07XX, 01XX, or 254XXX format)</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

{include file="sections/footer.tpl"}
