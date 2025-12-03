<div class="row" style="margin-bottom: 24px;">
    {if in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
        <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
            <div class="modern-stat-card" style="background: linear-gradient(135deg, #1890ff 0%, #40a9ff 100%); border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; right: 20px; top: 20px; opacity: 0.2; font-size: 48px; color: white;">
                    <i class="ion ion-clock"></i>
                </div>
                <div style="position: relative; z-index: 1;">
                    <div style="color: rgba(255,255,255,0.9); font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                        {Lang::T('Income Today')}
                    </div>
                    <div style="color: white; font-size: 28px; font-weight: 600; margin-bottom: 12px;">
                        <span style="font-size: 16px; font-weight: 500;">{$_c['currency_code']}</span>
                        {number_format($iday,0,$_c['dec_point'],$_c['thousands_sep'])}
                    </div>
                    <a href="{Text::url('reports/by-date')}" style="color: white; font-size: 13px; text-decoration: none; opacity: 0.9; display: inline-flex; align-items: center;">
                        {Lang::T('View Details')} <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 11px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
            <div class="modern-stat-card" style="background: linear-gradient(135deg, #52c41a 0%, #73d13d 100%); border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; right: 20px; top: 20px; opacity: 0.2; font-size: 48px; color: white;">
                    <i class="ion ion-android-calendar"></i>
                </div>
                <div style="position: relative; z-index: 1;">
                    <div style="color: rgba(255,255,255,0.9); font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                        {Lang::T('Income This Month')}
                    </div>
                    <div style="color: white; font-size: 28px; font-weight: 600; margin-bottom: 12px;">
                        <span style="font-size: 16px; font-weight: 500;">{$_c['currency_code']}</span>
                        {number_format($imonth,0,$_c['dec_point'],$_c['thousands_sep'])}
                    </div>
                    <a href="{Text::url('reports/by-period')}" style="color: white; font-size: 13px; text-decoration: none; opacity: 0.9; display: inline-flex; align-items: center;">
                        {Lang::T('View Details')} <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 11px;"></i>
                    </a>
                </div>
            </div>
        </div>
    {/if}
    <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
        <div class="modern-stat-card" style="background: linear-gradient(135deg, #faad14 0%, #ffc53d 100%); border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
            <div style="position: absolute; right: 20px; top: 20px; opacity: 0.2; font-size: 48px; color: white;">
                <i class="ion ion-person"></i>
            </div>
            <div style="position: relative; z-index: 1;">
                <div style="color: rgba(255,255,255,0.9); font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                    {Lang::T('Active')}/{Lang::T('Expired')}
                </div>
                <div style="color: white; font-size: 28px; font-weight: 600; margin-bottom: 12px;">
                    {$u_act}/{$u_all-$u_act}
                </div>
                <a href="{Text::url('plan/list')}" style="color: white; font-size: 13px; text-decoration: none; opacity: 0.9; display: inline-flex; align-items: center;">
                    {Lang::T('View Details')} <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 11px;"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6" style="margin-bottom: 16px;">
        <div class="modern-stat-card" style="background: linear-gradient(135deg, #f5222d 0%, #ff4d4f 100%); border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
            <div style="position: absolute; right: 20px; top: 20px; opacity: 0.2; font-size: 48px; color: white;">
                <i class="ion ion-android-people"></i>
            </div>
            <div style="position: relative; z-index: 1;">
                <div style="color: rgba(255,255,255,0.9); font-size: 13px; font-weight: 500; margin-bottom: 8px;">
                    {Lang::T('Customers')}
                </div>
                <div style="color: white; font-size: 28px; font-weight: 600; margin-bottom: 12px;">
                    {$c_all}
                </div>
                <a href="{Text::url('customers/list')}" style="color: white; font-size: 13px; text-decoration: none; opacity: 0.9; display: inline-flex; align-items: center;">
                    {Lang::T('View Details')} <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 11px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>