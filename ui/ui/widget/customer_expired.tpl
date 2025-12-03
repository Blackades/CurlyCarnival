<div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #faad14 0%, #ffc53d 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-clock-o" style="color: white; font-size: 18px;"></i>
        </div>
        <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #262626;">{Lang::T('Customers Expired, Today')}</h3>
    </div>
    <div class="table-responsive">
        <table class="table" style="margin-bottom: 0;">
            <thead>
                <tr style="background: #fafafa;">
                    <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0;">
                        <select class="form-control" style="border: 1px solid #d9d9d9; border-radius: 4px; height: 32px; padding: 4px 8px; font-size: 13px;"
                            onchange="changeExpiredDefault(this)">
                            <option value="username" {if $cookie['expdef'] == 'username'}selected{/if}>
                                {Lang::T('Username')}
                            </option>
                            <option value="fullname" {if $cookie['expdef'] == 'fullname'}selected{/if}>
                                {Lang::T('Full Name')}</option>
                            <option value="phone" {if $cookie['expdef'] == 'phone'}selected{/if}>{Lang::T('Phone')}
                            </option>
                            <option value="email" {if $cookie['expdef'] == 'email'}selected{/if}>{Lang::T('Email')}
                            </option>
                        </select>
                    </th>
                    <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0;">{Lang::T('Created / Expired')}</th>
                    <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0;">{Lang::T('Internet Package')}</th>
                    <th style="padding: 12px 16px; font-weight: 500; color: #595959; font-size: 13px; border-bottom: 1px solid #f0f0f0;">{Lang::T('Location')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $expire as $expired}
                    {assign var="rem_exp" value="{$expired['expiration']} {$expired['time']}"}
                    {assign var="rem_started" value="{$expired['recharged_on']} {$expired['recharged_time']}"}
                    <tr style="border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                        <td style="padding: 12px 16px;"><a href="{Text::url('customers/view/',$expired['id'])}" style="color: #1890ff; text-decoration: none; font-weight: 500;">
                                {if $cookie['expdef'] == 'fullname'}
                                    {$expired['fullname']}
                                {elseif $cookie['expdef'] == 'phone'}
                                    {$expired['phonenumber']}
                                {elseif $cookie['expdef'] == 'email'}
                                    {$expired['email']}
                                {else}
                                    {$expired['username']}
                                {/if}
                            </a></td>
                        <td style="padding: 12px 16px; font-size: 13px; color: #595959;"><small data-toggle="tooltip" data-placement="top"
                                title="{Lang::dateAndTimeFormat($expired['recharged_on'],$expired['recharged_time'])}">{Lang::timeElapsed($rem_started)}</small>
                            <span style="color: #8c8c8c; margin: 0 4px;">/</span>
                            <span data-toggle="tooltip" data-placement="top"
                                title="{Lang::dateAndTimeFormat($expired['expiration'],$expired['time'])}">{Lang::timeElapsed($rem_exp)}</span>
                        </td>
                        <td style="padding: 12px 16px; font-size: 13px; color: #262626;">{$expired['namebp']}</td>
                        <td style="padding: 12px 16px; font-size: 13px; color: #595959;">{$expired['routers']}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div style="padding: 16px 24px; border-top: 1px solid #f0f0f0;">
        {include file="pagination.tpl"}
    </div>
</div>
<script>
    function changeExpiredDefault(fl) {
        setCookie('expdef', fl.value, 365);
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
</script>