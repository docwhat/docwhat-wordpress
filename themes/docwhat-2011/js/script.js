(function() {

  jQuery(function() {
    $("form[role='search'] input[type='text']").attr('placeholder', 'Search for it!');
    return $("form[role='search'] input[type='submit']").hide();
  });

}).call(this);
