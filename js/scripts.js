jQuery(document).ready(function($){

  // Ajax form data and show
  $('.entries .entry').click(function(){
    // $('.form-data').slideUp();
    var id = $(this).data('id');

    var data = {
  		'action': 'my_action',
  		'entry_id': id
  	};
  	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.get(ajax_object.ajax_url, data, function(response) {
  		$('.form-data .data').html(response);
      $('.form-data').slideDown();
      $('html, body').animate({
    		scrollTop: $('#entry').offset().top
    	}, 100, 'linear');
  	});
  });

  $('.form-data .postbox .close').click(function(){
    $('.form-data').slideUp();
  });

  // Filters for Entries
  $('.form-select').change(function(){
    var formID = $(this).val();
    var currentLocation = window.location;
    var href = currentLocation.href;
    var url = new URL(href);
    if(formID == 0) {
      url.searchParams.delete('form_id', formID);
    } else {
      url.searchParams.set('form_id', formID);
    }

    window.location.replace(url.href);
  });

  // Delete entry check
  $('a.delete').click(function(e){
    e.preventDefault();
    var confirmDelete = confirm('Are you sure you want to delete this record? This can not be reversed.');
    if (confirmDelete) {
      var deleteLink = $(this).data('id');
      var currentLocation = window.location;
      var href = currentLocation.href;
      var url = new URL(href);
      url.searchParams.set('deleteRecord', deleteLink);

      window.location.replace(url.href);
    } else {

    }
  });

});
