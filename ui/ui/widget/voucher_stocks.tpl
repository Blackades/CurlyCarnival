{if $_c['disable_voucher'] != 'yes' && $stocks['unused']>0 || $stocks['used']>0}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-ticket"></i> Vouchers Stock</h3>
        </div>
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{Lang::T('Package Name')}</th>
                            <th class="text-center">
                                <span class="badge badge-info">
                                    <i class="fa fa-circle-o"></i> Unused
                                </span>
                            </th>
                            <th class="text-center">
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle-o"></i> Used
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $plans as $stok}
                            <tr>
                                <td><strong>{$stok['name_plan']}</strong></td>
                                <td class="text-center"><span class="text-info"><strong>{$stok['unused']}</strong></span></td>
                                <td class="text-center"><span class="text-success"><strong>{$stok['used']}</strong></span></td>
                            </tr>
                        {/foreach}
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray">
                            <th>Total</th>
                            <th class="text-center"><span class="text-info"><strong>{$stocks['unused']}</strong></span></th>
                            <th class="text-center"><span class="text-success"><strong>{$stocks['used']}</strong></span></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
{/if}