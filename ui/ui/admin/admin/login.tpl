<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{Lang::T('Login')} - {$_c['CompanyName']}</title>
    <link rel="shortcut icon" href="{$app_url}/ui/ui/images/logo.png" type="image/x-icon" />

    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/bootstrap.min.css">
    <link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill-modern.css?v=1.0.0" />
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo mb-4">
            {$_c['CompanyName']}
        </div>
        <div class="login-box-body shadow-lg rounded-lg p-xl">
            <p class="login-box-msg text-lg font-weight-bold mb-4">{Lang::T('Enter Admin Area')}</p>
            {if isset($notify)}
                {$notify}
            {/if}
            <form action="{Text::url('admin/post')}" method="post">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <div class="form-group has-feedback mb-3">
                    <input type="text" required class="form-control" name="username" placeholder="{Lang::T('Username')}">
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback mb-4">
                    <input type="password" required class="form-control" name="password" placeholder="{Lang::T('Password')}">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-flat btn-lg shadow-md hover-shadow-lg">{Lang::T('Login')}</button>
                <a href="{Text::url('login')}" class="back-link d-block text-center mt-3 text-secondary">{Lang::T('Go Back')}</a>
            </form>
        </div>
    </div>
</body>

</html>
