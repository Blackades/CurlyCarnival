{include file="customer/header-public.tpl"}

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{Lang::T('Announcement')}</h4>
                </div>
                <div class="card-body">
                    {$Announcement = "{$PAGES_PATH}/Announcement.html"}
                    {if file_exists($Announcement)}
                        {include file=$Announcement}
                    {/if}
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{Lang::T('Log in to Member Panel')}</h4>
                </div>
                <div class="card-body">
                    <form action="{Text::url('login/post')}" method="post">
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        
                        <div class="mb-3">
                            <label class="form-label" for="login-username">
                                {if $_c['registration_username'] == 'phone'}
                                    {Lang::T('Phone Number')}
                                {elseif $_c['registration_username'] == 'email'}
                                    {Lang::T('Email')}
                                {else}
                                    {Lang::T('Usernames')}
                                {/if}
                            </label>
                            <div class="input-group">
                                {if $_c['registration_username'] == 'phone'}
                                    <span class="input-group-text" id="username-addon"><i class="fa fa-phone" aria-hidden="true"></i></span>
                                {elseif $_c['registration_username'] == 'email'}
                                    <span class="input-group-text" id="username-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                                {else}
                                    <span class="input-group-text" id="username-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
                                {/if}
                                <input type="text" class="form-control" id="login-username" name="username" required
                                    placeholder="{if $_c['country_code_phone']!= '' || $_c['registration_username'] == 'phone'}{$_c['country_code_phone']} {Lang::T('Phone Number')}{elseif $_c['registration_username'] == 'email'}{Lang::T('Email')}{else}{Lang::T('Usernames')}{/if}"
                                    aria-describedby="username-addon login-username-error" aria-required="true">
                                <div id="login-username-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label" for="login-password">{Lang::T('Password')}</label>
                            <div class="input-group">
                                <span class="input-group-text" id="password-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                                <input type="password" class="form-control" id="login-password" name="password" required
                                    placeholder="{Lang::T('Password')}"
                                    aria-describedby="password-addon login-password-error" aria-required="true">
                                <div id="login-password-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            {if $_c['disable_registration'] != 'noreg'}
                                <div class="row">
                                    <div class="col-6">
                                        <a href="{Text::url('register')}" class="btn btn-success w-100">{Lang::T('Register')}</a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary w-100">{Lang::T('Login')}</button>
                                    </div>
                                </div>
                            {else}
                                <button type="submit" class="btn btn-primary">{Lang::T('Login')}</button>
                            {/if}
                        </div>
                        
                        <div class="text-center">
                            <a href="{Text::url('forgot')}" class="btn btn-link">{Lang::T('Forgot Password')}</a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="javascript:showPrivacy()" class="text-decoration-none">Privacy</a>
                                &bull;
                                <a href="javascript:showTaC()" class="text-decoration-none">T &amp; C</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="HTMLModal" tabindex="-1" role="dialog" aria-labelledby="HTMLModalTitle" aria-hidden="true" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="HTMLModalTitle">Information</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close dialog">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="HTMLModal_konten" role="document"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
        </div>
    </div>
</div>

{include file="customer/footer-public.tpl"}