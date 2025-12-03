<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-clock-o"></i> {Lang::T('Customers Expired, Today')}</h3>
    </div>
    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            <select class="form-control input-sm" onchange="changeExpiredDefault(this)">
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
                        <th>{Lang::T('Created / Expired')}</th>
                        <th>{Lang::T('Internet Package')}</th>
                        <th>{Lang::T('Location')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $expire as $expired}
                        {assign var="rem_exp" value="{$expired['expiration']} {$expired['time']}"}
                        {assign var="rem_started" value="{$expired['recharged_on']} {$expired['recharged_time']}"}
                        <tr>
                            <td>
                                <a href="{Text::url('customers/view/',$expired['id'])}">
                                    {if $cookie['expdef'] == 'fullname'}
                                        {$expired['fullname']}
                                    {elseif $cookie['expdef'] == 'phone'}
                                        {$expired['phonenumber']}
                                    {elseif $cookie['expdef'] == 'email'}
                                        {$expired['email']}
                                    {else}
                                        {$expired['username']}
                                    {/if}
                                </a>
                            </td>
                            <td>
                                <small class="text-muted" data-toggle="tooltip" data-placement="top"
                                    title="{Lang::dateAndTimeFormat($expired['recharged_on'],$expired['recharged_time'])}">{Lang::timeElapsed($rem_started)}</small>
                                <span class="text-muted">/</span>
                                <span class="badge badge-warning" data-toggle="tooltip" data-placement="top"
                                    title="{Lang::dateAndTimeFormat($expired['expiration'],$expired['time'])}">{Lang::timeElapsed($rem_exp)}</span>
                            </td>
                            <td>{$expired['namebp']}</td>
                            <td><small class="text-muted">{$expired['routers']}</small></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer">
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