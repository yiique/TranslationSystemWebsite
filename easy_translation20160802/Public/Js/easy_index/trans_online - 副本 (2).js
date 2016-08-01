
function trans()
{
//	alert("1");
	$('#tags').tagsInput();
	//$("#result").html("<font color='red'>该语种翻译不存在</font>");
	var dirid=$("#direction").combobox('getValue');
	setCookie('direction',dirid,365);
	var content=$("#input_area").text();
	if(content=="") $("#result").html("<font color='red'>请输入翻译内容</font>");
	else{
	//alert(dirid);
	$("#result").html("");
	$.post(app_url+'/Trans/tranSent',{'dirid':dirid,'content':content},function(data){
				//alert(data.info);
				//if(data.info.indexOf("<ResCode>21</ResCode>")!=-1)
				var test=data.data;
				var st=data.info;
				
			
	
	   
      var i,j;
	  for(i=0;i<test.length;i++)
       {	var words=$("<div class='mul' style='float: left' ></div> ");
			
			var sp=$("<span>"+test[i][0]+"</span>");
			words.append(sp);
			var segs=$("<ul class='segs trans_wrapper'></ul>");
			for(j=0;j<test[i].length && j<5;j++)
			{
				var seg=$("<li style='list-style-type:none'>"+test[i][j]+"</li>");
				segs.append(seg);
				seg.click(function(){
				//alert($(this).text());
				$(this).parent().parent().children("span").text($(this).text());
				});
			}
			
			words.append(segs);
			if(st==0);
			words.append("&nbsp;");
			segs.hide();
			$("#result").append(words);
			words.click(function(){
				//alert(words.attr("id"));
				$(this).children("ul").show();
		
				$(this).children("ul").css("background-color","white");
				$(this).children("span").css({
				"background-color":"white",
				"border":"1px solid blue"
				});
				});
			words.mouseleave(function(){
				//alert(words.attr("id"));
				$(this).children("ul").hide();
				$(this).children("span").css({
				"background-color":"white",
				"border":"0px solid blue"
				
				});
				
				
				});
			words.mouseover(function(){
				//alert(words.attr("id"));
				$(this).children("span").css("background-color","yellow");
				});
	   
       }
	
    },'json');
	}
}
$(function (){


});