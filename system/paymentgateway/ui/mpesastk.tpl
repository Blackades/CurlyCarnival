{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" role="form" action="{$_url}paymentgateway/mpesastk">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">{Lang::T('M-Pesa STK Push - Payment Gateway')}</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Consumer Key</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesastk_consumer_key" name="mpesastk_consumer_key"
                                value="{$mpesastk_consumer_key}">
                            <a href="https://developer.safaricom.co.ke/MyApps" target="_blank"
                                class="help-block">Get your credentials from Safaricom Daraja Portal</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Consumer Secret</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="mpesastk_consumer_secret" name="mpesastk_consumer_secret"
                                value="{$mpesastk_consumer_secret}">
                            <a href="https://developer.safaricom.co.ke/MyApps" target="_blank"
                                class="help-block">Get your credentials from Safaricom Daraja Portal</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Business Shortcode</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesastk_business_shortcode" name="mpesastk_business_shortcode"
                                value="{$mpesastk_business_shortcode}">
                            <small class="form-text text-muted">Your M-Pesa Paybill or Till Number</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Passkey</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="mpesastk_passkey" name="mpesastk_passkey"
                                value="{$mpesastk_passkey}">
                            <small class="form-text text-muted">Lipa Na M-Pesa Online Passkey from Daraja Portal</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Environment</label>
                        <div class="col-md-6">
                            <select class="form-control" name="mpesastk_environment">
                                <option value="sandbox" {if $mpesastk_environment == 'sandbox'}selected{/if}>Sandbox (Testing)</option>
                                <option value="production" {if $mpesastk_environment == 'production'}selected{/if}>Production (Live)</option>
                            </select>
                            <small class="form-text text-muted">Use Sandbox for testing, Production for live transactions</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Account Reference</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesastk_account_reference" name="mpesastk_account_reference"
                                value="{$mpesastk_account_reference}">
                            <small class="form-text text-muted">Account reference shown to customer (default: PHPNuxBill)</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Transaction Description</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="mpesastk_transaction_desc" name="mpesastk_transaction_desc"
                                value="{$mpesastk_transaction_desc}">
                            <small class="form-text text-muted">Description shown to customer (default: Payment for Internet Access)</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Callback URL</label>
                        <div class="col-md-6">
                            <input type="text" readonly class="form-control" onclick="this.select()"
                                value="{$mpesastk_callback_url}">
                            <small class="form-text text-muted">Register this URL in your Daraja Portal under Lipa Na M-Pesa Online</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary waves-effect waves-light"
                                type="submit">{Lang::T('Save Changes')}</button>
                        </div>
                    </div>

                    <div class="bs-callout bs-callout-info" id="callout-mpesastk-mikrotik">
                        <h4>Mikrotik Walled Garden Configuration</h4>
                        <pre>/ip hotspot walled-garden
add dst-host=safaricom.co.ke
add dst-host=*.safaricom.co.ke</pre>
                        <small class="form-text text-muted">Add these rules to allow M-Pesa API access through your hotspot</small>
                    </div>

                    <div class="bs-callout bs-callout-warning" id="callout-mpesastk-telegram">
                        <h4>Important Reminders</h4>
                        <ul>
                            <li><strong>STK Push:</strong> This gateway automatically sends payment prompts to customer phones</li>
                            <li><strong>Testing:</strong> Always test in Sandbox mode before switching to Production</li>
                            <li><strong>Callback URL:</strong> Ensure the callback URL is registered in your Daraja Portal</li>
                            <li><strong>Phone Numbers:</strong> Customer phone numbers must be valid Kenyan numbers (07XX, 01XX, or 254XXX format)</li>
                            <li><strong>Timeout:</strong> STK Push requests timeout after 60 seconds if customer doesn't respond</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

{include file="sections/footer.tpl"}
