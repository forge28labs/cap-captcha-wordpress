(function () {
  function init() {
    if (
      typeof frmFrontForm === "undefined" ||
      typeof frmFrontForm.validateFormSubmit !== "function"
    ) {
      setTimeout(init, 100);
      return;
    }

    var orig = frmFrontForm.validateFormSubmit;
    frmFrontForm.validateFormSubmit = function (object) {
      var errors = orig.call(this, object);

      object.querySelectorAll("cap-widget").forEach(function (widget) {
        if (widget.token) return;

        var container = widget.closest(".frm_form_field");
        if (!container || !container.id) return;

        var match = container.id.match(/frm_field_(\d+)_container/);
        if (match) {
          errors[match[1]] = "";
        }
      });

      return errors;
    };
  }

  document.addEventListener(
    "solve",
    function (e) {
      var widget = e.target;
      if (widget.tagName !== "CAP-WIDGET") return;

      var container = widget.closest(".frm_form_field");
      if (!container) return;

      container.classList.remove("frm_blank_field", "has-error");
      var err = container.querySelector(".frm_error");
      if (err) err.remove();
    },
    true,
  );

  init();
})();
