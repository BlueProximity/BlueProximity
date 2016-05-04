function LoginSplash(){
	$(".mlogin_submit").click(LoginSplash.submitForm);
	
	$("#dimmer").click(LoginSplash.toggleSplash);
	$(".mlogin_close").click(LoginSplash.toggleSplash).css("visibility", "hidden");;
	$("#dimmer").click(LoginSplash.hide);
	
	$("input").keydown(function(event){
		if(event.keyCode == 13){
			
			var parent = $(this);
			while( parent != null && !parent.hasClass("action") ){
				parent = parent.parent();
			}
							
			var submit = parent.children(".mlogin_submit");
			if(submit != null){
				submit.click();
			}
		}
	});
	
	LoginSplash.page = -1;
	LoginSplash.lock = true;
	
	LoginSplash.hide();
	LoginSplash.toPage(0);
}

LoginSplash.toPage = function(page){
	if(page == LoginSplash.page)
		return;
	
	if(LoginSplash.page != -1)
		$(".action").eq(LoginSplash.page).fadeOut(200);
	
	LoginSplash.page = page;
	$(".action").eq(page).fadeIn(600);
	$("#mlogin_wrapper").height( $(".action").eq(page).height() + 50);
};

LoginSplash.toggleSplash = function(hide){
	if(typeof hide == 'boolean' || LoginSplash.lock != true){
		if(typeof hide == undefined)
			hide = ($("#lsplash_wrapper").css("display") == "none") ? false : true;

		if( !hide )
			$("#lsplash_wrapper").show();
		else
			$("#lsplash_wrapper").hide();
	}
};

LoginSplash.hide = function(){
	$(".action").each(function(index){
		if( index != LoginSplash.page )
			$(this).hide();
	});
};

LoginSplash.show = function(){	
	$(".action").eq(LoginSplash.page).fadeIn(300);
};

LoginSplash.submitForm = function(){
	$(this).siblings(".create_form").children().each(function(){
		if($(this).prop("id").indexOf("mac") != -1){
			$(this).val( $(this).val().split(":").join(""));
		};
	});
	
	if( $(this).prop("id") == "lu_data_query" ){
		var url = "lookup.php?";
		
		if( $("#lu_fullname").val() != "" ){
			url  += "lu_fullname=" + $("#lu_fullname").val().split(" ").join("%20");
		}
		
		TimestampParser.loadData(url);	
		return;
	}
	
	$(this).siblings(".create_form").submit();
};

LoginSplash.globalError = function(parent, msg, isErr){
	var container = $("#" + parent).children(".mlogin_error");
	
	container.html(msg);
	
	if(isErr == 0){
		container.css("color", "green");
	}
	
	$(".action").each(function(index){
		if( $(this).prop("id") == parent ){
			LoginSplash.toPage(index);
			LoginSplash.hide();
		}
	});
};

LoginSplash.insertError = function(id, val, msg){
	console.log(id,val,msg);
	var elem = $("#" + id);
	
	if(elem[0].tagName == "DIV")
		elem.children("input").val(val);
	else
		elem.val(val);
	
	elem.wrap("<div></div>")
	elem.addClass("binline");
	elem.css("margin-left", "20px");
	elem.parent().append("<img class='field_err_icon binline' src='image/" + (msg=="" ? "ok" : "error") + ".png'/>");
	
	if(msg != ""){
		elem.parent().append("<div class='field_err_msg'>"+msg+"</div>");
		
		var image = elem.parent().children("img");
		var error = elem.parent().children("div");
		image.mousemove(function(e){
			error.css("top", e.clientY + 1).css("left", e.clientX + 1);
		});
	}
};

LoginSplash.removeErrors = function(){
	$(".field_err_msg").each(function(){
		var input = $(this).siblings("input");
		$(this).parent().after(input);
		$(this).parent().remove();
	});
};