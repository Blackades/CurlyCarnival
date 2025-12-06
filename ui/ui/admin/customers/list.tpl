{include file="sections/header.tpl"}

<!-- Customers Management Container -->
<div class="customers-container admin-table-container">
    <div class="row table-page-row">
        <div class="col-sm-12 table-page-col">
            <!-- Customers Table Card -->
            <div class="card table-card box box-primary shadow-md mb-4">
                <!-- Card Header -->
                <div class="card-header table-header box-header with-border py-3">
                    <div class="header-content">
                        <h3 class="card-title table-title box-title">
                            <i class="table-icon fa fa-users" aria-hidden="true"></i>
                            <span class="title-text">{Lang::T('Manage Contact')}</span>
                        </h3>
                        {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                            <div class="header-actions btn-group pull-right">
                                <a class="btn btn-primary btn-sm shadow-sm export-btn" 
                                   title="Export to CSV"
                                   href="{Text::url('customers/csv&token=', $csrf_token)}"
                                   onclick="return ask(this, '{Lang::T("This will export to CSV")}?')">
                                    <i class="glyphicon glyphicon-download" aria-hidden="true"></i>
                                    <span class="btn-text">CSV</span>
                                </a>
                            </div>
                        {/if}
                    </div>
                </div>
                <!-- Card Body -->
                <div class="card-body table-body box-body">
                    <!-- Search and Filter Form -->
                    <form id="site-search" class="table-search-form" method="post" action="{Text::url('customers')}">
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        <div class="search-filter-container table-filters bg-light p-4 rounded-md mb-4">
                            <div class="filter-row row">
                                <!-- Order By Filter -->
                                <div class="filter-col col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <div class="form-group filter-group mb-0">
                                        <label class="filter-label text-sm text-secondary mb-1">{Lang::T('Order By')}</label>
                                        <div class="input-group order-input-group">
                                            <div class="order-controls row row-no-gutters" style="width: 100%;">
                                                <div class="col-xs-8 order-field-col">
                                                    <select class="form-control order-select" id="order" name="order">
                                                        <option value="username" {if $order eq 'username' }selected{/if}>
                                                            {Lang::T('Username')}</option>
                                                        <option value="fullname" {if $order eq 'fullname' }selected{/if}>
                                                            {Lang::T('First Name')}</option>
                                                        <option value="lastname" {if $order eq 'lastname' }selected{/if}>
                                                            {Lang::T('Last Name')}</option>
                                                        <option value="created_at" {if $order eq 'created_at' }selected{/if}>
                                                            {Lang::T('Created Date')}</option>
                                                        <option value="balance" {if $order eq 'balance' }selected{/if}>
                                                            {Lang::T('Balance')}</option>
                                                        <option value="status" {if $order eq 'status' }selected{/if}>
                                                            {Lang::T('Status')}</option>
                                                    </select>
                                                </div>
                                                <div class="col-xs-4 order-direction-col">
                                                    <select class="form-control order-direction border-left-0" id="orderby" name="orderby">
                                                        <option value="asc" {if $orderby eq 'asc' }selected{/if}>
                                                            {Lang::T('Ascending')}</option>
                                                        <option value="desc" {if $orderby eq 'desc' }selected{/if}>
                                                            {Lang::T('Descending')}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Status Filter -->
                                <div class="filter-col col-lg-3 col-md-3 col-sm-12 mb-3">
                                    <div class="form-group filter-group mb-0">
                                        <label class="filter-label text-sm text-secondary mb-1">{Lang::T('Status')}</label>
                                        <select class="form-control status-filter" id="filter" name="filter">
                                            {foreach $statuses as $status}
                                                <option value="{$status}" {if $filter eq $status }selected{/if}>
                                                    {Lang::T($status)}
                                                </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <!-- Search Filter -->
                                <div class="filter-col col-lg-4 col-md-4 col-sm-12 mb-3">
                                    <div class="form-group filter-group mb-0">
                                        <label class="filter-label text-sm text-secondary mb-1">{Lang::T('Search')}</label>
                                        <div class="input-group search-input-group">
                                            <input type="text" name="search" class="form-control search-input"
                                                   placeholder="{Lang::T('Search')}..." value="{$search}"
                                                   aria-label="Search customers">
                                            <div class="input-group-btn search-actions">
                                                <button class="btn btn-primary search-btn" type="submit" 
                                                        aria-label="Search">
                                                    <i class="fa fa-search" aria-hidden="true"></i>
                                                </button>
                                                <button class="btn btn-info export-btn" type="submit" 
                                                        name="export" value="csv" aria-label="Export to CSV">
                                                    <i class="glyphicon glyphicon-download" aria-hidden="true"></i>
                                                    <span class="btn-text hidden-xs">CSV</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Add Customer Button -->
                                <div class="filter-col col-lg-1 col-md-1 col-sm-12 mb-3">
                                    <label class="filter-label text-sm text-secondary mb-1 d-block">&nbsp;</label>
                                    <a href="{Text::url('customers/add')}" 
                                       class="btn btn-success btn-block shadow-sm add-customer-btn"
                                       title="{Lang::T('Add Customer')}" aria-label="Add new customer">
                                        <i class="ion ion-android-add" aria-hidden="true"></i>
                                        <span class="btn-text hidden-xs">{Lang::T('Add')}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Table Container -->
                    <div class="table-container">
                        <div class="table-responsive customers-table-responsive">
                            <table id="customerTable" class="table table-striped table-hover customers-table data-table">
                                <!-- Table Header -->
                                <thead class="table-header">
                                    <tr class="header-row">
                                        <th class="select-col">
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" id="select-all" class="select-all-checkbox" 
                                                       aria-label="Select all customers">
                                            </div>
                                        </th>
                                        <th class="username-col sortable">{Lang::T('Username')}</th>
                                        <th class="photo-col">Photo</th>
                                        <th class="account-type-col">{Lang::T('Account Type')}</th>
                                        <th class="fullname-col sortable">{Lang::T('Full Name')}</th>
                                        <th class="balance-col sortable">{Lang::T('Balance')}</th>
                                        <th class="contact-col">{Lang::T('Contact')}</th>
                                        <th class="package-col">{Lang::T('Package')}</th>
                                        <th class="service-type-col">{Lang::T('Service Type')}</th>
                                        <th class="pppoe-col">PPPOE</th>
                                        <th class="status-col sortable">{Lang::T('Status')}</th>
                                        <th class="created-col sortable">{Lang::T('Created On')}</th>
                                        <th class="actions-col">{Lang::T('Manage')}</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody class="table-body">
                                    {foreach $d as $ds}
                                        <tr class="table-row customer-row {if $ds['status'] !='Active' }row-inactive danger{/if}" 
                                            data-customer-id="{$ds['id']}" data-status="{$ds['status']}">
                                            <!-- Select Checkbox -->
                                            <td class="select-cell">
                                                <div class="checkbox-wrapper">
                                                    <input type="checkbox" name="customer_ids[]" value="{$ds['id']}" 
                                                           class="customer-checkbox" aria-label="Select customer {$ds['username']}">
                                                </div>
                                            </td>
                                            
                                            <!-- Username -->
                                            <td class="username-cell clickable-cell" 
                                                onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                                role="button" tabindex="0" aria-label="View customer {$ds['username']}">
                                                <span class="username-text">{$ds['username']}</span>
                                            </td>
                                            
                                            <!-- Photo -->
                                            <td class="photo-cell">
                                                <div class="customer-photo">
                                                    <a href="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}" target="photo" 
                                                       class="photo-link" aria-label="View full photo of {$ds['fullname']}">
                                                        <img src="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}.thumb.jpg" 
                                                             width="32" height="32" alt="Photo of {$ds['fullname']}" 
                                                             class="customer-avatar">
                                                    </a>
                                                </div>
                                            </td>
                                            
                                            <!-- Account Type -->
                                            <td class="account-type-cell">
                                                <span class="account-type-text">{$ds['account_type']}</span>
                                            </td>
                                            
                                            <!-- Full Name -->
                                            <td class="fullname-cell clickable-cell" 
                                                onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                                role="button" tabindex="0" aria-label="View customer {$ds['fullname']}">
                                                <span class="fullname-text">{$ds['fullname']}</span>
                                            </td>
                                            
                                            <!-- Balance -->
                                            <td class="balance-cell">
                                                <span class="balance-amount">{Lang::moneyFormat($ds['balance'])}</span>
                                            </td>
                                            <!-- Contact Actions -->
                                            <td class="contact-cell">
                                                <div class="contact-actions table-actions">
                                                    {if $ds['phonenumber']}
                                                        <a href="tel:{$ds['phonenumber']}" 
                                                           class="btn btn-default btn-xs btn-icon contact-btn phone-btn"
                                                           title="Call {$ds['phonenumber']}" aria-label="Call {$ds['phonenumber']}">
                                                            <i class="glyphicon glyphicon-earphone" aria-hidden="true"></i>
                                                        </a>
                                                    {/if}
                                                    {if $ds['email']}
                                                        <a href="mailto:{$ds['email']}" 
                                                           class="btn btn-default btn-xs btn-icon contact-btn email-btn"
                                                           title="Email {$ds['email']}" aria-label="Email {$ds['email']}">
                                                            <i class="glyphicon glyphicon-envelope" aria-hidden="true"></i>
                                                        </a>
                                                    {/if}
                                                    {if $ds['coordinates']}
                                                        <a href="https://www.google.com/maps/dir//{$ds['coordinates']}/" 
                                                           target="_blank" rel="noopener noreferrer"
                                                           class="btn btn-default btn-xs btn-icon contact-btn location-btn"
                                                           title="View location {$ds['coordinates']}" 
                                                           aria-label="View location on map">
                                                            <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
                                                        </a>
                                                    {/if}
                                                </div>
                                            </td>
                                            <!-- Package Status -->
                                            <td class="package-cell" api-get-text="{Text::url('autoload/plan_is_active/')}{$ds['id']}">
                                                <div class="package-status">
                                                    <span class="badge badge-default package-badge">&bull;</span>
                                                </div>
                                            </td>
                                            
                                            <!-- Service Type -->
                                            <td class="service-type-cell">
                                                <span class="service-type-text">{$ds['service_type']}</span>
                                            </td>
                                            
                                            <!-- PPPOE Info -->
                                            <td class="pppoe-cell">
                                                <div class="pppoe-info">
                                                    {if $ds['pppoe_username']}
                                                        <span class="pppoe-username">{$ds['pppoe_username']}</span>
                                                    {/if}
                                                    {if !empty($ds['pppoe_username']) && !empty($ds['pppoe_ip'])}
                                                        <span class="pppoe-separator">:</span>
                                                    {/if}
                                                    {if $ds['pppoe_ip']}
                                                        <span class="pppoe-ip">{$ds['pppoe_ip']}</span>
                                                    {/if}
                                                </div>
                                            </td>
                                            
                                            <!-- Status Badge -->
                                            <td class="status-cell">
                                                <div class="status-wrapper">
                                                    {if $ds['status'] == 'Active'}
                                                        <span class="badge badge-success status-badge status-active">
                                                            {Lang::T($ds['status'])}
                                                        </span>
                                                    {elseif $ds['status'] == 'Disabled'}
                                                        <span class="badge badge-warning status-badge status-disabled">
                                                            {Lang::T($ds['status'])}
                                                        </span>
                                                    {elseif $ds['status'] == 'Banned'}
                                                        <span class="badge badge-danger status-badge status-banned">
                                                            {Lang::T($ds['status'])}
                                                        </span>
                                                    {else}
                                                        <span class="badge badge-default status-badge status-other">
                                                            {Lang::T($ds['status'])}
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                            
                                            <!-- Created Date -->
                                            <td class="created-cell">
                                                <span class="created-date">{Lang::dateTimeFormat($ds['created_at'])}</span>
                                            </td>
                                            <!-- Management Actions -->
                                            <td class="actions-cell">
                                                <div class="table-actions customer-actions">
                                                    <a href="{Text::url('customers/view/')}{$ds['id']}" 
                                                       class="btn btn-success btn-xs action-btn view-btn"
                                                       title="View customer details" aria-label="View {$ds['fullname']}">
                                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                                        <span class="btn-text hidden-xs">{Lang::T('View')}</span>
                                                    </a>
                                                    <a href="{Text::url('customers/edit/', $ds['id'], '&token=', $csrf_token)}"
                                                       class="btn btn-info btn-xs action-btn edit-btn"
                                                       title="Edit customer" aria-label="Edit {$ds['fullname']}">
                                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                                        <span class="btn-text hidden-xs">{Lang::T('Edit')}</span>
                                                    </a>
                                                    <a href="{Text::url('customers/sync/', $ds['id'], '&token=', $csrf_token)}"
                                                       class="btn btn-success btn-xs action-btn sync-btn"
                                                       title="Sync customer data" aria-label="Sync {$ds['fullname']}">
                                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                                        <span class="btn-text hidden-xs">{Lang::T('Sync')}</span>
                                                    </a>
                                                    <a href="{Text::url('plan/recharge/', $ds['id'], '&token=', $csrf_token)}"
                                                       class="btn btn-primary btn-xs action-btn recharge-btn"
                                                       title="Recharge customer account" aria-label="Recharge {$ds['fullname']}">
                                                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                                                        <span class="btn-text hidden-xs">{Lang::T('Recharge')}</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Table Actions -->
                        <div class="table-actions-container">
                            <div class="bulk-actions-row row">
                                <div class="bulk-actions-col col-lg-3 col-lg-offset-9">
                                    <div class="btn-group btn-group-justified bulk-actions" role="group">
                                        <!-- Commented out delete functionality
                                        <div class="btn-group" role="group">
                                            {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                                <button id="deleteSelectedTokens" class="btn btn-danger bulk-action-btn">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                    {Lang::T('Delete Selected')}
                                                </button>
                                            {/if}
                                        </div>
                                        -->
                                        <div class="btn-group message-action-group" role="group">
                                            <button id="sendMessageToSelected" class="btn btn-success bulk-action-btn message-btn">
                                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                                <span class="btn-text">{Lang::T('Send Message')}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <div class="pagination-container">
                        {include file="pagination.tpl"}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Send Message Modal -->
<div id="sendMessageModal" class="modal fade message-modal" tabindex="-1" role="dialog" 
     aria-labelledby="sendMessageModalLabel" aria-hidden="true" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content modal-card">
            <!-- Modal Header -->
            <div class="modal-header modal-card-header">
                <div class="modal-title-wrapper">
                    <h5 class="modal-title modal-card-title" id="sendMessageModalLabel">
                        <i class="modal-icon fa fa-envelope" aria-hidden="true"></i>
                        <span class="modal-title-text">{Lang::T('Send Message')}</span>
                    </h5>
                </div>
                <button type="button" class="modal-close btn-close" data-dismiss="modal" aria-label="Close send message dialog">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body modal-card-body">
                <div class="message-form modal-form">
                    <!-- Message Type Selection -->
                    <div class="form-group field-group message-type-group">
                        <label for="messageType" class="form-label field-label">
                            <i class="field-icon fa fa-list" aria-hidden="true"></i>
                            <span class="label-text">{Lang::T('Message Type')}</span>
                        </label>
                        <select id="messageType" class="form-control field-select message-type-select" 
                                aria-describedby="messageType-help">
                            <option value="all">{Lang::T('All')}</option>
                            <option value="email">{Lang::T('Email')}</option>
                            <option value="inbox">{Lang::T('Inbox')}</option>
                            <option value="sms">{Lang::T('SMS')}</option>
                            <option value="wa">{Lang::T('WhatsApp')}</option>
                        </select>
                        <small id="messageType-help" class="form-text text-muted field-help">
                            {Lang::T('Select how to send the message')}
                        </small>
                    </div>
                    
                    <!-- Message Content -->
                    <div class="form-group field-group message-content-group">
                        <label for="messageContent" class="form-label field-label">
                            <i class="field-icon fa fa-comment" aria-hidden="true"></i>
                            <span class="label-text">{Lang::T('Message Content')}</span>
                        </label>
                        <textarea id="messageContent" class="form-control field-textarea message-textarea" rows="4"
                                  placeholder="{Lang::T('Enter your message here...')}"
                                  aria-describedby="messageHelp messageContent-error" aria-required="true"></textarea>
                        <small id="messageHelp" class="form-text text-muted field-help">
                            {Lang::T('This message will be sent to all selected customers')}
                        </small>
                        <div id="messageContent-error" class="error-message" role="alert" aria-live="polite" style="display: none;"></div>
                            <i class="help-icon fa fa-info-circle" aria-hidden="true"></i>
                            <span class="help-text">{Lang::T('This message will be sent to all selected customers')}</span>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer modal-card-footer">
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary modal-cancel" data-dismiss="modal">
                        <i class="btn-icon fa fa-times" aria-hidden="true"></i>
                        <span class="btn-text">{Lang::T('Close')}</span>
                    </button>
                    <button type="button" id="sendMessageButton" class="btn btn-primary modal-send">
                        <i class="btn-icon fa fa-paper-plane" aria-hidden="true"></i>
                        <span class="btn-text">{Lang::T('Send Message')}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Select or deselect all checkboxes
    document.getElementById('select-all').addEventListener('change', function () {
        var checkboxes = document.querySelectorAll('input[name="customer_ids[]"]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    $(document).ready(function () {
        let selectedCustomerIds = [];

        // Collect selected customer IDs when the button is clicked
        $('#sendMessageToSelected').on('click', function () {
            selectedCustomerIds = $('input[name="customer_ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedCustomerIds.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: "{Lang::T('Please select at least one customer to send a message.')}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Open the modal
            $('#sendMessageModal').modal('show');
        });

        // Handle sending the message
        $('#sendMessageButton').on('click', function () {
            const message = $('#messageContent').val().trim();
            const messageType = $('#messageType').val();

            if (!message) {
                Swal.fire({
                    title: 'Error!',
                    text: "{Lang::T('Please enter a message to send.')}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Disable the button and show loading text
            $(this).prop('disabled', true).text('{Lang::T('Sending...')}');

            $.ajax({
                url: '?_route=message/send_bulk_selected',
                method: 'POST',
                data: {
                    customer_ids: selectedCustomerIds,
                    message_type: messageType,
                    message: message
                },
                dataType: 'json',
                success: function (response) {
                    // Handle success response
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: "{Lang::T('Message sent successfully.')}",
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: "{Lang::T('Error sending message: ')}" + response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    $('#sendMessageModal').modal('hide');
                    $('#messageContent').val(''); // Clear the message content
                },
                error: function () {
                    Swal.fire({
                        title: 'Error!',
                        text: "{Lang::T('Failed to send the message. Please try again.')}",
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function () {
                    // Re-enable the button and reset text
                    $('#sendMessageButton').prop('disabled', false).text('{Lang::T('Send Message')}');
                }
            });
        });
    });

    $(document).ready(function () {
        $('#sendMessageModal').on('show.bs.modal', function () {
            $(this).attr('inert', 'true');
        });
        $('#sendMessageModal').on('shown.bs.modal', function () {
            $('#messageContent').focus();
            $(this).removeAttr('inert');
        });
        $('#sendMessageModal').on('hidden.bs.modal', function () {
            // $('#button').focus();
        });
    });
</script>
{include file = "sections/footer.tpl" }