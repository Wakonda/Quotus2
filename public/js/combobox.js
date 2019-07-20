function setComboboxEdit(path, table, field)
{
	$('.combobox').ajaxComboBox(
		path,
		{
			lang: 'fr',
			db_table: table,
			per_page: 10,
			navi_num: 10,
			no_image: true,
			init_record: $(field).val()
		}
	).bind('foo', function(e, is_enter_key) {
		if(!is_enter_key) {
			$(field).change();
		}
	});
}

function setComboboxNew(path, table, field)
{
	var options = {
		lang: 'fr',
		db_table: table,
		per_page: 10,
		navi_num: 10,
		no_image: true,
		bind_to: 'foo'
	};
	
	if($(field).val() != "")
		options.init_record = $(field).val();
		
	return $('.combobox').ajaxComboBox(
		path,
		options
	).bind('foo', function(e, is_enter_key) {
		if(!is_enter_key) {
			$(field).change();
		}
	});
}