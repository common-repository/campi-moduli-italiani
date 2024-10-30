/*global
 event, gcmi_ajax, jQuery, wp
 */
jQuery(document).ready(function ($) {
  "use strict";
  const {
    __,
    _x,
    _n,
    _nx
  } = wp.i18n;
  var scegli = "<option value=\"\">" +
    __("Select...", "campi-moduli-italiani") + "</option>";
  var attendere = "<option value=\"\">" +
    __("Wait...", "campi-moduli-italiani") + "</option>";
  var comune = "";
  var provincia = "";
  var regione = "";
  var gcmi_comu_mail_value = "";
  var gcmi_istance_kind = "";
  var gcmi_istance_filtername = "";
  var myID = "";
  var regione_desc = "";
  var provincia_desc = "";
  var comune_desc = "";
  var predefiniti = "";
  // da versione 2.0.0 .
  var choichesLoaded = typeof (window.Choices === "function");
  var el = "";
  var elP = "";
  var elC = "";
  var kindString = "gcmi_kind";
  var fNameString = "gcmi_filtername";
  var regString = "gcmi_regione";
  var provString = "gcmi_province";
  var regDescString = "gcmi_reg_desc";
  var provDescString = "gcmi_prov_desc";
  var comDescString = "gcmi_comu_desc";
  var choicesjsClassString = "choicesjs-select";

  var comString = "gcmi_comuni";
  var iconString = "gcmi_icon";
  var targaString = "gcmi_targa";
  var infoString = "gcmi_info";

  var targetNodes;
  var MutationObserver;
  var myObserver;
  var obsConfig;

  $("select[id$='" + regString + "']").val("");
  $("select[id$='" + regString + "']").prop("disabled", false);
  $("select[id$='" + provString + "']").html(scegli);
  $("select[id$='" + provString + "']").attr("disabled", "disabled");
  $("select[id$='" + comString + "']").html(scegli);
  $("select[id$='" + comString + "']").attr("disabled", "disabled");
  $("[id$='" + iconString + "']").hide();
  $("input[id$='" + targaString + "']").val("");
  $("input[id$='gcmi_mail']").val("");

  /* compatibilità con JQuery Nice Select */
  function mutationHandler(mutationRecords) {
    var target = mutationRecords[0].target;
    $(target).niceSelect("update");
  }

  if (typeof $.fn.niceSelect !== "undefined") {
    targetNodes = $("select[id*='gcmi'][style='display: none']");
    MutationObserver =
      window.MutationObserver ||
      window.WebKitMutationObserver;
    myObserver = new MutationObserver(mutationHandler);
    obsConfig = {
      attributes: true,
      characterData: true,
      childList: true,
      subtree: true
    };
    //--- Add a target node to the observer. Can only add one node at a time.
    targetNodes.each(function () {
      myObserver.observe(this, obsConfig);
    });
  }
  /* FINE compatibilità con JQuery Nice Select */

  // Seleziono una regione.
  $("select[id$='" + regString + "']").on("change",
    function () {
      window.MyPrefix = this.id.substring(
        0,
        (this.id.length - regString.length));
      regione = $("select#" + window.MyPrefix + regString + " option:selected")
        .attr("value");
      regione_desc = $("select#" +
        window.MyPrefix + regString + " option:selected")
        .text();
      gcmi_istance_kind = $("input#" + window.MyPrefix + kindString)
        .attr("value");
      gcmi_istance_filtername = $("input#" + window.MyPrefix +
        fNameString)
        .attr("value");
      $.ajax({
        beforeSend: function() {
          $(this).attr("disabled", "disabled");
          // imposto gli altri campi
          $("input#" + window.MyPrefix + regDescString).val(regione_desc);
          $("input#" + window.MyPrefix + provDescString).val("");
          $("input#" + window.MyPrefix + comDescString).val("");

          // disabilito provincia e comune
          elP = $("select#" + window.MyPrefix + provString);
          elC = $("select#" + window.MyPrefix + comString);
          switch (regione) {
            case "":
              elP.html(scegli);
              elC.html(scegli);
              break;
            case "00":
              elP.html(attendere);
              elC.html(attendere);
              break;
            default:
              elP.html(attendere);
              elC.html(scegli);
          }
          elP.attr("disabled", "disabled");
          elC.attr("disabled", "disabled");
          if (choichesLoaded && elP.hasClass(choicesjsClassString)) {
            $(elP).data("choicesjs")
              .setChoices(Array.from($(elP)[0].options), "value", "label", true);
            $(elP).data("choicesjs").disable();
          }

          if (choichesLoaded && elC.hasClass(choicesjsClassString)) {
            $(elC).data("choicesjs")
              .setChoices(Array.from($(elC)[0].options), "value", "label", true);
            $(elC).data("choicesjs").disable();
          }
        },
        data: {
          action: "the_ajax_hook_prov",
          codice_regione: regione,
          gcmi_filtername: gcmi_istance_filtername,
          gcmi_kind: gcmi_istance_kind,
          nonce_ajax: gcmi_ajax.nonce
        },
        success: function (data) {
          switch (regione) {
            case "":
              // deve disabilitare tutte e due e inserire scegli e non procedere
              elP = $("select#" + window.MyPrefix + provString);
              elC = $("select#" + window.MyPrefix + comString);
              elP.html(scegli);
              elC.html(scegli);
              elP.attr("disabled", "disabled");
              elC.attr("disabled", "disabled");
              return;
            case "00":
              el = $("select#" + window.MyPrefix + comString);
              break;
            default:
              el = $("select#" + window.MyPrefix + provString);
          }
          el.prop("disabled", false);
          el.html(data);
          if (choichesLoaded && el.hasClass(choicesjsClassString)) {
            $(el).data("choicesjs")
              .setChoices(Array.from($(el)[0].options), "value", "label", true);
            $(el).data("choicesjs").enable();
          }
          hideSingleProvince(window.MyPrefix);
          $(this).prop("disabled", false);
        },
        type: "POST",
        url: gcmi_ajax.ajaxurl
      });
      $("#" + window.MyPrefix + iconString).hide();
      $("#" + window.MyPrefix + infoString).hide();
    }
  );

  // Seleziono una provincia.
  $("select[id$='" + provString + "']").on("change",
    function () {
      window.MyPrefix = this.id.substring(
        0,
        (this.id.length - provString.length)
        );
      provincia = $("select#" + window.MyPrefix +
        provString + " option:selected")
        .attr("value");
      provincia_desc = $("select#" + window.MyPrefix +
        provString + " option:selected")
        .text();
      gcmi_istance_kind = $("input#" + window.MyPrefix + kindString)
        .attr("value");
      gcmi_istance_filtername = $("input#" + window.MyPrefix +
        fNameString)
        .attr("value");
      $.ajax({
        beforeSend: function () {
          $(this).attr("disabled", "disabled");
          $("input#" + window.MyPrefix + provDescString).val(provincia_desc);
          $("input#" + window.MyPrefix + comDescString).val("");

          elC = $("select#" + window.MyPrefix + comString);
          elC.html(attendere);
          elC.attr("disabled", "disabled");
          if (choichesLoaded && elC.hasClass(choicesjsClassString)) {
            $(elC).data("choicesjs")
              .setChoices(Array.from($(elC)[0].options), "value", "label", true);
            $(elC).data("choicesjs").disable();
          }
        },
        data: {
          action: "the_ajax_hook_comu",
          codice_provincia: provincia,
          gcmi_filtername: gcmi_istance_filtername,
          gcmi_kind: gcmi_istance_kind,
          nonce_ajax: gcmi_ajax.nonce
        },
        success: function (data) {
          if ("" !== provincia) {
            elC.html(data);
            elC.prop("disabled", false);
          } else {
            elC.html(scegli);
          }

          if (choichesLoaded && elC.hasClass(choicesjsClassString)) {
            $(elC).data("choicesjs")
              .setChoices(Array.from($(elC)[0].options), "value", "label", true);
            $(elC).data("choicesjs").enable();
          }
          $(this).prop("disabled", false);
        },
        type: "POST",
        url: gcmi_ajax.ajaxurl
      });
      $("#" + window.MyPrefix + iconString).hide();
      $("#" + window.MyPrefix + infoString).hide();
    }
  );

  // Seleziono un comune.
  $("select[id$='" + comString + "']").on("change",
    function () {
      var gcmi_comu_form_value = "";
      window.MyPrefix = this.id.substring(
        0,
        (this.id.length - comString.length));
      comune = $("select#" + window.MyPrefix +
        comString + " option:selected")
        .attr("value");
      $.ajax({
        beforeSend: function () {
          gcmi_istance_kind = $("input#" + window.MyPrefix +
            kindString)
            .attr("value");
          gcmi_istance_filtername = $("input#" + window.MyPrefix +
            fNameString)
            .attr("value");
          comune_desc = $("select#" + window.MyPrefix +
            comString + " option:selected")
            .text();
          $("input#" + window.MyPrefix + comDescString).val(comune_desc);
        },
        data: {
          action: "the_ajax_hook_targa",
          codice_comune: comune,
          gcmi_filtername: gcmi_istance_filtername,
          gcmi_kind: gcmi_istance_kind,
          nonce_ajax: gcmi_ajax.nonce
        },
        success: function (data) {
          $("input#" + window.MyPrefix + targaString).val(data);
          if (regione !== "00") {
            gcmi_comu_form_value = $(
              "select#" + window.MyPrefix + comString + " option:selected")
              .text() + " (" + $(
              "input#" + window.MyPrefix + targaString).val() + ")";
          } else {
            gcmi_comu_form_value = $(
              "select#" + window.MyPrefix + comString + " option:selected")
              .text() + " - (" + __("sopp.", "campi-moduli-italiani") + ")" +
              " (" + $("input#" + window.MyPrefix + targaString).val() + ")";
          }
          $("input#" + window.MyPrefix + "gcmi_formatted")
            .attr("value", gcmi_comu_form_value);
          $("#" + window.MyPrefix + infoString).hide();
          if ($("select#" + window.MyPrefix + comString + " option:selected")
            .val() !== "") {
            $("#" + window.MyPrefix + iconString).show();
          } else {
            $("#" + window.MyPrefix + iconString).hide();
          }
        },
        type: "POST",
        url: gcmi_ajax.ajaxurl
      });
    }
  );

  // Click sull'icona per le info.
  $("[id$='" + iconString + "']").on("click",
    function () {
      window.MyPrefix = event.target.id.substring(
        0, (event.target.id.length - iconString.length));
      comune = $("select#" + window.MyPrefix + comString + " option:selected")
        .attr("value");
      $.ajax({
        data: {
          action: "the_ajax_hook_info",
          codice_comune: comune,
          gcmi_filtername: gcmi_istance_filtername,
          nonce_ajax: gcmi_ajax.nonce
        },
        success: function (data) {
          var trimmed = data.trim();
          if ("" !== trimmed) {
            $("#" + window.MyPrefix + infoString).html(trimmed);
            $("#" + window.MyPrefix + infoString).dialog({
              autoOpen: false,
              closeText: __("Close", "campi-moduli-italiani"),
              height: "auto",
              hide: "puff",
              maxWidth: 600,
              minWidth: 300,
              show: "slide",
              title: __("Municipality details", "campi-moduli-italiani"),
              width: "auto"
            });
            $("#" + window.MyPrefix + infoString).dialog("open");
          }
        },
        type: "POST",
        url: gcmi_ajax.ajaxurl
      });
    }
  );

  // tooltip.
  $("[id^='TTVar']").on("mouseover",
    function () {
      event.target.id.tooltip();
    }
  );

  // Imposta i valori di default.
  async function setDefault(CurPrefix, predefiniti) {
    var response;
    try {
      response = await $("select#" + CurPrefix + regString)
        .find("option[value=\"" + predefiniti.substring(0, 2) + "\"]")
        .prop("selected", true)
        .trigger("change");
    } catch (e) {
      console.log(e);
    }
    if ($("select#" + CurPrefix + regString).val() !== "00") {
      try {
        response = await $("select#" + CurPrefix + provString)
          .find("option[value=\"" + predefiniti.substring(2, 5) + "\"]")
          .prop("selected", true)
          .trigger("change");
      } catch (e) {
        console.log(e);
      }
    }
    try {
      response = await $("select#" + CurPrefix + comString)
        .find("option[value=\"" + predefiniti.substring(5) + "\"]")
        .prop("selected", true)
        .trigger("change");
    } catch (e) {
      console.log(e);
    }
  }

  $("select[id$='" + comString + "']").each(
    function () {
      var CurPrefix = this.id.substring(0, (this.id.length - comString.length));
      predefiniti = $(this).attr("data-prval");
      if (typeof predefiniti !== typeof undefined && predefiniti !== false) {
        setDefault(CurPrefix, predefiniti);
      }
    }
  );

  function hideSingleRegion() {
    // se sono solo 2 le opzioni, nella regione seleziono la seconda
    var RegOps = 0;
    $("select[id$='" + regString + "']").each(function () {
      window.MyPrefix = this.id.substring(
        0,
        (this.id.length - regString.length)
        );
      RegOps = $(this).children("option").length;
      if (2 === RegOps) {
        $("#" + window.MyPrefix + regString + " option")
          .eq(1)
          .prop("selected", true);
        $("#" + window.MyPrefix + regString).trigger("change");
        $("label[for='" + window.MyPrefix + regString + "'").hide();
        $(this).hide();
      } else {
        $(this).show();
      }
    });
  }
  hideSingleRegion();

  function hideSingleProvince(MyPrefix) {
    // nasconde la selezione delle province se è inutile
    // (solo 1 provincia selezionabile)
    var ProOps = 0;
    ProOps = $("#" + MyPrefix + provString).children("option").length;
    if (2 === ProOps) {
      $("#" + MyPrefix + provString + " option").eq(1).prop("selected", true);
      $("#" + MyPrefix + provString).trigger("change");
      $("label[for='" + MyPrefix + provString).hide();
      $("#" + MyPrefix + provString).hide();
    } else {
      $("#" + MyPrefix + provString).show();
    }
  }
});