{include file="sections/header.tpl"}

<!-- Add Customer Form Container -->
<div class="form-container add-customer-container">
    <form class="form-horizontal customer-form add-form" method="post" role="form" 
          action="{Text::url('customers/add-post')}" novalidate>
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        
        <!-- Form Content Row -->
        <div class="form-row row">
            <!-- Main Customer Information -->
            <div class="form-col col-md-6">
                <div class="card form-card panel panel-primary panel-hovered panel-stacked mb30">
                    <!-- Card Header -->
                    <div class="card-header form-header panel-heading">
                        <h3 class="card-title form-title">
                            <i class="form-icon fa fa-user-plus" aria-hidden="true"></i>
                            <span class="title-text">{Lang::T('Add New Contact')}</span>
                        </h3>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body form-body panel-body">
                        <!-- Username Field -->
                        <div class="form-group field-group username-group">
                            <label class="col-md-3 control-label field-label" for="username">
                                {Lang::T('Username')}
                                <span class="required-indicator" aria-label="required">*</span>
                            </label>
                            <div class="col-md-9 field-input">
                                <div class="input-group username-input-group">
                                    {if $_c['country_code_phone'] != ''}
                                        <span class="input-group-addon input-icon" id="username-addon">
                                            <i class="glyphicon glyphicon-phone-alt" aria-hidden="true"></i>
                                        </span>
                                    {else}
                                        <span class="input-group-addon input-icon" id="username-addon">
                                            <i class="glyphicon glyphicon-user" aria-hidden="true"></i>
                                        </span>
                                    {/if}
                                    <input type="text" class="form-control field-input-control" 
                                           id="username" name="username" required
                                           placeholder="{if $_c['country_code_phone']!= ''}{$_c['country_code_phone']} {Lang::T('Phone Number')}{else}{Lang::T('Usernames')}{/if}"
                                           aria-describedby="username-addon username-error" aria-required="true">
                                    <div id="username-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Full Name Field -->
                        <div class="form-group field-group fullname-group">
                            <label class="col-md-3 control-label field-label" for="fullname">
                                {Lang::T('Full Name')}
                                <span class="required-indicator" aria-label="required">*</span>
                            </label>
                            <div class="col-md-9 field-input">
                                <input type="text" required class="form-control field-input-control" 
                                       id="fullname" name="fullname" 
                                       placeholder="{Lang::T('Enter full name')}"
                                       aria-required="true" aria-describedby="fullname-error">
                                <div id="fullname-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                            </div>
                        </div>
                        <!-- Email Field -->
                        <div class="form-group field-group email-group">
                            <label class="col-md-3 control-label field-label" for="email">
                                {Lang::T('Email')}
                            </label>
                            <div class="col-md-9 field-input">
                                <input type="email" class="form-control field-input-control" 
                                       id="email" name="email" 
                                       placeholder="{Lang::T('Enter email address')}"
                                       aria-describedby="email-help">
                                <small id="email-help" class="form-text text-muted field-help">
                                    {Lang::T('Optional - used for notifications')}
                                </small>
                            </div>
                        </div>
                        <!-- Phone Number Field -->
                        <div class="form-group field-group phone-group">
                            <label class="col-md-3 control-label field-label" for="phonenumber">
                                {Lang::T('Phone Number')}
                            </label>
                            <div class="col-md-9 field-input">
                                <div class="input-group phone-input-group">
                                    {if $_c['country_code_phone']!= ''}
                                        <span class="input-group-addon input-icon" id="phone-addon">+</span>
                                    {else}
                                        <span class="input-group-addon input-icon" id="phone-addon">
                                            <i class="glyphicon glyphicon-phone-alt" aria-hidden="true"></i>
                                        </span>
                                    {/if}
                                    <input type="tel" class="form-control field-input-control" 
                                           id="phonenumber" name="phonenumber"
                                           placeholder="{if $_c['country_code_phone']!= ''}{$_c['country_code_phone']}{/if} {Lang::T('Phone Number')}"
                                           aria-describedby="phone-addon">
                                </div>
                            </div>
                        </div>
                        <!-- Password Field -->
                        <div class="form-group field-group password-group">
                            <label class="col-md-3 control-label field-label" for="password">
                                {Lang::T('Password')}
                                <span class="required-indicator" aria-label="required">*</span>
                            </label>
                            <div class="col-md-9 field-input">
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control field-input-control password-input" 
                                           autocomplete="new-password" required id="password"
                                           value="{rand(000000,999999)}" name="password" 
                                           onmouseleave="this.type = 'password'"
                                           onmouseenter="this.type = 'text'"
                                           aria-required="true" aria-describedby="password-help password-error">
                                    <small id="password-help" class="form-text text-muted field-help">
                                        {Lang::T('Hover to reveal password')}
                                    </small>
                                    <div id="password-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Address Field -->
                        <div class="form-group field-group address-group">
                            <label class="col-md-3 control-label field-label" for="address">
                                {Lang::T('Home Address')}
                            </label>
                            <div class="col-md-9 field-input">
                                <textarea name="address" id="address" class="form-control field-textarea" 
                                          rows="3" placeholder="{Lang::T('Enter home address')}"
                                          aria-describedby="address-help"></textarea>
                                <small id="address-help" class="form-text text-muted field-help">
                                    {Lang::T('Complete address for service installation')}
                                </small>
                            </div>
                        </div>
                        <!-- Service Type Field -->
                        <div class="form-group field-group service-type-group">
                            <label class="col-md-3 control-label field-label" for="service_type">
                                {Lang::T('Service Type')}
                            </label>
                            <div class="col-md-9 field-input">
                                <select class="form-control field-select" id="service_type" name="service_type"
                                        aria-describedby="service-type-help">
                                    <option value="Hotspot">Hotspot</option>
                                    <option value="PPPoE">PPPoE</option>
                                    <option value="VPN">VPN</option>
                                    <option value="Others">{Lang::T('Other')}</option>
                                </select>
                                <small id="service-type-help" class="form-text text-muted field-help">
                                    {Lang::T('Select the type of internet service')}
                                </small>
                            </div>
                        </div>
                        <!-- Account Type Field -->
                        <div class="form-group field-group account-type-group">
                            <label class="col-md-3 control-label field-label" for="account_type">
                                {Lang::T('Account Type')}
                            </label>
                            <div class="col-md-9 field-input">
                                <select class="form-control field-select" id="account_type" name="account_type"
                                        aria-describedby="account-type-help">
                                    <option value="Personal">{Lang::T('Personal')}</option>
                                    <option value="Business">{Lang::T('Business')}</option>
                                </select>
                                <small id="account-type-help" class="form-text text-muted field-help">
                                    {Lang::T('Personal or business account')}
                                </small>
                            </div>
                        </div>
                        <!-- Coordinates Field -->
                        <div class="form-group field-group coordinates-group">
                            <label class="col-md-3 control-label field-label" for="coordinates">
                                {Lang::T('Coordinates')}
                            </label>
                            <div class="col-md-9 field-input">
                                <input name="coordinates" id="coordinates" class="form-control field-input-control" 
                                       value="" placeholder="-6.465422, 3.406448"
                                       aria-describedby="coordinates-help">
                                <small id="coordinates-help" class="form-text text-muted field-help">
                                    {Lang::T('Click on the map to set location')}
                                </small>
                                <div id="map" class="location-map" style="width: 100%; height: 200px; min-height: 150px; margin-top: 10px; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PPPoE Section Header -->
                    <div class="card-header form-section-header panel-heading">
                        <h4 class="section-title">
                            <i class="section-icon fa fa-network-wired" aria-hidden="true"></i>
                            <span class="title-text">PPPoE</span>
                        </h4>
                    </div>
                    
                    <!-- PPPoE Section Body -->
                    <div class="card-body form-section-body panel-body">
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Usernames')} <span class="label label-danger"
                                id="warning_username"></span></label>
                        <div class="col-md-9">
                            <input type="username" class="form-control" id="pppoe_username" name="pppoe_username"
                                onkeyup="checkUsername(this, '0')">
                            <span class="help-block">{Lang::T('Not Working for freeradius')}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Password')}</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control" id="pppoe_password" name="pppoe_password"
                                onmouseleave="this.type = 'password'" onmouseenter="this.type = 'text'">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Remote IP <span class="label label-danger"
                                id="warning_ip"></span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="pppoe_ip" name="pppoe_ip"
                                onkeyup="checkIP(this, '0')">
                            <span class="help-block">{Lang::T('Also Working for freeradius')}</span>
                        </div>
                    </div>
                    <span class="help-block">
                        {Lang::T('User Cannot change this, only admin. if it Empty it will use Customer Credentials')}
                    </span>
                    </div>
                    
                    <!-- Notification Settings Section -->
                    <div class="card-header form-section-header panel-heading">
                        <h4 class="section-title">
                            <i class="section-icon fa fa-bell" aria-hidden="true"></i>
                            <span class="title-text">{Lang::T('Notification Settings')}</span>
                        </h4>
                    </div>
                    
                    <div class="card-body form-section-body panel-body">
                        <!-- Welcome Message Toggle -->
                        <div class="form-group field-group welcome-message-group">
                            <label class="col-md-3 control-label field-label" for="send_welcome_message">
                                {Lang::T('Send welcome message')}
                            </label>
                            <div class="col-md-9 field-input">
                                <div class="switch-wrapper">
                                    <label class="switch toggle-switch">
                                        <input type="checkbox" id="send_welcome_message" value="1" 
                                               name="send_welcome_message" class="switch-input">
                                        <span class="slider switch-slider"></span>
                                    </label>
                                    <small class="form-text text-muted field-help">
                                        {Lang::T('Send welcome message to new customer')}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- Notification Methods -->
                        <div class="form-group field-group notification-methods-group" id="method" style="display: none;">
                            <label class="col-md-3 control-label field-label">
                                {Lang::T('Notification via')}
                            </label>
                            <div class="col-md-9 field-input">
                                <div class="checkbox-group notification-checkboxes">
                                    <label class="checkbox-label notification-option">
                                        <input type="checkbox" name="sms" value="1" class="notification-checkbox">
                                        <span class="checkbox-text">{Lang::T('SMS')}</span>
                                    </label>
                                    <label class="checkbox-label notification-option">
                                        <input type="checkbox" name="wa" value="1" class="notification-checkbox">
                                        <span class="checkbox-text">{Lang::T('WA')}</span>
                                    </label>
                                    <label class="checkbox-label notification-option">
                                        <input type="checkbox" name="mail" value="1" class="notification-checkbox">
                                        <span class="checkbox-text">{Lang::T('Email')}</span>
                                    </label>
                                </div>
                                <small class="form-text text-muted field-help">
                                    {Lang::T('Select at least one notification method')}
                                </small>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">{Lang::T('Attributes')}</div>
                <div class="panel-body">
                    <!-- Customers Attributes add start -->
                    <div id="custom-fields-container">
                    </div>
                    <!-- Customers Attributes add end -->
                </div>
                <div class="panel-footer">
                    <button class="btn btn-success btn-block" type="button"
                        id="add-custom-field">{Lang::T('Add')}</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary box-solid collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title">{Lang::T('Additional Information')}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body" style="display: none;">
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('City')}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="city" name="city" value="{$d['city']}">
                            <small class="form-text text-muted">{Lang::T('City of Resident')}</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('District')}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="district" name="district"
                                value="{$d['district']}">
                            <small class="form-text text-muted">{Lang::T('District')}</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('State')}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="state" name="state" value="{$d['state']}">
                            <small class="form-text text-muted">{Lang::T('State of Resident')}</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{Lang::T('Zip')}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="zip" name="zip" value="{$d['zip']}">
                            <small class="form-text text-muted">{Lang::T('Zip Code')}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions-container">
            <div class="form-actions text-center">
                <button class="btn btn-primary btn-lg submit-btn"
                        onclick="return ask(this, '{Lang::T("Continue the process of adding Customer Data?")}')" 
                        type="submit">
                    <i class="fa fa-save" aria-hidden="true"></i>
                    <span class="btn-text">{Lang::T('Save Changes')}</span>
                </button>
                <div class="form-cancel">
                    <a href="{Text::url('customers/list')}" class="btn btn-link cancel-btn">
                        <i class="fa fa-times" aria-hidden="true"></i>
                        <span class="btn-text">{Lang::T('Cancel')}</span>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
{literal}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sendWelcomeCheckbox = document.getElementById('send_welcome_message');
            var methodSection = document.getElementById('method');

            function toggleMethodSection() {
                if (sendWelcomeCheckbox.checked) {
                    methodSection.style.display = 'block';
                } else {
                    methodSection.style.display = 'none';
                }
            }

            toggleMethodSection();

            sendWelcomeCheckbox.addEventListener('change', toggleMethodSection);
            document.querySelector('form').addEventListener('submit', function(event) {
                if (sendWelcomeCheckbox.checked) {
                    var methodCheckboxes = methodSection.querySelectorAll('input[type="checkbox"]');
                    var oneChecked = Array.from(methodCheckboxes).some(function(checkbox) {
                        return checkbox.checked;
                    });

                    if (!oneChecked) {
                        event.preventDefault();
                        alert('Please choose at least one method notification.');
                        methodSection.focus();
                    }
                }
            });
        });
    </script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var customFieldsContainer = document.getElementById('custom-fields-container');
            var addCustomFieldButton = document.getElementById('add-custom-field');

            addCustomFieldButton.addEventListener('click', function() {
                var fieldIndex = customFieldsContainer.children.length;
                var newField = document.createElement('div');
                newField.className = 'form-group';
                newField.innerHTML = `
                <div class="col-md-4">
                    <input type="text" class="form-control" name="custom_field_name[]" placeholder="Name">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="custom_field_value[]" placeholder="Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="remove-custom-field btn btn-danger btn-sm">-</button>
                </div>
            `;
                customFieldsContainer.appendChild(newField);
            });

            customFieldsContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-custom-field')) {
                    var fieldContainer = event.target.parentNode.parentNode;
                    fieldContainer.parentNode.removeChild(fieldContainer);
                }
            });
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        function getLocation() {
            if (window.location.protocol == "https:" && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                setupMap(51.505, -0.09);
            }
        }

        function showPosition(position) {
            setupMap(position.coords.latitude, position.coords.longitude);
        }

        function setupMap(lat, lon) {
            var map = L.map('map').setView([lat, lon], 13);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&hl=en&x={x}&y={y}&z={z}&s=Ga', {
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20
        }).addTo(map);
        var marker = L.marker([lat, lon]).addTo(map);
        map.on('click', function(e) {
            var coord = e.latlng;
            var lat = coord.lat;
            var lng = coord.lng;
            var newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng);
            $('#coordinates').val(lat + ',' + lng);
        });
        }
        window.onload = function() {
            getLocation();
        }
    </script>
{/literal}


{include file="sections/footer.tpl"}