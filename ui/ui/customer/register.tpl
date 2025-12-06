{include file="customer/header-public.tpl"}

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">{Lang::T('Registration Info')}</h4>
                </div>
                <div class="card-body">
                    {include file="$_path/../pages/Registration_Info.html"}
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <form enctype="multipart/form-data" action="{Text::url('register/post')}" method="post">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title mb-0">1. {Lang::T('Register as Member')}</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">
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
                                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                        {elseif $_c['registration_username'] == 'email'}
                                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        {else}
                                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                                        {/if}
                                        <input type="text" class="form-control" name="username" required
                                            placeholder="{if $_c['country_code_phone']!= '' || $_c['registration_username'] == 'phone'}{$_c['country_code_phone']} {Lang::T('Phone Number')}{elseif $_c['registration_username'] == 'email'}{Lang::T('Email')}{else}{Lang::T('Usernames')}{/if}">
                                    </div>
                                </div>
                                
                                {if $_c['photo_register'] == 'yes'}
                                    <div class="mb-3">
                                        <label class="form-label">{Lang::T('Photo')}</label>
                                        <input type="file" required class="form-control" id="photo" name="photo" accept="image/*">
                                    </div>
                                {/if}
                                
                                <div class="mb-3">
                                    <label class="form-label">{Lang::T('Full Name')}</label>
                                    <input type="text" {if $_c['man_fields_fname'] neq 'no'}required{/if} class="form-control"
                                        id="fullname" value="{$fullname}" name="fullname">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">{Lang::T('Email')}</label>
                                    <input type="email" {if $_c['man_fields_email'] neq 'no'}required{/if} class="form-control"
                                        id="email" placeholder="user@example.com" value="{$email}" name="email">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">{Lang::T('Home Address')}</label>
                                    <textarea {if $_c['man_fields_address'] neq 'no'}required{/if} name="address"
                                        id="address" class="form-control" rows="3">{$address}</textarea>
                                </div>
                                
                                {$customFields}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title mb-0">2. {Lang::T('Password')}</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">{Lang::T('Password')}</label>
                                    <input type="password" required class="form-control" id="password" name="password">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">{Lang::T('Confirm Password')}</label>
                                    <input type="password" required class="form-control" id="cpassword" name="cpassword">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" type="submit">{Lang::T('Register')}</button>
                                    <a href="{Text::url('login')}" class="btn btn-outline-warning">{Lang::T('Cancel')}</a>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <a href="javascript:showPrivacy()" class="text-decoration-none">Privacy</a>
                                        &bull;
                                        <a href="javascript:showTaC()" class="text-decoration-none">T &amp; C</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{include file="customer/footer-public.tpl"}