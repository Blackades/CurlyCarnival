{include file="sections/header.tpl"}


<div class="row">
    <div class="col-sm-12">
        <div class="box box-primary shadow-md mb-4">
            <div class="box-header with-border py-3">
                {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-sm shadow-sm" title="save"
                        href="{Text::url('customers/csv&token=', $csrf_token)}"
                        onclick="return ask(this, '{Lang::T("This will export to CSV")}?')"><span
                            class="glyphicon glyphicon-download" aria-hidden="true"></span> CSV</a>
                </div>
                {/if}
                <h3 class="box-title">{Lang::T('Manage Contact')}</h3>
            </div>
            <div class="box-body">
                <form id="site-search" method="post" action="{Text::url('customers')}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <div class="search-filter-container bg-light p-4 rounded-md mb-4">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="text-sm text-secondary mb-1">{Lang::T('Order By')}</label>
                                    <div class="input-group">
                                        <div class="row row-no-gutters" style="width: 100%;">
                                            <div class="col-xs-8">
                                                <select class="form-control" id="order" name="order">
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
                                            <div class="col-xs-4">
                                                <select class="form-control border-left-0" id="orderby" name="orderby">
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
                            <div class="col-lg-3 col-md-3 col-sm-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="text-sm text-secondary mb-1">{Lang::T('Status')}</label>
                                    <select class="form-control" id="filter" name="filter">
                                        {foreach $statuses as $status}
                                        <option value="{$status}" {if $filter eq $status }selected{/if}>{Lang::T($status)}
                                        </option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="text-sm text-secondary mb-1">{Lang::T('Search')}</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="{Lang::T('Search')}..." value="{$search}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-primary" type="submit"><span class="fa fa-search"></span>
                                                </button>
                                            <button class="btn btn-info" type="submit" name="export" value="csv">
                                                <span class="glyphicon glyphicon-download" aria-hidden="true"></span> CSV
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-1 col-md-1 col-sm-12 mb-3">
                                <label class="text-sm text-secondary mb-1 d-block">&nbsp;</label>
                                <a href="{Text::url('customers/add')}" class="btn btn-success btn-block shadow-sm"
                                    title="{Lang::T('Add')}">
                                    <i class="ion ion-android-add"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
                <br>&nbsp;
                <div class="table-responsive">
                    <table id="customerTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>{Lang::T('Username')}</th>
                                <th>Photo</th>
                                <th>{Lang::T('Account Type')}</th>
                                <th>{Lang::T('Full Name')}</th>
                                <th>{Lang::T('Balance')}</th>
                                <th>{Lang::T('Contact')}</th>
                                <th>{Lang::T('Package')}</th>
                                <th>{Lang::T('Service Type')}</th>
                                <th>PPPOE</th>
                                <th>{Lang::T('Status')}</th>
                                <th>{Lang::T('Created On')}</th>
                                <th>{Lang::T('Manage')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $d as $ds}
                            <tr {if $ds['status'] !='Active' }class="danger" {/if}>
                                <td><input type="checkbox" name="customer_ids[]" value="{$ds['id']}"></td>
                                <td onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                    style="cursor:pointer;">{$ds['username']}</td>
                                <td>
                                    <a href="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}" target="photo">
                                        <img src="{$app_url}/{$UPLOAD_PATH}{$ds['photo']}.thumb.jpg" width="32" alt="">
                                    </a>
                                </td>
                                <td>{$ds['account_type']}</td>
                                <td onclick="window.location.href = '{Text::url('customers/view/', $ds['id'])}'"
                                    style="cursor: pointer;">{$ds['fullname']}</td>
                                <td>{Lang::moneyFormat($ds['balance'])}</td>
                                <td align="center">
                                    <div class="table-actions">
                                        {if $ds['phonenumber']}
                                        <a href="tel:{$ds['phonenumber']}" class="btn btn-default btn-xs btn-icon"
                                            title="{$ds['phonenumber']}"><i class="glyphicon glyphicon-earphone"></i></a>
                                        {/if}
                                        {if $ds['email']}
                                        <a href="mailto:{$ds['email']}" class="btn btn-default btn-xs btn-icon"
                                            title="{$ds['email']}"><i class="glyphicon glyphicon-envelope"></i></a>
                                        {/if}
                                        {if $ds['coordinates']}
                                        <a href="https://www.google.com/maps/dir//{$ds['coordinates']}/" target="_blank"
                                            class="btn btn-default btn-xs btn-icon" title="{$ds['coordinates']}"><i
                                                class="glyphicon glyphicon-map-marker"></i></a>
                                        {/if}
                                    </div>
                                </td>
                                <td align="center" api-get-text="{Text::url('autoload/plan_is_active/')}{$ds['id']}">
                                    <span class="badge badge-default">&bull;</span>
                                </td>
                                <td>{$ds['service_type']}</td>
                                <td>
                                    {$ds['pppoe_username']}
                                    {if !empty($ds['pppoe_username']) && !empty($ds['pppoe_ip'])}:{/if}
                                    {$ds['pppoe_ip']}
                                </td>
                                <td>
                                    {if $ds['status'] == 'Active'}
                                        <span class="badge badge-success">{Lang::T($ds['status'])}</span>
                                    {elseif $ds['status'] == 'Disabled'}
                                        <span class="badge badge-warning">{Lang::T($ds['status'])}</span>
                                    {elseif $ds['status'] == 'Banned'}
                                        <span class="badge badge-danger">{Lang::T($ds['status'])}</span>
                                    {else}
                                        <span class="badge badge-default">{Lang::T($ds['status'])}</span>
                                    {/if}
                                </td>
                                <td>{Lang::dateTimeFormat($ds['created_at'])}</td>
                                <td align="center">
                                    <div class="table-actions">
                                        <a href="{Text::url('customers/view/')}{$ds['id']}" id="{$ds['id']}"
                                            class="btn btn-success btn-xs">{Lang::T('View')}</a>
                                        <a href="{Text::url('customers/edit/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}"
                                            class="btn btn-info btn-xs">{Lang::T('Edit')}</a>
                                        <a href="{Text::url('customers/sync/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}"
                                            class="btn btn-success btn-xs">{Lang::T('Sync')}</a>
                                        <a href="{Text::url('plan/recharge/', $ds['id'], '&token=', $csrf_token)}"
                                            id="{$ds['id']}"
                                            class="btn btn-primary btn-xs">{Lang::T('Recharge')}</a>
                                    </div>
                                </td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <div class="row" style="padding: 5px">
                        <div class="col-lg-3 col-lg-offset-9">
                            <div class="btn-group btn-group-justified" role="group">
                                <!-- <div class="btn-group" role="group">
                                    {if in_array($_admin['user_type'],['SuperAdmin','Admin'])}
                                    <button id="deleteSelectedTokens" class="btn btn-danger">{Lang::T('Delete
                                        Selected')}</button>
                                    {/if}
                                </div> -->
                                <div class="btn-group" role="group">
                                    <button id="sendMessageToSelected" class="btn btn-success">{Lang::T('Send
                                        Message')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {include file="pagination.tpl"}
            </div>
        </div>
    </div>
</div>
<!-- Modal for Sending Messages -->
<div id="sendMessageModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="sendMessageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendMessageModalLabel">{Lang::T('Send Message')}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select id="messageType" class="form-control">
                    <option value="all">{Lang::T('All')}</option>
                    <option value="email">{Lang::T('Email')}</option>
                    <option value="inbox">{Lang::T('Inbox')}</option>
                    <option value="sms">{Lang::T('SMS')}</option>
                    <option value="wa">{Lang::T('WhatsApp')}</option>
                </select>
                <br>
                <textarea id="messageContent" class="form-control" rows="4"
                    placeholder="{Lang::T('Enter your message here...')}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{Lang::T('Close')}</button>
                <button type="button" id="sendMessageButton" class="btn btn-primary">{Lang::T('Send Message')}</button>
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