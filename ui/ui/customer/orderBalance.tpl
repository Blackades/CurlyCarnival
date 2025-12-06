{include file="customer/header.tpl"}

<!-- Balance Package Order -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {if $_c['enable_balance'] == 'yes'}
                <div class="page-header mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h1 class="page-title">{Lang::T('Buy Balance Package')}</h1>
                        </div>
                    </div>
                </div>
                
                <div class="balance-plans-container">
                    <div class="row">
                        {foreach $plans_balance as $plan}
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card plan-card h-100">
                                    <div class="card-header text-center">
                                        <h3 class="card-title mb-0">{$plan['name_plan']}</h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="price-display mb-3">
                                            <div class="price-amount">
                                                {Lang::moneyFormat($plan['price'])}
                                                {if !empty($plan['price_old'])}
                                                    <small class="text-muted text-decoration-line-through ms-2">{Lang::moneyFormat($plan['price_old'])}</small>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="{Text::url('order/gateway/0/')}{$plan['id']}"
                                            onclick="return ask(this, '{Lang::T('Buy Balance')}?')"
                                            class="btn btn-primary btn-block">{Lang::T('Buy')}</a>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        
                        {if $_c['allow_balance_custom'] eq 'yes'}
                            <div class="col-lg-4 col-md-6 mb-4">
                                <form action="{Text::url('order/gateway/0/0')}" method="post">
                                    <input type="hidden" name="custom" value="1">
                                    <div class="card plan-card h-100 custom-balance-card">
                                        <div class="card-header text-center">
                                            <h3 class="card-title mb-0">{Lang::T('Custom Balance')}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="amount" class="form-label">{Lang::T('Amount')}</label>
                                                <input type="number" name="amount" id="amount" class="form-control"
                                                    placeholder="{Lang::T('Input Desired Amount')}" required>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" onclick="return ask(this, '{Lang::T('Buy Balance')}?')"
                                                class="btn btn-primary btn-block">{Lang::T('Buy')}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>
{include file="customer/footer.tpl"}