{include file="sections/header.tpl"}

<div class="row">
	<div class="col-sm-12">
		<div class="box box-primary shadow-md mb-4">
			<div class="box-header with-border py-3">
				<h3 class="box-title">{Lang::T('Bandwidth Plans')}</h3>
			</div>
			<div class="box-body">
				<div class="search-filter-container bg-light p-4 rounded-md mb-4">
					<div class="row">
						<div class="col-md-8 mb-3">
							<form id="site-search" method="post" action="{Text::url('bandwidth/list/')}">
								<div class="input-group">
									<div class="input-group-addon">
										<span class="fa fa-search"></span>
									</div>
									<input type="text" name="name" class="form-control"
										placeholder="{Lang::T('Search by Name')}...">
									<div class="input-group-btn">
										<button class="btn btn-success" type="submit">{Lang::T('Search')}</button>
									</div>
								</div>
							</form>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{Text::url('bandwidth/add')}" class="btn btn-primary btn-block shadow-sm"><i
									class="ion ion-android-add">
								</i> {Lang::T('New Bandwidth')}</a>
						</div>
					</div>
				</div>
				<div class="table-responsive">
                    <table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>{Lang::T('Bandwidth Name')}</th>
								<th>{Lang::T('Rate')}</th>
								<th>Burst</th>
								<th>{Lang::T('Manage')}</th>
							</tr>
						</thead>
						<tbody>
							{foreach $d as $ds}
								<tr>
									<td>{$ds['name_bw']}</td>
									<td>{$ds['rate_down']} {$ds['rate_down_unit']} / {$ds['rate_up']} {$ds['rate_up_unit']}
									</td>
									<td>{$ds['burst']}</td>
									<td>
										<a href="{Text::url('bandwidth/edit/', $ds['id'])}"
											class="btn btn-sm btn-warning">{Lang::T('Edit')}</a>
										<a href="{Text::url('bandwidth/delete/', $ds['id'])}" id="{$ds['id']}"
											class="btn btn-danger btn-sm"
											onclick="return ask(this, '{Lang::T('Delete')}?')"><i
												class="glyphicon glyphicon-trash"></i></a>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				{include file="pagination.tpl"}
				<div class="bs-callout bs-callout-info" id="callout-navbar-role">
					<h4>{Lang::T('Create Bandwidth Package for expired Internet Package')}</h4>
					<p>{Lang::T('When customer expired, you can move it to Expired Internet Package')}</p>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

{include file="sections/footer.tpl"}