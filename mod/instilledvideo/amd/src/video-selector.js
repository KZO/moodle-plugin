define(['jquery'], function($) {
  return {
    init: function() {
      window.addEventListener('message', event => {
        $('#id_mediumid').val(event.data);
      });
    }
  };
});
