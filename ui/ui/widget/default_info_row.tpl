<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <i class="fa fa-calendar text-muted"></i>
                <span class="text-muted">{Lang::dateFormat($start_date)}</span>
            </div>
            <div class="col-sm-4">
                <i class="fa fa-calendar-check-o text-muted"></i>
                <span class="text-muted">{Lang::dateFormat($current_date)}</span>
            </div>
            {if $_c['enable_balance'] == 'yes' && in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
                <div class="col-sm-4">
                    <a href="{Text::url('customers&search=&order=balance&filter=Active&orderby=desc')}" class="btn btn-info btn-sm">
                        <i class="fa fa-money"></i>
                        {Lang::T('Customer Balance')}
                        <span class="badge badge-light">
                            {$_c['currency_code']} {number_format($cb,0,$_c['dec_point'],$_c['thousands_sep'])}
                        </span>
                    </a>
                </div>
            {/if}
        </div>
    </div>
</div>