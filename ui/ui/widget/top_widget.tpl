<div class="row" style="margin-bottom: 24px;">
    {if in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
        <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="ion ion-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('Income Today')}</span>
                    <span class="info-box-number">
                        <small>{$_c['currency_code']}</small>
                        {number_format($iday,0,$_c['dec_point'],$_c['thousands_sep'])}
                    </span>
                    <a href="{Text::url('reports/by-date')}" class="info-box-link">
                        {Lang::T('View Details')} <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="ion ion-android-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{Lang::T('Income This Month')}</span>
                    <span class="info-box-number">
                        <small>{$_c['currency_code']}</small>
                        {number_format($imonth,0,$_c['dec_point'],$_c['thousands_sep'])}
                    </span>
                    <a href="{Text::url('reports/by-period')}" class="info-box-link">
                        {Lang::T('View Details')} <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    {/if}
    <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="ion ion-person"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{Lang::T('Active')}/{Lang::T('Expired')}</span>
                <span class="info-box-number">{$u_act}/{$u_all-$u_act}</span>
                <a href="{Text::url('plan/list')}" class="info-box-link">
                    {Lang::T('View Details')} <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="ion ion-android-people"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{Lang::T('Customers')}</span>
                <span class="info-box-number">{$c_all}</span>
                <a href="{Text::url('customers/list')}" class="info-box-link">
                    {Lang::T('View Details')} <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>