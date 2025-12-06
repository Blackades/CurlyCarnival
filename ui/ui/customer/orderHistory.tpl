{include file="customer/header.tpl"}

<!-- Order History -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title mb-0">{Lang::T('Order History')}</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{Lang::T('Package Name')}</th>
                                    <th>{Lang::T('Payment Method')}</th>
                                    <th>Routers</th>
                                    <th>{Lang::T('Type')}</th>
                                    <th>{Lang::T('Package Price')}</th>
                                    <th>{Lang::T('Created on')}</th>
                                    <th>{Lang::T('Expires on')}</th>
                                    <th>{Lang::T('Date')}</th>
                                    <th>{Lang::T('Status')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $d as $ds}
                                    <tr>
                                        <td>
                                            <a href="{Text::url('order/view/')}{$ds['id']}" class="text-decoration-none fw-medium">{$ds['plan_name']}</a>
                                        </td>
                                        <td>{$ds['gateway']}</td>
                                        <td>{$ds['routers']}</td>
                                        <td>{$ds['payment_channel']}</td>
                                        <td class="fw-medium">{Lang::moneyFormat($ds['price'])}</td>
                                        <td class="text-info">{date("{$_c['date_format']} H:i", strtotime($ds['created_date']))}</td>
                                        <td class="text-warning">{date("{$_c['date_format']} H:i", strtotime($ds['expired_date']))}</td>
                                        <td class="text-success">{if $ds['status']!=1}{date("{$_c['date_format']} H:i", strtotime($ds['paid_date']))}{/if}</td>
                                        <td>
                                            {if $ds['status']==1}<span class="badge badge-warning">{Lang::T('UNPAID')}</span>
                                            {elseif $ds['status']==2}<span class="badge badge-success">{Lang::T('PAID')}</span>
                                            {elseif $ds['status']==3}<span class="badge badge-danger">{$_L['FAILED']}</span>
                                            {elseif $ds['status']==4}<span class="badge badge-danger">{Lang::T('CANCELED')}</span>
                                            {elseif $ds['status']==5}<span class="badge badge-default">{Lang::T('UNKNOWN')}</span>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    
                    {if isset($pagination)}
                        <div class="d-flex justify-content-center mt-4">
                            {include file="pagination.tpl"}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>


{include file="customer/footer.tpl"}