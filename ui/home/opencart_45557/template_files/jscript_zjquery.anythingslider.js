(function($){$.fn.loopedCarousel=function(options){var defaults={container:'.container',slides:'.slides',pagination:'.pagination',autoStart:0,slidespeed:300,fadespeed:300,items:3,padding:10,showPagination:true,vertical:false};this.each(function(){var obj=$(this);var o=$.extend(defaults,options);var i=o.items;var m=0;var t=1;var s=$(o.slides,obj).children().size();var w=$(o.slides,obj).children().outerWidth()+o.padding;var h=$(o.slides,obj).children().outerHeight()+o.padding;var c=Math.ceil($(o.slides,obj).children().size()/i);var pd=o.padding/2;var p=0;var u=false;var n=0;var pt=0;var os=i*c-s;var params={};if(o.vertical===true){w=h;}
if(o.showPagination){var buttons=s/i;$(obj).append('<ul class="pagination">');$(o.slides,obj).children().each(function(){if(n<buttons){$(o.pagination,obj).append('<li><a rel="'+(n+1)+'" href="#" >'+(n+1)+'</a></li>');n=n+1;}else{n=0;return false;}
$(o.pagination+' li a:eq(0)',obj).parent().addClass('active');});}
if(o.vertical===true){$(o.container,obj).css({height:(w*i)});$(o.slides,obj).css({height:(s*w)});}else{$(o.container,obj).css({width:(w*i)});$(o.slides,obj).css({width:(s*w)});}
$(o.slides,obj).children().each(function(){if(o.vertical===true){$(this).css({position:'absolute',top:p+pd,display:'block'});}else{$(this).css({position:'absolute',left:p+pd,display:'block'});}
p=p+w;});$(o.slides,obj).children().each(function(){pt=pt+1;if(pt<i+1){params[o.vertical?'top':'left']=(-w*pt)+pd-(w*os);$(o.slides,obj).children(':eq('+(s-pt)+')').css(params);}
if(pt===i+2){pt=0;return false;}});$('.next',obj).click(function(){if(u===false){animate('next',true);if(o.autoStart){clearInterval(sliderIntervalID);}}
return false;});$('.previous',obj).click(function(){if(u===false){animate('prev',true);if(o.autoStart){clearInterval(sliderIntervalID);}}return false;});$(o.pagination+' li a',obj).click(function(){if($(this).parent().hasClass('active')){return false;}
else{t=$(this).attr('rel');$(o.pagination+' li a',obj).parent().siblings().removeClass('active');$(this).parent().addClass('active');animate('fade',t);if(o.autoStart){clearInterval(sliderIntervalID);}}return false;});if(o.autoStart){sliderIntervalID=setInterval(function(){if(u===false){animate('next',true);}},o.autoStart);}
function current(t){if(t===c+1){t=1;}
if(t===0){t=c;}
$(o.pagination+' li a',obj).parent().siblings().removeClass('active');$(o.pagination+' li a[rel="'+(t)+'"]',obj).parent().addClass('active');}
function animate(dir,clicked){u=true;switch(dir){case'next':t=t+1;m=(-(t*w-w)*i);current(t);params[o.vertical?'top':'left']=m;$(o.slides,obj).animate(params,o.slidespeed,function(){if(t===c+1){t=1;params[o.vertical?'top':'left']=0;$(o.slides,obj).css(params,function(){$(o.slides,obj).animate(params);});$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=(w*pt)+pd;$(o.slides,obj).children(':eq('+pt+')').css(params);params[o.vertical?'top':'left']=-(w*(pt+os+1)-pd);$(o.slides,obj).children(':eq('+(s-(pt+1))+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
if(t===c){$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=w*(s+pt+os)+pd;$(o.slides,obj).children(':eq('+(pt)+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
if(t===2){$(o.slides,obj).children().each(function(){pt=pt+1;if(pt<i+1){params[o.vertical?'top':'left']=((w*s)+pd)-(w*pt);$(o.slides,obj).children(':eq('+(s-pt)+')').css(params);}else{pt=0;return false;}});}
u=false;});break;case'prev':t=t-1;m=(-(t*w-w)*i);current(t);params[o.vertical?'top':'left']=m;$(o.slides,obj).animate(params,o.slidespeed,function(){if(t===0){t=c;params[o.vertical?'top':'left']=-w*(s-i)-(w*os);$(o.slides,obj).css(params);$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=w*(s+pt+os)+pd;$(o.slides,obj).children(':eq('+pt+')').css(params);params[o.vertical?'top':'left']=(s*w)+pd-(pt*w)-w;$(o.slides,obj).children(':eq('+((s-1)-pt)+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
if(t===2){$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=(w*pt)+pd;$(o.slides,obj).children(':eq('+pt+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
if(t===1){$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=-(w*pt)-w+pd-(w*os);$(o.slides,obj).children(':eq('+((s-1)-pt)+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
u=false;});break;case'fade':t=[t]*1;m=(-(t*w-w)*i);current(t);$(o.slides,obj).children().fadeOut(o.fadespeed,function(){params[o.vertical?'top':'left']=m;$(o.slides,obj).css(params);$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=(pt*w)+pd;$(o.slides,obj).children(':eq('+pt+')').css(params);params[o.vertical?'top':'left']=w*(s-pt)-w+pd;$(o.slides,obj).children(':eq('+((s-1)-pt)+')').css(params);}else{pt=0;return false;}
pt=pt+1;});if(t===c){$(o.slides,obj).children().each(function(){if(pt<i){params[o.vertical?'top':'left']=w*(s+pt+os)+pd;$(o.slides,obj).children(':eq('+(pt)+')').css(params);}else{pt=0;return false;}
pt=pt+1;});}
if(t===1){$(o.slides,obj).children().each(function(){pt=pt+1;if(pt<i+1){params[o.vertical?'top':'left']=-(w*pt)+pd-(w*os);$(o.slides,obj).children(':eq('+(s-pt)+')').css(params);}else{pt=0;return false;}});}
$(o.slides,obj).children().fadeIn(o.fadespeed);u=false;});break;default:break;}}});};})(jQuery);