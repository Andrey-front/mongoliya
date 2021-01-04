<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $module; ?>" class="btn btn-default"><?php echo $text_module_setting; ?></a>
				<a href="<?php echo $order; ?>" class="btn btn-default"><?php echo $text_order_store; ?></a> 
				<a href="<?php echo $shiptor; ?>" class="btn btn-default"><?php echo $text_order_shiptor; ?></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
					<?php if (!empty($breadcrumb['separator'])) : ?>
						<li><?php echo $breadcrumb['separator']; ?></li>
					<?php endif; ?>
					<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	
	<div class="container-fluid">
		<link rel="stylesheet" href="view/javascript/shipping/shiptor/shiptor.css" rel="stylesheet" />
		<link rel="stylesheet" href="view/javascript/shipping/shiptor/bootstrap-table/bootstrap-table.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="view/javascript/shipping/shiptor/bootstrap-table/bootstrap-editable.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="view/javascript/shipping/shiptor/kladr/css/jquery.kladr.min.css" />

		<script src="view/javascript/shipping/shiptor/notify.min.js"></script>
		<script src="view/javascript/shipping/shiptor/bootstrap-table/bootstrap-table.min.js"></script>
		<script src="view/javascript/shipping/shiptor/bootstrap-table/bootstrap-table-editable.min.js"></script>
		<script src="view/javascript/shipping/shiptor/bootstrap-table/bootstrap-editable.min.js"></script>	
		<script src="view/javascript/shipping/shiptor/kladr/js/jquery.kladr.min.js"></script>
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-list"></i></h3>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<div id="toolbar">
						<form class="form-inline">
							<div class="bs-bars pull-left">
								<input type="text" name="filter_customer" placeholder="<?php echo $text_filter_customer; ?>" class="form-control" /> 
								<input type="text" name="filter_email" placeholder="<?php echo $text_filter_email; ?>" class="form-control" /> 
								<input type="text" name="filter_telephone" placeholder="<?php echo $text_filter_telephone; ?>" class="form-control" /> 
								<input type="text" name="filter_kladr_id" placeholder="<?php echo $text_filter_kladr_id; ?>" class="form-control" /> 			
								<button type="button" class="btn btn-primary sh-filter-button"><i class="fa fa-search"></i> <?php echo $button_filter; ?></button>
							</div>
						</form>
					</div>
					<table id="table"></table>
				</div>
			</div>
		</div>
	</div>

	<!-- HTML-код модального окна-->
	<div class="modal fade" id="modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-body"></div>
			</div>
		</div>
	</div>

	<script>
	var $table = $('#table'), $toolbar = $('#toolbar'), filter_data = {};

	$(function () {
		$(document).on('click', '.sh-filter-button', function () {
			var object = $toolbar.find('form').serializeArray();

			filter_data = {}

			$.each(object, function(i, e){
				if (e.value) {
					filter_data[e.name] = encodeURIComponent(e.value);
				}
			});

			$table.bootstrapTable('refresh');
		});

		$table.bootstrapTable({
			locale: 'ru-RU',
			url: 'index.php?route=extension/shipping/shiptor/customer_table&token=<?php echo $token; ?>', 
			queryParams: function (params) {
				filter_data['per_page'] = params.limit;
				filter_data['page'] = params.offset;

				return filter_data;
			},
			idField: 'address_id',
			uniqueId: 'address_id',		
			cache: 0,
					showRefresh: 1,
			showFilter: 1,
			showColumns: 1,
			minimumCountColumns: 3,
			toolbar: '#toolbar',
			toolbarAlign: 'left',
			pageSize: 50,
			pageList: [50, 100, 200],
			pagination: 1,
			sidePagination: 'server',
			iconsPrefix: 'fa',
					icons: { refresh: 'fa-refresh icon-refresh', columns: 'fa-table icon-th' },
			columns: 
				[{
					title: '<?php echo $column_recipient; ?>', 
					field: 'recipient', valign: 'middle', align:	'center'
				},{
					title: '<?php echo $column_customer; ?>', 
					field: 'customer', valign: 'middle', align:	'center'
				},{
					title: '<?php echo $column_address; ?>', 
					field: 'address', valign: 'middle', align:	'left'
				},{
					title: '<?php echo $column_kladr_id; ?>', 
					field: 'kladr_id', align:	'center', valign: 'middle', 
					editable: {
											clear: true,
											validate: function(value) {
													if (! $.isNumeric(value)) {
														 return '<?php echo $error_is_numeric; ?>';
													}
											}			
									}
				},{
									field: 'action', 
					valign: 'middle', align:	'center',
									formatter: function(value, row) {
						return '<button onclick="remove(' + row.address_id + ');" type="button" class="btn btn-xs btn-danger" title="Удалить выбранные товары"><i class="fa fa-trash-o"></i></button>';
					}
							}]
		}).on('editable-save.bs.table', function (e, field, row) {
					save(row);
		});
	});

	function save(row) {
		$.post('index.php?route=extension/shipping/shiptor/customer_table&token=<?php echo $token; ?>', { type: 'save', address_id: row.address_id, customer_id: row.customer_id, kladr_id: row.kladr_id }).done(function(json) {
			if (json.success) {
				$.notify(json.success, 'success');
				$table.bootstrapTable('refresh');
			}
		});
	}

	function remove(address_id) {
			$.post('index.php?route=extension/shipping/shiptor/customer_table&token=<?php echo $token; ?>', { type: 'remove', address_id: address_id }) .done(function(json) {
			if (json.success) {
				$.notify(json.success, 'success');
				$table.bootstrapTable('refresh');
			}
		});
	}

	$('[name="filter_customer"]').autocomplete({source:function(a,b){$.ajax({url:"index.php?route=customer/customer/autocomplete&token=<?php echo $token; ?>&filter_name="+encodeURIComponent(a),dataType:"json",success:function(a){b($.map(a,function(a){return{label:a.name,value:a.customer_id}}))}})},select:function(a){$('[name="filter_customer"]').val(a.label)}});

	$(document).on("click",'[data-name="kladr_id"]',function(){var a=$(this).closest("td").find(".editable-input input");a.kladr({type:$.kladr.type.city,withParents:!0,select:function(b){a.val(b.id.substr(0,11))},labelFormat:function(a,b){var c="",d=a.name.toLowerCase();b=b.name.toLowerCase();var e=d.indexOf(b);if(e=e>0?e:0,a.typeShort&&(c+=a.typeShort+". "),b.length<a.name.length?(c+=a.name.substr(0,e),c+="<strong>"+a.name.substr(e,b.length)+"</strong>",c+=a.name.substr(e+b.length,a.name.length-b.length-e)):c+="<strong>"+a.name+"</strong>",a.parents)for(var f=a.parents.length-1;f>-1;f--){var g=a.parents[f];g.name&&(c&&(c+="<small>, </small>"),c+="<small>"+g.name+" "+g.typeShort+".</small>")}return c}})});
	</script>
</div>
<?php echo $footer; ?>
