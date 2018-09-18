(function($) {

$logoMovile = $(".logo-movile");
$containerMenuMovile = $("#menu-movile");
$listMenu  = $(".container__menu-movile > ul li");

var cont =0;

$listMenu.on('click',function(){

	cont=0;
	$containerMenuMovile.hide('slow');
	
});


$logoMovile.on('click',function(){

	if(cont==0){
		$containerMenuMovile.show('slow');
		cont++;

	}else{
		$containerMenuMovile.hide('slow');
		cont=0;
	}
		
});



 let functionScroll = (start, end) => {
   $(start).on('click', function() {
     var posicion = $(end).offset().top;
     $('html, body').animate({
       scrollTop: posicion
     }, 1000);
   });
 };


$(".inicio").click(function(){
	console.log("click");
})


functionScroll('.inicio','.header-principal');
functionScroll('.inicio-logo-menu','.header-principal');
functionScroll('#etapas','.etapas__deportivas');
functionScroll('#requisitos','.req-preinscribete');
functionScroll('#app','.download-app');
functionScroll('#noticias','.news-and-news');
functionScroll('#consultas','.consultas');


})(jQuery);