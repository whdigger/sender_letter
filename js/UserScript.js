/*Функция перевода секунд во время*/
function her(a){
	i=0;
	dv1='';dv2='';probel='';
	while(a.substr(i,1)){
		if(a.substr(0,1)===' '){
			a=a.substr(1,100);
			continue;
		}
		switch(a.substr(i,1)){
			case ' ':{probel=i;break;}
			case ':':{if(!dv1)dv1=i;else dv2=i;break;}
		}
		i++;
	}
	len=i;
	if(!dv1&&!dv2){
		d=Math.floor(a/86400);
		a-=86400*d;
		h=Math.floor(a/3600);
		a-=3600*h;
		m=Math.floor(a/60);if(m<10){m='0'+m;}
		s=a-60*m;if(s<10){s='0'+s.toFixed(1);}
		return d+" дн. "+h+":"+m+":"+s;
	}
	else{
		if(probel)d=a.substr(0,probel)+' ';else d=0;
		h=a.substr(probel+1,dv1-probel-1);
		m=a.substr(dv1+1,dv2-dv1-1);
		s=a.substr(dv2+1,len-dv2-1);
		sec=d*86400;
		sec+=h*3600;
		sec+=m*60;
		sec+=s*1;
		return sec+" секунд";
	}
}

/* Вывод информациии о процессе */
function loadprogress (percent,secondstime){
    $('div#progressbar').progressbar({
		value: percent
	});
    
    $("div#progressbar .ui-progressbar-value").animate({
        width: percent+'%'
    }, 0);
    
	$('div#progresspercent').fadeOut(100 , function(){ $(this).html('<b>'+percent+'%</b>'); }).fadeIn( 500 );
    $('div#progresstext').fadeOut(100 , function(){ $(this).html('Приблизительное время до завершения</br><b>'+her(String(secondstime))+'</b>'); }).fadeIn( 500 );
}
/* Очистка информациии о процессе */
function clearprogress (duration){
    $('div#progress').animate({height: "hide"},
    {
      complete: function() {
        $('div#progressbar').progressbar({
    		value: 0
    	});
        $('div#progresspercent').html('');
        $('div#progresstext').html('');
        }
    });
}
/* Изменения класса */
function changeclass (addcl,datanotice){
        
    $('div#content_resp').animate({height: "hide"},
    {
      complete: function() {
                $(this).removeClass();
                $(this).html('');
                $(this).addClass(addcl);
                }
    }).fadeOut(1000);
    
    $('div#content_resp').animate({height: "show"},
    {
      complete: function() {
                $(this).html(datanotice);
                }
    }).fadeIn(1000);
    
    
}
function redirect (rd){
    if (rd) {
        window.setTimeout("window.location.href = '"+rd+"'", 5000);
    }
}
/* Обновление статуса */
function updateStatus(url,lastvalue) {
    var lastvalue = lastvalue || 0;
    $.getJSON(url, function(data) {
        if (data !== undefined) {
        	if (data.success === true){
        		if (data.percent > 0) {
  		            // Проверяем предыдущее значение с текущим для перерисовки страницы
  		            if (lastvalue !== data.percent){
            			loadprogress(data.percent,data.timeexec);
                    }
        		}
        		if (data.percent < 100)
                    t = setTimeout( function () { updateStatus(url,data.percent); }, 5000);
        	}
            else{
                $('div#progress').animate({height: "hide"},1000, function () {changeclass('flash-notice',data.notice);});
                $("#buttondel").animate({height: "hide"});
                redirect(data.redirect);
          }
        }
        else{
            if (lastvalue > 90){
                loadprogress(100,0);
                $('div#content_resp').fadeOut(100 , function(){ $(this).html('Рассылка закончена'); }).fadeIn( 500 );
                $("#buttondel").animate({height: "hide"});
                clearprogress(200);
                redirect(data.redirect);
            }
            else{
                $('div#progress').animate({height: "hide"},1000, function () {changeclass('flash-error','Произошла ошибка');});
            }
        }
    });
}