/**
 * @name 表格操作类(修改值，状态切换)
 */
;(function($) {
	$.fn.editTable = function(options) {
		var self = this,
			local_url = window.location.search,
			settings = {
				url: $(self).attr('data-acturi')
			};
		if(options) {
			$.extend(settings, options);
		}
		//整理排序
		var params  = local_url.substr(1).split('&');
		var sort,order;
		for(var i=0; i < params.length; i++) {
			var param = params[i];
			var arr   = param.split('=');
			if(arr[0] == 'sort') {
				sort = arr[1];
			}
			if(arr[0] == 'order') {
				order = arr[1];
			}
		}
		//修改
		$(self).on("click",'span[data-tdtype="edit"]',function(){
			var s_val   =$.trim($(this).text()),
    			s_name  = $(this).attr('data-field'),
    			s_id    = $(this).attr('data-id'),
    			width   = $(this).width(),
                is_date  = $(this).hasClass('date'),
                input_id="list_table_edit_input_"+s_id;
                $this=$(this);

            $(this).hide();
            $(this).after('<input id="'+input_id+'" type="text" class="tabledit-input form-control" value="'+s_val+'" />');

            function _ajax_update(){
                $this=$('#'+input_id);
                $this.prev('span').show().text($this.val());
				if($this.val() != s_val) {
				    var val=$this.val();
				    if(is_date){
				        val=strtotime(val);
				    }
					$.getJSON(settings.url, {id:s_id, field:s_name, val:val}, function(result){
						if(result.status == 0) {
							$('span[data-field="'+s_name+'"][data-id="'+s_id+'"]').text(s_val);
							return;
						}
					});
				}
                $this.remove();
            }

            $('#'+input_id).width(width).focusout(function(){
                if(is_date) return;
                _ajax_update();
			}).focus().select();
			return false;
		});

		//切换
		$(self).on("click",'a[data-tdtype="toggle"]',function(){
			var img    = this,
				e_icon = $("i",img),
				s_val  = ($(img).attr('data-value'))== 0 ? 1 : 0,
				s_name = $(img).attr('data-field'),
				s_id   = $(img).attr('data-id'),
				s_icon_class  = e_icon.attr("class");
			$.getJSON(settings.url, {id:s_id, field:s_name, val:s_val}, function(result){
				if(result.status == 1) {
					if(s_icon_class.indexOf('mdi-close-circle-outline text-danger')>-1) {
						e_icon.attr({'class':s_icon_class.replace('mdi-close-circle-outline text-danger','mdi-checkbox-marked-circle-outline text-success'),'data-value':s_val});
					} else {
						e_icon.attr({'class':s_icon_class.replace('mdi-checkbox-marked-circle-outline text-success','mdi-close-circle-outline text-danger'),'data-value':s_val});
					}
				}
			});
			return false;
		});
	};
})(jQuery);