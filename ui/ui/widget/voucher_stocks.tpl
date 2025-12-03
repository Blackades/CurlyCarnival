{if $_c['disable_voucher'] != 'yes' && $stocks['unused']>0 || $stocks['used']>0}
    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #1890ff 0%, #40a9ff 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-ticket" style="color: white; font-size: 18px;"></i>
            </div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #262626;">Vouchers Stock</h3>
        </div>
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead>
                    <tr style="background: #fafafa;">
                        <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0;">{Lang::T('Package Name')}</th>
                        <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0; text-align: center;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #e6f7ff; padding: 4px 10px; border-radius: 12px; color: #1890ff;">
                                <i class="fa fa-circle-o" style="font-size: 10px;"></i>
                                Unused
                            </span>
                        </th>
                        <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0; text-align: center;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #f6ffed; padding: 4px 10px; border-radius: 12px; color: #52c41a;">
                                <i class="fa fa-check-circle-o" style="font-size: 10px;"></i>
                                Used
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $plans as $stok}
                        <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 12px 16px; font-size: 13px; color: #262626; font-weight: 500;">{$stok['name_plan']}</td>
                            <td style="padding: 12px 16px; font-size: 14px; color: #1890ff; text-align: center; font-weight: 600;">{$stok['unused']}</td>
                            <td style="padding: 12px 16px; font-size: 14px; color: #52c41a; text-align: center; font-weight: 600;">{$stok['used']}</td>
                        </tr>
                    {/foreach}
                    <tr style="background: #fafafa; border-top: 2px solid #f0f0f0;">
                        <td style="padding: 14px 16px; font-size: 14px; color: #262626; font-weight: 600;">Total</td>
                        <td style="padding: 14px 16px; font-size: 16px; color: #1890ff; text-align: center; font-weight: 700;">{$stocks['unused']}</td>
                        <td style="padding: 14px 16px; font-size: 16px; color: #52c41a; text-align: center; font-weight: 700;">{$stocks['used']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
{/if}