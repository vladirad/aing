jQuery(document).ready(function ($) {
  function submitAiNicknameForm(form, page = 1, onFinish) {
    form.find(".is-invalid").removeClass("is-invalid");
    form.find(".invalid-feedback").html("");
    const _loading = form.parent().find(".ai-nickname-loading");

    if (form.hasClass("is-loading")) {
      onFinish(false);
      return;
    }

    const _results = form.parent().find(".ai-nickname-form-results");

    if (page < 2) {
      _results.html("");
      _loading.find(".more").hide();
      _loading.find(".your").show();
    } else {
      _loading.find(".your").hide();
      _loading.find(".more").show();
    }

    _loading.show();

    const formData = new FormData(form[0]);
    formData.append("action", "ai_generate_nickname");
    formData.append("security", aiNickname.nonce);
    formData.append("formId", form.data("id"));

    if (page) {
      formData.append("page", page.toString());
    }

    form.addClass("is-loading");

    $.ajax({
      url: aiNickname.ajaxUrl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        form.parent().find(".ai-nickname-loading").hide();

        if (typeof onFinish === "function") {
          onFinish(response.success);
        }

        if (response.success) {
          if (page > 1) {
            const newResults = $(response.data).find("#nicknames-list>div");
            _results.find("#nicknames-list").append(newResults);
          } else {
            _results.html(response.data);
          }
        } else if (
          typeof response.data === "object" &&
          Array.isArray(response.data.errors)
        ) {
          response.data.errors.forEach(function (error) {
            const _field = form.find('[name="' + error.name + '"]');
            _field.find("+.invalid-feedback").append(error.error + "<br />");
            _field.addClass("is-invalid");
          });
        }

        form.removeClass("is-loading");
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX error: ", textStatus, ", ", errorThrown);

        if (typeof onFinish === "function") {
          onFinish();
        }

        form.removeClass("is-loading");
      },
    });
  }

  $(".ai-nickname-form").submit(function (e) {
    e.preventDefault();

    const _button = $(this).find("button");
    _button.addClass("is-loading");

    submitAiNicknameForm($(this), 1, function () {
      _button.removeClass("is-loading");
    });
  });

  $(document).on("click", ".ai-nickname-results .sortingbutton", function () {
    const _parent = $(this).closest(".ai-nickname-results");
    const _list = _parent.find(".ai-nickname-results-list");
    _parent.find(".sortingbutton").removeClass("active");
    $(this).addClass("active");

    const _order = $(this).data("order");

    let sortedNicknames = _list.find(" > div");

    if (_order === "alphabet") {
      sortedNicknames = sortedNicknames.sort(function (a, b) {
        var nicknameA = $(a).data("nickname").toUpperCase();
        var nicknameB = $(b).data("nickname").toUpperCase();
        return nicknameA < nicknameB ? -1 : nicknameA > nicknameB ? 1 : 0;
      });
    } else if (_order === "random") {
      sortedNicknames = sortedNicknames.sort(() => 0.5 - Math.random());
    }

    _list.empty().append(sortedNicknames);
  });

  $(document).on("click", "#nickname-generator-copy", function () {
    const _this = $(this);
    _this.addClass("is-loading");

    const nicknameTitles = document.querySelectorAll(
      ".ai-nickname-form-results .nickname-title"
    );

    const combinedText = Array.from(nicknameTitles)
      .map((element) => element.innerText)
      .join("\n");

    const copyText = document.createElement("textarea");

    document.body.appendChild(copyText);

    copyText.value = combinedText;

    copyText.select();
    copyText.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(copyText.value);

    document.body.removeChild(copyText);

    setTimeout(function () {
      _this.removeClass("is-loading");
    }, 300);
  });

  $(document).on(
    "click",
    ".ai-nickname-results .nickname-generator-more",
    function () {
      const _button = $(this);
      const nextPage = +(_button.attr("data-page") || 0) + 1;

      _button.addClass("is-loading");

      submitAiNicknameForm(
        _button.closest(".ai-nickname-form-wrapper").find(".ai-nickname-form"),
        nextPage,
        function (success = false) {
          _button.removeClass("is-loading");

          if (success) {
            if (nextPage >= 3) {
              _button.parent().hide();
            } else {
              _button.attr("data-page", nextPage);
            }
          }
        }
      );
    }
  );

  $(".ai-nickname-loading").each(function () {
    const _element = $(this);

    let dots = 0;

    setInterval(function () {
      _element.find(".dots").text(".".repeat(dots));
      dots = (dots + 1) % 4;
    }, 500);
  });
});
