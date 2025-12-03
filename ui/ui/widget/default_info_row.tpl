<div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 16px 24px; margin-bottom: 24px; display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fa fa-calendar" style="color: #8c8c8c; font-size: 14px;"></i>
        <span style="font-size: 13px; color: #595959;">{Lang::dateFormat($start_date)}</span>
    </div>
    <div style="width: 1px; height: 20px; background: #f0f0f0;"></div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fa fa-calendar-check-o" style="color: #8c8c8c; font-size: 14px;"></i>
        <span style="font-size: 13px; color: #595959;">{Lang::dateFormat($current_date)}</span>
    </div>
    {if $_c['enable_balance'] == 'yes' && in_array($_admin['user_type'],['SuperAdmin','Admin', 'Report'])}
        <div style="width: 1px; height: 20px; background: #f0f0f0;"></div>
        <div onclick="window.location.href = '{Text::url('customers&search=&order=balance&filter=Active&orderby=desc')}'" style="cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 6px 12px; background: #e6f7ff; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#bae7ff'" onmouseout="this.style.background='#e6f7ff'">
            <i class="fa fa-money" style="color: #1890ff; font-size: 14px;"></i>
            <span style="font-size: 13px; color: #595959;">{Lang::T('Customer Balance')}</span>
            <span style="font-size: 16px; font-weight: 600; color: #1890ff; margin-left: 4px;">
                <span style="font-size: 12px; font-weight: 500;">{$_c['currency_code']}</span>
                {number_format($cb,0,$_c['dec_point'],$_c['thousands_sep'])}
            </span>
        </div>
    {/if}
</div>