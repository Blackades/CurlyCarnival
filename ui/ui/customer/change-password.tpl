{include file="customer/header.tpl"}
<!-- user-change-password -->

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">{Lang::T('Change Password')}</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{Text::url('accounts/change-password-post')}">
                        <input type="hidden" name="csrf_token" value="{$csrf_token|escape:'html'}">
                        
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">{Lang::T('Current Password')}</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">{Lang::T('New Password')}</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" id="npass" name="npass" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">{Lang::T('Confirm New Password')}</label>
                            <div class="col-md-8">
                                <input type="password" class="form-control" id="cnpass" name="cnpass" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 offset-md-4">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success" type="submit">{Lang::T('Save Changes')}</button>
                                    <a href="{Text::url('home')}" class="btn btn-outline-secondary">{Lang::T('Cancel')}</a>
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
