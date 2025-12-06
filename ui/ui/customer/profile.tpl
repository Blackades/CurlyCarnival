{include file="customer/header.tpl"}
<!-- user-profile -->

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">{Lang::T('Data Change')}</h3>
                </div>
                <div class="card-body">
                    <form enctype="multipart/form-data" method="post" action="{Text::url('accounts/edit-profile-post')}">
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        <input type="hidden" name="id" value="{$_user['id']}">
                        
                        <div class="text-center mb-4">
                            <img src="{$app_url}/{$UPLOAD_PATH}{$_user['photo']}.thumb.jpg" width="150" height="150"
                                onerror="this.src='{$app_url}/{$UPLOAD_PATH}/user.default.jpg'"
                                class="rounded-circle border" alt="Profile Photo" onclick="return deletePhoto({$d['id']})">
                        </div>
                        
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">{Lang::T('Photo')}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" name="photo" accept="image/*">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" checked name="faceDetect" value="yes" id="faceDetect">
                                    <label class="form-check-label" for="faceDetect" title="Not always Working">
                                        {Lang::T('Face Detect')}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">{Lang::T('Usernames')}</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    {if $_c['registration_username'] == 'phone'}
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                    {elseif $_c['registration_username'] == 'email'}
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    {else}
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    {/if}
                                    <input type="text" class="form-control" name="username" id="username" readonly
                                        value="{$_user['username']}"
                                        placeholder="{if $_c['country_code_phone']!= '' || $_c['registration_username'] == 'phone'}{$_c['country_code_phone']} {Lang::T('Phone Number')}{elseif $_c['registration_username'] == 'email'}{Lang::T('Email')}{else}{Lang::T('Username')}{/if}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">{Lang::T('Full Name')}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    value="{$_user['fullname']}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">{Lang::T('Home Address')}</label>
                            <div class="col-md-9">
                                <textarea name="address" id="address" class="form-control" rows="3">{$_user['address']}</textarea>
                            </div>
                        </div>
                        {if $_c['allow_phone_otp'] != 'yes'}
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label">{Lang::T('Phone Number')}</label>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                        <input type="text" class="form-control" name="phonenumber" id="phonenumber"
                                            value="{$_user['phonenumber']}"
                                            placeholder="{if $_c['country_code_phone']!= ''}{$_c['country_code_phone']}{/if} {Lang::T('Phone Number')}">
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label">{Lang::T('Phone Number')}</label>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                        <input type="text" class="form-control" name="phonenumber" id="phonenumber"
                                            value="{$_user['phonenumber']}" readonly
                                            placeholder="{if $_c['country_code_phone']!= ''}{$_c['country_code_phone']}{/if} {Lang::T('Phone Number')}">
                                        <a href="{Text::url('accounts/phone-update')}" class="btn btn-outline-info">
                                            {Lang::T('Change')}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        
                        {if $_c['allow_email_otp'] != 'yes'}
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label">{Lang::T('Email Address')}</label>
                                <div class="col-md-9">
                                    <input type="email" class="form-control" id="email" name="email" value="{$_user['email']}">
                                </div>
                            </div>
                        {else}
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label">{Lang::T('Email Address')}</label>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" id="email"
                                            value="{$_user['email']}" readonly>
                                        <a href="{Text::url('accounts/email-update')}" class="btn btn-outline-info">
                                            {Lang::T('Change')}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        
                        {$customFields}
                        
                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" type="submit">
                                        {Lang::T('Save Changes')}
                                    </button>
                                    <a href="{Text::url('home')}" class="btn btn-outline-secondary">
                                        {Lang::T('Cancel')}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="customer/footer.tpl"}