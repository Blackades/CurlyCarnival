{include file="customer/header.tpl"}

<!-- Order View -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card {if $trx['status']==1}border-warning{elseif $trx['status']==2}border-success{elseif $trx['status']==3}border-danger{elseif $trx['status']==4}border-danger{else}border-primary{/if}">
                <div class="card-header {if $trx['status']==1}bg-warning{elseif $trx['status']==2}bg-success{elseif $trx['status']==3}bg-danger{elseif $trx['status']==4}bg-danger{else}bg-primary{/if} text-white">
                    <h3 class="card-title mb-0">{Lang::T('Transaction')} #{$trx['id']}</h3>
                </div>
                
                {if !in_array($trx['routers'],['balance','radius'])}
                    <div class="card-body border-bottom">
                        <div class="alert alert-info alert-modern router-info-alert">
                            <div class="alert-content alert-body">
                                <div class="alert-icon-wrapper">
                                    <i class="alert-icon fa fa-info-circle" aria-hidden="true"></i>
                                </div>
                                <div class="alert-message alert-text-content">
                                    <h5 class="alert-heading alert-title">{$router['name']}</h5>
                                    <p class="alert-description mb-0">{$router['description']}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                
                <div class="card-body">
                    {if $trx['pg_url_payment']=='balance'}
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Type')}</td>
                                        <td>{$trx['plan_name']}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Paid Date')}</td>
                                        <td>{date($_c['date_format'], strtotime($trx['paid_date']))} {date('H:i', strtotime($trx['paid_date']))}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">
                                            {if $trx['plan_name'] == 'Receive Balance'}
                                                {Lang::T('From')}
                                            {else}
                                                {Lang::T('To')}
                                            {/if}
                                        </td>
                                        <td>{$trx['gateway']}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Total')}</td>
                                        <td class="fw-bold text-primary fs-5">{Lang::moneyFormat($trx['price'])}</td>
                                    </tr>
                                    {if $invoice['note']}
                                        <tr>
                                            <td class="fw-medium">{Lang::T('Notes')}</td>
                                            <td>{nl2br($invoice['note'])}</td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Status')}</td>
                                        <td>
                                            {if $trx['status']==1}<span class="badge badge-warning">{Lang::T('UNPAID')}</span>
                                            {elseif $trx['status']==2}<span class="badge badge-success">{Lang::T('PAID')}</span>
                                            {elseif $trx['status']==3}<span class="badge badge-danger">{Lang::T('FAILED')}</span>
                                            {elseif $trx['status']==4}<span class="badge badge-danger">{Lang::T('CANCELED')}</span>
                                            {else}<span class="badge badge-secondary">{Lang::T('UNKNOWN')}</span>{/if}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('expired')}</td>
                                        <td>{date($_c['date_format'], strtotime($trx['expired_date']))} {date('H:i', strtotime($trx['expired_date']))}</td>
                                    </tr>
                                    {if $trx['status']==2}
                                        <tr>
                                            <td class="fw-medium">{Lang::T('Paid Date')}</td>
                                            <td>{date($_c['date_format'], strtotime($trx['paid_date']))} {date('H:i', strtotime($trx['paid_date']))}</td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Package Name')}</td>
                                        <td class="fw-medium">{$plan['name_plan']}</td>
                                    </tr>
                                    {if $add_cost!=0}
                                        {foreach $bills as $k => $v}
                                            <tr>
                                                <td class="fw-medium">{$k}</td>
                                                <td>{Lang::moneyFormat($v)}</td>
                                            </tr>
                                        {/foreach}
                                        <tr>
                                            <td class="fw-medium">{Lang::T('Additional Cost')}</td>
                                            <td>{Lang::moneyFormat($add_cost)}</td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <td class="fw-medium">
                                            {Lang::T('Package Price')}
                                            {if $add_cost!=0}<small class="text-muted"> + {Lang::T('Additional Cost')}</small>{/if}
                                        </td>
                                        <td class="fw-bold text-primary fs-4">{Lang::moneyFormat($trx['price'])}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">{Lang::T('Type')}</td>
                                        <td>{$plan['type']}</td>
                                    </tr>
                                    {if $plan['type']!='Balance'}
                                        {if $plan['type'] eq 'Hotspot'}
                                            <tr>
                                                <td class="fw-medium">{Lang::T('Plan_Type')}</td>
                                                <td>{Lang::T($plan['typebp'])}</td>
                                            </tr>
                                            {if $plan['typebp'] eq 'Limited'}
                                                {if $plan['limit_type'] eq 'Time_Limit' or $plan['limit_type'] eq 'Both_Limit'}
                                                    <tr>
                                                        <td class="fw-medium">{Lang::T('Time_Limit')}</td>
                                                        <td>{$ds['time_limit']} {$ds['time_unit']}</td>
                                                    </tr>
                                                {/if}
                                                {if $plan['limit_type'] eq 'Data_Limit' or $plan['limit_type'] eq 'Both_Limit'}
                                                    <tr>
                                                        <td class="fw-medium">{Lang::T('Data_Limit')}</td>
                                                        <td>{$ds['data_limit']} {$ds['data_unit']}</td>
                                                    </tr>
                                                {/if}
                                            {/if}
                                        {/if}
                                        <tr>
                                            <td class="fw-medium">{Lang::T('Validity Periode')}</td>
                                            <td>{$plan['validity']} {$plan['validity_unit']}</td>
                                        </tr>
                                        {if $_c['show_bandwidth_plan'] == 'yes'}
                                            <tr>
                                                <td class="fw-medium">{Lang::T('Bandwidth Plans')}</td>
                                                <td>
                                                    <div>{$bandw['name_bw']}</div>
                                                    <small class="text-muted">{$bandw['rate_down']}{$bandw['rate_down_unit']}/{$bandw['rate_up']}{$bandw['rate_up_unit']}</small>
                                                </td>
                                            </tr>
                                        {/if}
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                    {/if}
                </div>
                
                {if $trx['status']==1}
                    <div class="card-footer">
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="{$trx['pg_url_payment']}" {if $trx['gateway']=='midtrans'} target="_blank" {/if}
                                class="btn btn-primary btn-lg">{Lang::T('Pay Now')}</a>
                            <a href="{Text::url('order/view/', $trx['id'], '/check')}"
                                class="btn btn-info btn-lg">{Lang::T('Check for Payment')}</a>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{Text::url('order/view/', $trx['id'], '/cancel')}" class="btn btn-outline-danger btn-sm"
                                onclick="return ask(this, '{Lang::T('Cancel it?')}')">{Lang::T('Cancel')}</a>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

