function addProductToQuotations(url, productId, productAttributeId, addButton) {
  $.ajax({
    type: "POST",
    url: url,
    data: {
      productId: productId,
      id_product_attribute: productAttributeId,
    },
    success: function (resp) {
      resp = JSON.parse(resp);
      setTimeout(() => {
        if (resp.success) {
          $(".count-quotation").text(resp.quotes);
          addButton.prop("disabled", true);

          var dropdownMenu = $(".dropdown-menu");
          var productLink = $("<a>", {
            class: "dropdown-item",
            href: "#",
            text: resp.product_name,
          });
          dropdownMenu.append(productLink);

          alert(resp.message);
        } else {
          alert(resp.message);
        }
      }, 1000);
    },
  });
  return false;
}

$(document).ready(function () {
  $(".add-to-quote").on("click", function () {
    var addButton = $(this);
    addProductToQuotations(
      addButton.data("url"),
      addButton.data("product"),
      addButton.data("product-attribute"),
      addButton
    );
  });
});
