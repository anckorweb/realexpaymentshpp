/**

 * NOTICE OF LICENSE

 *

 * This file is licenced under the Software License Agreement.

 * With the purchase or the installation of the software in your application

 * you accept the licence agreement.

 *

 * You must not modify, adapt or create derivative works of this source code

 *

 *  @author    Coccinet

 *  @copyright 2017 Coccinet

 *  @license   LICENSE.txt

 */
 
$(document).ready(function(){
	if (!!$.prototype.fancybox)
		$("a.iframerealex").fancybox({
			type: "iframe",
      fitToView : true,
		  modal: true,
		  topRatio : 0
    });
   
  
  // store the currently selected tab in the hash value
  $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {    
    var id = $(e.target).attr("href").substr(1);
    //console.log(id);
    window.location.hash = id;
  });
  
  // on load of the page: switch to the currently selected tab
  var hash = window.location.hash;
  $('#myTab a[href="' + hash + '"]').tab('show');

  //Delayed Settlement mandatory

  var $lab = $('#REALEXPAYMENTS_PENDING_STATUS').parent().prev(); 
      $lab.removeClass('required')

  if ($('#settle_delay').is(":checked"))
  {
    $lab.addClass('required')
  }else{
    $lab.removeClass('required')
  }

  $("input[name='REALEXPAYMENTS_AUTO_SETTLE']").on('change', function() {
     if ($('#settle_delay').is(":checked"))
    {
      $lab.addClass('required')
    }else{
      $lab.removeClass('required')
    }

  })

  if($("#iframerealex").length != 0) {
    window.scrollTo(0,$('#iframerealex').parent().parent().parent().parent().offset().top);
  }

})