{if $trx['status']==1 && $trx['gateway']=='mpesastk'}
<script>
// Auto-refresh for M-Pesa STK Push payments
(function() {
    var checkCount = 0;
    var maxChecks = 24; // Check for 2 minutes (24 * 5 seconds)
    var checkInterval = 5000; // 5 seconds
    var timeoutReached = false;
    
    function checkPaymentStatus() {
        checkCount++;
        
        if (checkCount > maxChecks) {
            timeoutReached = true;
            showTimeoutMessage();
            return;
        }
        
        // Simple page reload to check status
        window.location.reload();
    }
    
    function showTimeoutMessage() {
        var messageDiv = document.querySelector('.mpesa-waiting-message');
        if (messageDiv) {
            messageDiv.className = 'alert alert-warning alert-modern payment-timeout-alert';
            messageDiv.innerHTML = '<div class="alert-content alert-body">' +
                                  '<div class="alert-icon-wrapper">' +
                                  '<i class="alert-icon fa fa-exclamation-triangle" aria-hidden="true"></i>' +
                                  '</div>' +
                                  '<div class="alert-message alert-text-content">' +
                                  '<span class="alert-text">{Lang::T("Payment confirmation timeout.")}</span> ' +
                                  '<a href="{Text::url("order/view/", $trx["id"], "/check")}" class="btn btn-sm btn-info alert-action-btn">' +
                                  '<i class="btn-icon fa fa-refresh" aria-hidden="true"></i>' +
                                  '<span class="btn-text">{Lang::T("Check Payment Status")}</span>' +
                                  '</a>' +
                                  '</div>' +
                                  '</div>';
        }
    }
    
    // Show a message to the user
    var panelBody = document.querySelector('.panel-warning .table-responsive');
    if (panelBody && panelBody.parentNode) {
        var messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-info alert-modern mpesa-waiting-message payment-waiting-alert';
        messageDiv.style.margin = '15px';
        messageDiv.innerHTML = '<div class="alert-content alert-body">' +
                              '<div class="alert-icon-wrapper">' +
                              '<i class="alert-icon fa fa-spinner fa-spin" aria-hidden="true"></i>' +
                              '</div>' +
                              '<div class="alert-message alert-text-content">' +
                              '<span class="alert-text">{Lang::T("Waiting for payment confirmation...")} {Lang::T("This page will update automatically.")}</span>' +
                              '</div>' +
                              '</div>';
        panelBody.parentNode.insertBefore(messageDiv, panelBody);
    }
    
    // Start checking after 5 seconds
    setTimeout(checkPaymentStatus, checkInterval);
})();
</script>
{elseif $trx['status']==2 && $trx['gateway']=='mpesastk'}
<script>
// Auto-redirect to dashboard after successful payment
(function() {
    var countdown = 5;
    var messageDiv = document.querySelector('.panel-success .table-responsive');
    
    if (messageDiv && messageDiv.parentNode) {
        var successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success alert-modern payment-success-alert';
        successDiv.style.margin = '15px';
        successDiv.innerHTML = '<div class="alert-content alert-body">' +
                              '<div class="alert-icon-wrapper">' +
                              '<i class="alert-icon fa fa-check-circle" aria-hidden="true"></i>' +
                              '</div>' +
                              '<div class="alert-message alert-text-content">' +
                              '<strong class="alert-title">{Lang::T("Payment Successful!")}</strong> ' +
                              '<span class="alert-text">{Lang::T("Redirecting to dashboard in")} <span id="countdown">5</span> {Lang::T("seconds...")}</span>' +
                              '</div>' +
                              '</div>';
        messageDiv.parentNode.insertBefore(successDiv, messageDiv);
        
        var countdownSpan = document.getElementById('countdown');
        var interval = setInterval(function() {
            countdown--;
            if (countdownSpan) {
                countdownSpan.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(interval);
                // Force hard refresh by adding timestamp to URL and using location.replace
                window.location.replace('{$_url}home?t=' + new Date().getTime());
            }
        }, 1000);
    }
})();
</script>
{/if}

{include file="customer/footer.tpl"}