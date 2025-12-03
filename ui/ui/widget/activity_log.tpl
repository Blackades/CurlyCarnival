<div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #13c2c2 0%, #36cfc9 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-history" style="color: white; font-size: 18px;"></i>
            </div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #262626;">{Lang::T('Activity Log')}</h3>
        </div>
        <a href="{Text::url('logs')}" style="color: #1890ff; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center;">
            {Lang::T('View All')} <i class="fa fa-arrow-right" style="margin-left: 6px; font-size: 11px;"></i>
        </a>
    </div>
    <div style="padding: 16px 24px;">
        <ul style="list-style: none; padding: 0; margin: 0;">
            {foreach $dlog as $dl}
                <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; position: relative; padding-left: 24px;">
                    <span style="position: absolute; left: 0; top: 18px; width: 8px; height: 8px; background: #1890ff; border-radius: 50%; border: 2px solid #e6f7ff;"></span>
                    <span style="font-size: 12px; color: #8c8c8c; display: block; margin-bottom: 4px;">{Lang::timeElapsed($dl['date'],true)}</span>
                    <p style="margin: 0; font-size: 13px; color: #262626; line-height: 1.5;">{$dl['description']}</p>
                </li>
            {/foreach}
        </ul>
    </div>
</div>