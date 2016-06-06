(function(window,jQuery){
  
  var $ = jQuery;
  var lastAction = Math.round(new Date().getTime()/1000);
  
  var sendAjaxRequest = function(postId, field, val) {
    
    var obj = {
      "ID": postId,
      "field": field,
      "value": val,
      "action": "wsml_update_product"
    };
    
    //Send ajax data.
    $.ajax({
      url: window.ajaxurl,
      data: obj,
      method: "post"
    })
    .done(function(resp){
      console.log(resp);
      var sel = $('tr[data-id=' + postId + ']');
      $(sel).addClass("success");
      window.setTimeout(function(){
        $(sel).removeClass("success");
      },1000);
    })
    .error(function(err){
      console.error(err);
      alert("There was an AJAX error updating a record.");
    });
    
  };
  
  var updateAjaxData = function(e) {
    
    lastAction = Math.round(new Date().getTime()/1000);
    
    var postId = $(this).attr('data-id') || false;
    var field = $(this).attr('data-field') || false;
    var val = $(this).val();
    
    if (! postId || ! field ) {
      console.error("Could not send update AJAX, missing postId or field");
      return false;
    }
    
    if ( val < 40 && field !== "stock" && val !== "" ) {
      if(! confirm("The price you entered is small. Are you sure $" + val + " is correct?") ) {
        return false;
      }
    }
    
    sendAjaxRequest(postId,field,val);
    
  };
  
    
  var debounce = function(func, wait, immediate) {
      var timeout;
      return function() {
          var context = this, args = arguments;
          var later = function() {
              timeout = null;
              if (!immediate) func.apply(context, args);
          };
          var callNow = immediate && !timeout;
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
          if (callNow) func.apply(context, args);
      };
  };
  
  var searchProducts = debounce(function() {
    var val = $(this).val().toUpperCase(), tv, m;
    var i=0; max = window.stocklist.length;
    var matches = [];
    
    for (i;i<max;i++) {
      tv = "!@#!@#!-" + window.stocklist[i].post.post_title.toUpperCase() + " " + window.stocklist[i].sku.toUpperCase();
      m = tv.indexOf(val);
      if ( m > 0 ) {
        //Match.
        matches.push(window.stocklist[i].id);
      }
    }
    
    if ( matches.length < 1 ) {
      //Show tablle.
      $("#stock-table tbody tr").show();
      return true;
    }
    
    $("#stock-table tbody tr").hide();
    for ( var i=0; i<matches.length; i++ ) {
      $("tr[data-id=" + matches[i] + "]").show();
    }
    
  }, 250);
  
  var checkIdle = function() {
   
    var now = Math.round(new Date().getTime()/1000);
	var diff = now - lastAction;
    if ( diff >= 300 ) { //5 minutes
      window.location.reload();
    }
    
  }

  
  $(document).ready(function(){
    
    $('[data-ajax-update]').on('change', updateAjaxData);
    $('[data-filter=stock]').on('keyup', searchProducts);
    $('[data-filter=stock]').on('change', searchProducts);
    $('[data-filter=stock]').on('blur', searchProducts);
	
    //Prevent sign outs.
    window.setInterval(checkIdle,60000); //Once per minute.
    
  });
  
})(window,jQuery);