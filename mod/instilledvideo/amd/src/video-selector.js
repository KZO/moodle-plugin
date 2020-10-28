define(['jquery'], function($) {
  return {
    init: function() {
      window.addEventListener('message', event => {
        if (typeof event.data === 'string' || event.data instanceof String) {
          $('#id_mediumid').val(event.data);
        }
      });
    }
  };
});
