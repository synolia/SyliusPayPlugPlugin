const Payment = {
  options: {
    trigger: ".payment-method-choice",
    completeInfo: {
      modal: ".oney-complete-info-popin",
      area: ".ui.steps + .ui.grid",
    },
  },
  init(options) {
    if (typeof options === "undefined") {
      options = this.options;
    }
    this.options = $.extend(true, {}, options);
    Payment.toggleGateway();
    if (typeof completeInfoRoute !== "undefined") {
      Payment.modalAppear();
    }
    Payment.tabs();
    $(window).on("resize", () => {
      setTimeout(Payment.tabs, 100);
    });
    Payment.tabsHandler();
    Payment.applePayHandler();
  },
  toggleGateway() {
    const paymentMethodInputId = $(this.options.trigger).data("payment-input-id");
    const checkedPaymentMethodInput = $(`#${paymentMethodInputId}:checked`);
    if (checkedPaymentMethodInput.length) {
      $(`.payment-method-choice[data-payment-input-id="${paymentMethodInputId}"]`).show();
    }
    $("input[id*=sylius_checkout_select_payment]").on("change", (event) => {
      const clickedPaymentMethodId = $(event.currentTarget).attr("id");
      $(".payment-method-choice").slideUp();
      $(`.payment-method-choice[data-payment-input-id="${clickedPaymentMethodId}"]`).slideDown();
    });
  },
  tabs() {
    if (window.innerWidth <= 991) {
      $(".oney-payment-choice__item").hide();
      setTimeout(() => {
        $.each($(".oney-payment-choice__input"), (k, el) => {
          if ($(el).is(":checked")) {
            $(el).parent().show();
            $(`a.tablink[data-id=${$(el).val()}]`).addClass("active");
          }
        });
      }, 1);
    } else {
      $(".oney-payment-choice__item").show();
      $("a.tablink").removeClass("active");
    }
  },
  tabsHandler() {
    $.each($("a.tablink"), (k, el) => {
      $(el).click(function (evt) {
        $("a.tablink").removeClass("active");
        $(this).addClass("active");
        $(".oney-payment-choice__item").hide();
        $(`#${$(this).data("id")}`).show();
        $(`input[value=${$(this).data("id")}`).prop("checked", true);
      });
    });
  },
  applePayHandler() {
    $(".payment-item .checkbox input:radio").on('change', this.onPaymentMethodChoice);
    $(document).on('click', "apple-pay-button", this.onApplePayButtonClick);
  },
  onPaymentMethodChoice(event) {
    const isApplePay = $(event.currentTarget).closest('.checkbox-applepay').length;
    const applePayButton = $("apple-pay-button");
    const nextStepButton = $('form[name="sylius_checkout_select_payment"] .select-payment-submit #next-step');

    console.log(isApplePay);

    if (isApplePay) {
      if (applePayButton.length) {
        applePayButton.addClass('enabled');
      }

      nextStepButton.replaceWith(
        $("<span/>", {
          id: 'next-step',
          class: 'ui large disabled icon labeled button',
          html: nextStepButton.html()
        })
      );
    } else {
      if (applePayButton.length) {
        applePayButton.removeClass('enabled');
      }

      nextStepButton.replaceWith(
        $("<button/>", {
          type: 'submit',
          id: 'next-step',
          class: 'ui large primary icon labeled button',
          html: nextStepButton.html()
        })
      );
    }
  },
  onApplePayButtonClick(event)
  {
    const applePayButton = $(event.currentTarget);

  },
  modalAppear() {
    const self = this;
    let path = completeInfoRoute;
    $.get(path).then((data) => {
      $("body .pusher").append("<div class='overlay'></div>");
      $(self.options.completeInfo.area).addClass("inactive");
      $(self.options.completeInfo.area).parent().append(data);
      self.modalEvents();
    });
  },
  modalFadeaway() {
    $(this.options.completeInfo.modal).fadeOut(300, () => {
      $(this.options.completeInfo.area).removeClass("inactive");
      $(".overlay").hide();
    });
  },
  modalSubmit(evt) {
    const self = this;
    evt.preventDefault();
    $(evt.currentTarget).addClass("loading");

    $.ajax({
      method: "post",
      url: completeInfoRoute,
      data: $(evt.currentTarget).serialize(),
      success: function (res) {
        if (Array.isArray(res)) {
          $(`${self.options.completeInfo.modal}__content`).fadeOut(() => {
            $(`${self.options.completeInfo.modal}__success`).show();
          });
          setTimeout(() => {
            self.modalFadeaway();
          }, 2500);
        } else {
          $(self.options.completeInfo.modal).html(res);
        }
        self.modalEvents();
      },
      error: function (res) {
        console.log(res);
      },
    });
  },
  modalEvents() {
    $(".close").on("click", () => {
      this.modalFadeaway();
    });
    $("form[name=form]").on("submit", (e) => {
      this.modalSubmit(e);
    });
  },
};

const onDocumentLoad = function (event) {
  Payment.init();

  $('form[name="sylius_checkout_select_payment"] button[type="submit"]').on("click", (event) => {
    if ($(".checkbox-oney :radio:checked").length) {
      $(".checkbox-payplug").closest(".payment-item").find(".payment-choice__input:checked").prop("checked", false);
    } else if ($(".checkbox-payplug :radio:checked").length) {
      $(".checkbox-oney").closest(".payment-item").find(".payment-choice__input:checked").prop("checked", false);
    }

    $("input#payplug_choice_card_other").attr("disabled", true);
    $('form[name="sylius_checkout_select_payment"]').submit();
  });
};

document.addEventListener("DOMContentLoaded", onDocumentLoad, false);
