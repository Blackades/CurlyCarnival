{include file="customer/header.tpl"}

<!-- Customer Dashboard -->
<div class="dashboard-container">
    <div class="dashboard-widgets">

        {function showWidget pos=0}
            {foreach $widgets as $w}
                {if $w['position'] == $pos}
                    <div class="widget-wrapper">
                        {$w['content']}
                    </div>
                {/if}
            {/foreach}
        {/function}

        {assign rows explode(".", $_c['dashboard_Customer'])}
        {assign pos 1}
        
        {foreach $rows as $cols}
            {if $cols == 12}
                <div class="dashboard-row full-width-row">
                    <div class="dashboard-col col-full">
                        {showWidget widgets=$widgets pos=$pos}
                    </div>
                </div>
                {assign pos value=$pos+1}
            {else}
                {assign colss explode(",", $cols)}
                <div class="dashboard-row multi-col-row">
                    {foreach $colss as $c}
                        <div class="dashboard-col col-{$c}">
                            <div class="widget-container">
                                {showWidget widgets=$widgets pos=$pos}
                            </div>
                        </div>
                        {assign pos value=$pos+1}
                    {/foreach}
                </div>
            {/if}
        {/foreach}
        
    </div>
</div>


{if isset($hostname) && $hchap == 'true' && $_c['hs_auth_method'] == 'hchap'}
    <script type="text/javascript" src="/ui/ui/scripts/md5.js"></script>
    <script type="text/javascript">
        var hostname = "http://{$hostname}/login";
        var user = "{$_user['username']}";
        var pass = "{$_user['password']}";
        var dst = "{$apkurl}";
        var authdly = "2";
        var key = hexMD5('{$key1}' + pass + '{$key2}');
        var auth = hostname + '?username=' + user + '&dst=' + dst + '&password=' + key;
        document.write('<meta http-equiv="refresh" target="_blank" content="' + authdly + '; url=' + auth + '">');
    </script>
{/if}
{include file="customer/footer.tpl"}