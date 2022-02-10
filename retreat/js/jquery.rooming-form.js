var formID = 0,
    formArea = '',
    programStartDate = '',
    programEndDate = '',
    programStartDate2 = '',
    programEndDate2 = '',
    startDate = '',
    endDate = '',
    startDate2 = '',
    endDate2 = '',
    programStartDateInput = '',
    programEndDateInput = '',
    programStartOffsetInput = '',
    programEndOffsetInput = '',
    programTotalOffsetInput = '',
    hotelDatesArea = '',
    hotelStartDateInput = '',
    hotelEndDateInput = '',
    programTimesArea = '',
    programStartTime = '',
    programEndTime = '',
    hotelAvailabilityArea = '',
    inputRoomType = '',
    beddingArea = '',
    occupancyArea = '',
    datesArea = '',
    guestTypeArea = '',
    guestTypeItem = '',
    roomTypeAndRateArea = '',
    roomTypeArea = '',
    roomDescArea = '',
    inputTotal = '',
    hiddenTotal = '',
    guestAdultField = '',
    guestTeenField = '',
    guestChildField = '',
    guestInfantField = '',
    basicDates = [],
    packageDates = [],
    roomHeaderBlock = "<header>Room</header>",
    roomContentArea = '',
    dateFormat = '',
    guestTypeInput = '',
    staffArea = '',
    guestCount = 0,
    taxArea = '',
    guestMinValidationMessage = '',
    guestMaxValidationMessage = '',
    messageInfoArea = '',
    promoArea = '',
    promoMessage = '',
    promoPrice = '',
    applyPromoButton = '',
    validationInProgress = false,
    validationResponse = false,
    validationResult = null,
    newRoomInProgress = false,
    newRoomResult = null,
    isAdmin = false,
    earlyBirdDiscountArea = '',
    surchargeArea = '',
    additionalBedOptions = '',
    privateBabysitter = '';

jQuery(document).ready(function() {

    isAdmin = $("input[name='is_admin']").val();
    formID = $("#reservation-form .rooming-form").eq(0).data("room_id");
    roomingFormInit(formID);
    console.log(params['promo'])
    if (params['promo']){
        $("#reservation-form .rooming-form input.input-promo").val(params['promo']);
    }

    $(document).on("click", "#add-room", function() { addNewRoom() });

    $("#submit").on("click", function() {

        if (newRoomInProgress && newRoomResult === null) {
            newRoomInProgress = false;
            newRoomResult = false;
            setTimeout(function() {
                $("#submit").trigger('click');
            }, 500);
            return true;
        } else if (!newRoomInProgress && newRoomResult === null) {
            addNewRoom(true);
            setTimeout(function() {
                $("#submit").trigger('click');
            }, 500);
            return true;
        }
        if (newRoomResult === false) {
            newRoomResult = null;
        }

        var result = newRoomResult;

        if (result !== null && typeof result === "object") {
            result.always(function(responce, textStatus) {
                if (textStatus == 'success') {
                    window.location.href = $("#reservation-form").attr("action");
                }
            });
        }
    });

    $(document).on("change paste keyup", ".input-total", function() {
        var value = $(this).val();
        $("#input-total-" + formID).val(value);
    });

    $(document).on("click", "button.remove-room", function() {
        var id = $(this).data("room_id");

        removeRoom(id);
    });
});

var roomInit = function(id) {
    formArea = $("#room-" + id);
    programStartDateInput = formArea.find(".program-start-date");
    programEndDateInput = formArea.find(".program-end-date");
    hotelDatesArea = formArea.find(".hotel-dates-area");
    hotelStartDateInput = formArea.find(".hotel-start-date");
    hotelEndDateInput = formArea.find(".hotel-end-date");
    programTimesArea = formArea.find(".program-times-area");
    programStartTime = formArea.find(".program-start-time");
    programEndTime = formArea.find(".program-end-time");
    programStartOffsetInput = formArea.find(".program-start-offset");
    programEndOffsetInput = formArea.find(".program-end-offset");
    programTotalOffsetInput = formArea.find(".program-total-offset");
    hotelAvailabilityArea = formArea.find(".hotel-availability-area");
    inputRoomType = formArea.find(".input-room-type");
    beddingArea = formArea.find(".bedding-area");
    occupancyArea = formArea.find(".occupancy-area");
    datesArea = formArea.find(".dates-area");
    roomTypeAndRateArea = formArea.find(".room-type-and-rate-area");
    guestTypeArea = formArea.find(".guest-types-area");
    guestTypeItem = guestTypeArea.find(".guest-type");
    roomTypeArea = formArea.find(".room-type-area");
    roomDescArea = formArea.find(".room-description-area");
    inputTotal = formArea.find(".input-total");
    hiddenTotal = $("#input-total-" + id);
    guestAdultField = formArea.find(".guest-type-Adult");
    guestTeenField = formArea.find(".guest-type-Teen");
    guestChildField = formArea.find(".guest-type-Child");
    guestInfantField = formArea.find(".guest-type-Infant");
    roomContentArea = $("#rooming-form");
    guestTypeInput = formArea.find(".guest-type-input");
    staffArea = formArea.find(".staff-area");
    taxArea = formArea.find(".tax-area");
    messageInfoArea = formArea.find(".message-info");
    promoArea = formArea.find(".promo-area");
    promoMessage = formArea.find(".promo-message");
    applyPromoButton = formArea.find(".apply-promo-button");
    promoPrice = formArea.find(".promo-price");
    earlyBirdDiscountArea = formArea.find(".early-bird-discount-area");
    surchargeArea = formArea.find(".surcharge-area");
    additionalBedOptions = formArea.find(".additional-bed-options");
    privateBabysitter = formArea.find(".private-babysitter-checkbox");

    dateFormat = programStartDateInput.data("format");

    basicDates = $("#basic-dates").val();
    basicDates = basicDates.split(",");
    packageDates = $("#package-dates").val();
    packageDates = packageDates.split(",");

    generateDates();
};

var generateDates = function () {
    startDate = '';
    endDate = '';
    guestCount = 0;

    var formattedDate = '';

    data = {};
    for (var key = 0; key < packageDates.length; key++) {
        formattedDate = $.datepicker.formatDate(dateFormat, new Date(packageDates[key]));
        data[formattedDate] = formattedDate;

        if (startDate == '' || new Date(startDate) > new Date(formattedDate)) {
            startDate = formattedDate;
        }
        if (endDate == '' || new Date(endDate) < new Date(formattedDate)) {
            endDate = formattedDate;
        }
    }
    packageDates = data;

    programStartDate = new Date(startDate);
    programEndDate = new Date(endDate);
    programStartDate2 = new Date(programStartDate);
    programEndDate2 = new Date(programEndDate);

    var data = {};
    for (var key = 0; key < basicDates.length; key++) {
        formattedDate = $.datepicker.formatDate(dateFormat, new Date(basicDates[key]));
        data[formattedDate] = formattedDate;

        if (startDate == '' || new Date(startDate) > new Date(formattedDate)) {
            startDate = formattedDate;
        }
        if (endDate == '' || new Date(endDate) < new Date(formattedDate)) {
            endDate = formattedDate;
        }
    }
    basicDates = data;

    startDate = new Date(startDate);
    endDate = new Date(endDate);
    startDate2 = new Date(startDate);
    endDate2 = new Date(endDate);
};

var roomingAccordionInit = function() {
    roomContentArea.accordion({
        heightStyle: "content",
        activate: function(event, ui) {
            var title = ui.newHeader[0];
            if (title && title.hasAttribute("aria-controls")) {
                formID = $("#" + $(title).attr("aria-controls")).data("room_id");
                roomingFormInit(formID);
                spinnerInit(formID);
                formArea.find("em.invalid").remove();
                formArea.find(".invalid").removeClass("invalid");
                formArea.find(".state-error").removeClass("state-error");
            }
        }
    });
};

var roomingAccordionRefresh = function() {
    var tabId = parseInt($("#reservation-form .rooming-form").size()) - 1;

    roomContentArea.accordion("refresh");
    roomContentArea.accordion("option", "active", tabId);
};

var spinnerInit = function() {

    guestTypeInput.spinner({
        min: 0,
        max: 0,
        icons: { down: "icon-prepend fa fa-minus", up: "icon-append fa fa-plus" },
        create: function(event, ui) {
            var element = $(event.target),
                min = element.data("min"),
                max = element.data("max");

            element.parents().eq(0).find("span.ui-icon").text("");
            element.spinner("option", {
                min: min,
                max: max
            });
        },
        stop: function(event) {
            var button = $(event.toElement);

            if (!button.hasClass("state-disabled")) {
                setGuestRange(true);
            } else {
                messageInfoArea.html(guestMaxValidationMessage);
            }
        }
    });
    guestTypeInput.prop("disabled", true);
};

var roomingFormInit = function (id) {

    if (id != undefined) {
        formID = id;
    }

    roomInit(formID);
    roomingAccordionInit();
    spinnerInit();

    guestTypeArea.find("label.guest-type").each(function() {
        enableItem($(this));
    });

    minMaxAvailable();
    

    $(document).on("click", applyPromoButton.selector, function() {
        var data = ajaxReservationData("base/applyPromo");
        data.success(function(responce) {
            if (responce.total != undefined && parseInt(responce.total) != 0) {
                inputTotal.val(responce.total);
                hiddenTotal.val(responce.total);
            }

            var promoLabel = promoArea.find("label.input");
            var promoInput = promoArea.find("label.input");
            promoPrice.text("");
            promoMessage.text("");
            if (responce.promo != undefined && parseInt(responce.promo) != 0) {

                if (promoLabel.hasClass("state-error")) {
                    promoLabel.removeClass("state-error");
                }
                if (promoInput.hasClass("invalid")) {
                    promoInput.removeClass("invalid");
                }

                promoArea.find("label.input").addClass("state-success");
                promoArea.find("input.input-promo").addClass("valid");

                promoPrice.text("$" + responce.promo);
                if (responce.message) {
                    promoMessage.text(responce.message);
                }
                applyPromoButton.text("Applied");
                promoLabel.css({opacity: '0.5'});
            } else {

                if (promoLabel.hasClass("state-success")) {
                    promoLabel.removeClass("state-success");
                }
                if (promoInput.hasClass("valid")) {
                    promoInput.removeClass("valid");
                }
                

                promoArea.find("label.input").addClass("state-error");
                promoArea.find("input.input-promo").addClass("invalid");

                promoPrice.text("");
                promoMessage.text("");
                if (responce.error) {
                    promoMessage.text(responce.error);
                }
                applyPromoButton.text("Apply");
                promoLabel.css({opacity: '1'});
            }
        });
    });
  var setDatePickers = function (){
        programStartDateInput.datepicker({
            dateFormat: dateFormat,
            defaultDate: programStartDate,
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            minDate: programStartDate,
            maxDate: programEndDate2,
            numberOfMonths: [1,2],
            beforeShow: function(input) {
                var selector = $(input).attr("id");
                selector = '#' + selector;
    
                setTimeout(function() {
                    datePickerInfo(selector);
                }, 10);
            },
            beforeShowDay: function(date) { return beforeShowDay(date) },
            onSelect: function(selectedDate) {
                onSelect('start', programStartDateInput, programEndDateInput, selectedDate);
            },
            onClose: function() {
                if ($(this).valid()) {
                    if (programEndDateInput.val()) {
                        setGuestRange();
                    }
                    if (!hotelStartDateInput.val()) {
                        var formattedDate = $.datepicker.formatDate(dateFormat, new Date($(this).val()));
                        hotelStartDateInput.val(formattedDate);
                        roomTypeAvailability();
                    }
                }
            }
        });
        if (!programStartDateInput.val()) {
            setTimeout(function() {
                programStartDateInput.focus();
            }, 500);
        }
    
        programEndDateInput.datepicker({
            dateFormat: dateFormat,
            defaultDate: programStartDate2,
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            minDate: programStartDate2,
            maxDate: programEndDate,
            numberOfMonths: [1,2],
            beforeShow: function(input) {
                var selector = $(input).attr("id");
                selector = '#' + selector;
    
                setTimeout(function() {
                    datePickerInfo(selector);
                }, 10);
            },
            beforeShowDay: function(date) { return beforeShowDay(date) },
            onSelect: function (selectedDate) {
                onSelect('end', programStartDateInput, programEndDateInput, selectedDate);
            },
            onClose: function() {
                if ($(this).valid()) {
                    if (programStartDateInput.val()) {
                        setGuestRange();
                    }
    
                    if (!hotelEndDateInput.val()) {
                        var formattedDate = $.datepicker.formatDate(dateFormat, new Date($(this).val()));
                        hotelEndDateInput.val(formattedDate);
                        roomTypeAvailability();
                        
                    }
                    
                }
            }
        });
    
        hotelStartDateInput.datepicker({
            dateFormat: dateFormat,
            defaultDate: startDate,
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            minDate: startDate,
            maxDate: endDate2,
            numberOfMonths: [1,2],
            beforeShow: function(input) {
                var selector = $(input).attr("id");
                selector = '#' + selector;
    
                setTimeout(function() {
                    datePickerInfo(selector);
                }, 10);
            },
            beforeShowDay: function(date) { return beforeShowDay(date) },
            onSelect: function(selectedDate) {
                onSelect('start', hotelStartDateInput, hotelEndDateInput, selectedDate);
            },
            onClose: function() {
                if (hotelStartDateInput.val()) {
                    if (!hotelEndDateInput.val()) {
                        setTimeout(function () {
                            hotelEndDateInput.focus();
                        }, 300);
                    } else {
                        setGuestRange();
                        roomTypeAvailability();
                        
                    }
                }
                
            }
        });
    
        hotelEndDateInput.datepicker({
            dateFormat: dateFormat,
            defaultDate: startDate2,
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            minDate: startDate2,
            maxDate: endDate,
            numberOfMonths: [1,2],
            beforeShow: function(input) {
                var selector = $(input).attr("id");
                selector = '#' + selector;
    
                setTimeout(function() {
                    datePickerInfo(selector);
                }, 10);
            },
            beforeShowDay: function(date) { return beforeShowDay(date) },
            onSelect: function (selectedDate) {
                onSelect('end', hotelStartDateInput, hotelEndDateInput, selectedDate);
            },
            onClose: function() {
                if (hotelEndDateInput.val()) {
                    if (hotelStartDateInput.val()) {
                        setGuestRange();
                    } else {
                        setTimeout(function () {
                            hotelStartDateInput.focus();
                        }, 300);
                    }
                    roomTypeAvailability();
                    
                }
                
                
            }
        });
    }
    
    setDatePickers();



    $(document).on("change", inputRoomType.selector, function() {
        
        
        showPrice();
        generatePersonRange([]);
        messageInfoArea.html("");
        beddingArea.html("");
        staffArea.html("");
        occupancyArea.html("");
        formArea.find(".guest-participants-area").css({display: 'none'});

        var data = ajaxReservationData("base/getOccupancies");
        data.success(function(responce) {

            if (responce.occupancy) {
                occupancyArea.html(responce.occupancy);
            }
            if (responce.description) {
                roomDescArea.html(responce.description);
            }
            if (responce.tax != undefined) {
                setTax(responce.tax);
            }
            if (responce.early_bird_discount != undefined) {
                setEarlyBirdDiscount(responce.early_bird_discount);
            }
            if (responce.surcharge != undefined) {
                setSurcharge(responce.surcharge);
            }
            if (responce.total && responce.guest_prices) {
                showPrice(responce.total, responce.guest_prices);
            } else {
                showPrice();
            }
            if (responce.range && !$.isEmptyObject(responce.range)) {
                formArea.find(".guest-participants-area").css({display: 'block'});
                generatePersonRange(responce.range);
            }

            guestTypeArea.find("label.guest-type").each(function() {
                enableItem($(this), true);
            });
        });
    });

    $(document).on("change", occupancyArea.selector + " .occupancy input", function() {
        showPrice();
        generatePersonRange([]);
        beddingArea.html("");
        staffArea.html("");
        formArea.find(".guest-participants-area").css({display: 'block'});

        if ($(this).val() == "Double Occupancy") {
            guestCount = 0;
            guestTypeInput.each(function() {
                var value = $(this).val() * 1;
                guestCount = guestCount + value;
            });

            if (guestCount == 0) {
                guestAdultField.val(2);
            }
        }

        setGuestRange();
    });

    $(document).on("change", guestTypeArea.selector + " select", function() {

        guestTypeArea.find(".state-error").removeClass("state-error");
        guestTypeArea.find("em.invalid").remove();

        setGuestRange();
    });

    $(document).on("change", staffArea.selector + " input", function() {


        setGuestRange();
    });

    $(document).on("change", beddingArea.selector + " input:radio", function() {
        if ($(this).valid()) {
            $(this).parents("label").eq(0).find("em.invalid").remove();
        }
    });

    $(document).on("blur", guestInfantField.selector, function() {
        setTimeout(function() {
            beddingArea.find("input[type='radio']").eq(0).focus();
        }, 300);
    });

    $(document).on("change", programStartTime.selector + ", " + programEndTime.selector, function() {
        setGuestRange();
    });

    $(document).on("change", hotelAvailabilityArea.selector + " input:radio", function() {
        var selected = $(this).val();

        if (selected == 1) {
            hotelDatesArea.css({display: "block"});
            roomTypeAndRateArea.css({display: "block"});
            formArea.find(".guest-participants-area").css({display: 'none'});
            roomDescArea.html("");
        } else {
            hotelStartDateInput.val("");
            hotelEndDateInput.val("");
            inputRoomType.prop("selectedIndex", 0);
            inputRoomType.prop("disabled", true);
            inputRoomType.parents("label").eq(0).addClass("state-disabled");
            hotelDatesArea.css({display: "none"});
            roomTypeAndRateArea.css({display: "none"});
            messageInfoArea.html("");
            beddingArea.html("");
            staffArea.html("");
            occupancyArea.html("");
            formArea.find(".guest-participants-area").css({display: 'block'});
            setGuestRange();
        }
    });
    $(document).on("change", additionalBedOptions.selector + " input:checkbox", function() {
        setGuestRange();
    });
};

var removeRoom = function(id) {
    if (id == undefined) {
        id = formID;
    }

    var request = ajaxReservationData("base/removeRoom", {form_id: id});
    request.success(function(responce) {

        if (responce.form_id != undefined) {

            var tabId = 0,
                i = 0,
                form_id = responce.form_id;

            $("#reservation-form .rooming-form").each(function() {
                i = i + 1;
                if ($(this).data("room_id") == form_id) {
                    tabId = i;
                }
            });
            if (responce.result == false) {
                tabId = parseInt(tabId) - 1;
                if (tabId < 0) {
                    tabId = 0;
                }
            }

            roomContentArea.accordion({
                active: tabId,
                heightStyle: "content",
                activate: function(event, ui) {
                    var title = ui.newHeader[0];
                    if (title && title.hasAttribute("aria-controls")) {

                        $("#header-form-" + id).remove();
                        $("#room-" + id).remove();

                        //guestTypeInput.spinner("destroy");
                        roomingFormInit(form_id);
                        roomingAccordionRefresh();
                    }
                }
            });
        }
    });
};

var roomTypeAvailability = function(availability) {

    if (availability == undefined) {
        availability = true;
    }

    if (availability) {
        inputRoomType.prop("disabled", false);
        inputRoomType.parents("label").eq(0).removeClass("state-disabled");
    } else {
        var value = inputRoomType.find("option:disabled").val();

        inputRoomType.val(value).trigger("change");
        inputRoomType.prop("disabled", true);
        inputRoomType.parents("label").eq(0).addClass("state-disabled");

        occupancyArea.html("");
        beddingArea.html("");
        roomDescArea.html("");
    }
};

var setHeader = function() {

    var header = formArea.prev("header");
    var numb = formID + 1;

    if (header.find("span.numb").size()) {
        header.find("span.numb").text(numb);
    } else {
        header.attr("id", "header-form-" + formID);
        header.append(" #<span class='numb'>" + numb + "</span><button id='remove-room-" + formID + "'"
        + "type='button' data-room_id='" + formID + "' class='remove-room button button-secondary'>X</button>");
    }
};

var addNewRoom = function(returnStatus) {
    if (!newRoomInProgress) {
        newRoomInProgress = true;
    }

    var returnData = 'undefined';
    //var validation = validateForm();
    if (!validationInProgress && validationResponse) {
        var validation = validationResult;
        validationResponse = false;
        newRoomResult = null;
        validationResult = null;
    } else {
        validateForm();
        setTimeout(function() {
            addNewRoom(returnStatus);
        }, 500);
        return true;
    }

    var lastFormId = $("#reservation-form .rooming-form").last().data("room_id");
    var lastTabId = parseInt($("#reservation-form .rooming-form").size()) - 1;
    var lastpromo = $("#reservation-form .rooming-form input.input-promo").last().val();
    lastFormId = parseInt(lastFormId);
    formID = parseInt(formID);

    if (validation) {
        if (lastFormId == formID) {
            var request = ajaxReservationData("base/addNewRoom");
            if (returnStatus == true) {
                returnData = request;
            } else {
                request.success(function(responce) {
                    if (responce.html && responce.formId) {

                        //guestTypeInput.spinner("destroy");
                        roomContentArea.append(roomHeaderBlock + responce.html);

                        roomingFormInit(responce.formId);
                        roomingAccordionRefresh();

                        setHeader();
                        inputRoomType.prop("required", false);
                        $("#reservation-form .rooming-form#room-"+responce.formId+" input.input-promo").val(lastpromo);
                        $( applyPromoButton.selector ).trigger( "click" );
                    }
                });
            }
        } else {
            roomContentArea.accordion({
                active: lastTabId,
                heightStyle: "content",
                activate: function(event, ui) {
                    var title = ui.newHeader[0];
                    if (title && title.hasAttribute("aria-controls")) {
                        formID = $("#" + $(title).attr("aria-controls")).data("room_id");
                        roomingFormInit(formID);
                        spinnerInit(formID);
                        formArea.find("em.invalid").remove();
                        formArea.find(".invalid").removeClass("invalid");
                        formArea.find(".state-error").removeClass("state-error");
                        $("#reservation-form .rooming-form#room-"+formID+" input.input-promo").val(lastpromo);
                        $( applyPromoButton.selector ).trigger( "click" );
                        
                    }
                }
            });
            if (returnStatus == true) {
                returnData = false;
            }
        }
    } else {
        if (returnStatus == true) {
            returnData = false;
        }
    }
    if (returnData != 'undefined') {
        newRoomInProgress = false;
        newRoomResult = returnData;
        return returnData;
    }
};

var validateForm = function() {
    validationInProgress = true;
    var validation = true,
        validElements = {},
        elementsName = {},
        activeElements = formArea.find("input, textarea, select").not(".disabled input, .disabled textarea, .disabled select").not("[disabled]");

    activeElements.each(function() {
        var name = $(this).attr("name").replace(/\[.*$/, ""),
            validElement = true;

        if (elementsName[name]) {
            if (validElement = $(this).valid()) {
                validElements[name] = true;
            }
        } else {
            elementsName[name] = name;
            if (validElement = $(this).valid()) {
                validElements[name] = true;
            } else {
                validElements[name] = false;
            }
        }

        if (!validElement) {
            formArea.find(".state-error").removeClass("state-error");
            formArea.find("em.invalid").remove();
        }
    });

    activeElements.each(function() {
        if (validation == true) {
            var name = $(this).attr("name").replace(/\[.*$/, "");

            if (validation == true && validElements[name] == false) {
                $(this).valid();
                validation = false;
            }
        }
    });

    formArea.removeClass("state-success");

    if (validation) {
        var request = ajaxReservationData("base/validateForm");

        request.success(function(responce) {
            if ($.isEmptyObject(responce.errors)) {
                validationInProgress = false;
                validationResponse = responce;
                validationResult = true;
                return true;
            } else {
                $.each(responce.errors, function(name, message) {
                    var selector = $("[name^='" + name + "[" + formID + "]" + "']");
                    var size = selector.size();

                    if (size == 1) {
                        var label = selector.parents("label").eq(0);

                        if (!selector.attr("id")) {
                            selector.uniqueId();
                        }
                        var id = selector.attr("id");

                        selector.removeClass("valid");
                        label.removeClass("state-success");

                        selector.addClass("invalid");
                        label.addClass("state-error");

                        if ($("em[for='" + id + "']").size() != 0) {
                            $("em[for='" + id + "']").each(function() {
                                $(this).remove();
                            });
                        }

                        var insertAfterElement = selector;
                        if (selector.parents("label").eq(0).size() > 0) {
                            insertAfterElement = selector.parents("label").eq(0);
                        }
                        $("<em for='" + id + "' class='invalid'>" + message + "</em>").insertAfter(insertAfterElement);
                    } else if (size > 1) {
                        selector.each(function(){
                            var label = $(this).parents("label").eq(0);

                            $(this).removeClass("valid");
                            label.removeClass("state-success");

                            $(this).addClass("invalid");
                            label.addClass("state-error");
                        });
                        if (message) {
                            var element = selector.last();

                            if ($("em[for='" + name + "']").size() != 0) {
                                $("em[for='" + name + "']").each(function() {
                                    $(this).remove();
                                });
                            }
                            var insertAfterElement = selector;
                            if (selector.parents("label").eq(0).size() > 0) {
                                insertAfterElement = selector.parents("label").eq(0);
                            }
                            $("<em for='" + name + "' class='invalid'>" + message + "</em>").insertAfter(insertAfterElement);
                        }
                    }

                });
                validationInProgress = false;
                validationResponse = true;
                validationResult = false;
                return true;
            }
        });
    } else {
        validationInProgress = false;
        return false;
    }
};

var guestParticipantsValidator = function() {
    var isValid = true,
        isFilled = false,
        filledElement = null,
        successLabelClass = "state-success",
        successInputClass = "valid",
        errorLabelClass = "state-error",
        errorInputClass = "invalid",
        errorCustomMessage = function(text) {
            return "<em class='invalid'>" + text + "</em>";
        };

    guestTypeArea.find("em").remove();
    guestTypeArea.find("." + errorLabelClass).removeClass(errorLabelClass);
    guestTypeArea.find("." + errorInputClass).removeClass(errorInputClass);
    guestTypeArea.find("." + successLabelClass).removeClass(successLabelClass);
    guestTypeArea.find("." + successInputClass).removeClass(successInputClass);

    guestTypeInput.each(function() {
        var value = $(this).val();
        if (value != '' && value != '0' && isFilled == false) {
            filledElement = $(this);
            isFilled = true;
        }
    });

    if (filledElement == null) {
        isValid = false;
        guestTypeArea.append(errorCustomMessage("One of the fields should be filled"));
        guestTypeInput.each(function() {
            $(this).addClass(errorInputClass);
            $(this).parents("label").eq(0).addClass(errorLabelClass);
        });
    } else {
        guestTypeInput.each(function() {
            if ($(this).data("min") != undefined && $(this).data("max") != undefined) {
                var min = parseInt($(this).data("min")),
                    max = parseInt($(this).data("max")),
                    value = parseInt($(this).val());

                if (value < min || value > max) {
                    $(this).after(errorCustomMessage("min: " + min + " max: " + max));
                    guestAdultField.addClass(errorInputClass);
                    guestAdultField.parents("label").eq(0).addClass(errorLabelClass);
                    isValid = false;
                }
            }
        });
    }

    if (isValid) {
        guestTypeInput.each(function() {
            $(this).addClass(successInputClass);
            $(this).parents("label").eq(0).addClass(successLabelClass);
        });
    }

    return isValid;
};

var showPrice = function(total, guestPrices) {
    if (guestPrices) {
        $.each(guestPrices, function(type, price) {
            guestTypeArea.find(".guest-price-" + type).text("$" + price);
        });
    } else {
        guestTypeArea.find("label.guest-type input").each(function() {
            $(this).val(0);
        });
        guestTypeArea.find(".guest-price").each(function () {
            $(this).text("$0");
        });
    }

    if (total) {
        inputTotal.val(total);
        hiddenTotal.val(total);
    } else {
        inputTotal.val("");
        hiddenTotal.val("");
    }
};

var setRoomTypeArea = function() {
    var request = ajaxReservationData("base/getRoomTypeArea", {form_id: formID});
    request.success(function(responce) {
        if (responce.html) {
            roomTypeAndRateArea.html(responce.html);
            roomInit(formID);
            enableItem(roomTypeArea.find('label.select'));

            setTimeout(function() {
                inputRoomType.focus();
            }, 300);
        }
        updateTotal();
    });
};

var setGuestRange = function(checkRange) {
    
    
    showPrice(0, []);
    generatePersonRange([]);
    messageInfoArea.html("");

    var currentGuestCount = 0;
    if (checkRange != undefined) {
        guestTypeInput.each(function() {
            var value = $(this).val() * 1;
            currentGuestCount = currentGuestCount + value;
        });
    }
    

    var data = ajaxReservationData("base/getGuestRange");

    data.success(function(responce) {

        if (beddingArea.length) {
            if (responce.html != null && responce.html != '') {
                beddingArea.html(responce.html);

                if (beddingArea.find("input:radio").size() == 1) {
                    beddingArea.find("input:radio").prop("checked", true);
                }
            }
        }

        if (responce.program_times_html) {
            programTimesArea.html(responce.program_times_html);
        }

        if (responce.staff_html != undefined) {
            staffArea.html(responce.staff_html);
        }

        if (responce.tax != undefined) {
            setTax(responce.tax);
        }

        if (responce.early_bird_discount != undefined) {
            setEarlyBirdDiscount(responce.early_bird_discount);
        }

        if (responce.surcharge != undefined) {
            setSurcharge(responce.surcharge);
        }

        if (responce.promo_price != undefined && parseInt(responce.promo_price) != 0 ) {
            promoPrice.text("$" + responce.promo_price);
        }
        
        if (responce.promo_message) {
            promoMessage.text(responce.promo_message);
        }
        if ((responce.promo_price == undefined || parseInt(responce.promo_price) == 0) && responce.promo_error != '' ) {
            promoPrice.text("");
            promoMessage.text("");
            promoMessage.text(responce.promo_error);
        }

        guestMinValidationMessage = '';
        if (responce.guest_min_validation_message != undefined) {
            guestMinValidationMessage = responce.guest_min_validation_message;
            messageInfoArea.html(guestMinValidationMessage);
        }
        guestMaxValidationMessage = '';
        if (responce.guest_max_validation_message != undefined) {
            guestMaxValidationMessage = responce.guest_max_validation_message;
        }

        if (responce.occupancy != undefined) {
            var input = occupancyArea.find("input[value='" + responce.occupancy + "']");
            if (input.size() > 0) {
                input.prop("checked", true);
            }
        }

        if (responce.occupancy_html) {
            occupancyArea.html(responce.occupancy_html);
        }

        if (responce.occupancy_description_html) {
            roomDescArea.html(responce.occupancy_description_html);
        }

        if (responce.room_type) {
            inputRoomType.val(responce.room_type).trigger("change");
        }

        showPrice(responce.total, responce.guest_prices);

        guestCount = 0;
        guestTypeInput.each(function() {
            var value = $(this).val() * 1;
            guestCount = guestCount + value;
        });

        generatePersonRange(responce.range);
    });
    
};

var updateTotal = function() {
    
    var data = ajaxReservationData("base/getTotal");
    data.success(function(responce) {
        if (responce.total && responce.guest_prices) {
            showPrice(responce.total, responce.guest_prices);
        }
        if (responce.surcharge) {
            setSurcharge(responce.surcharge);
        }
    });
};

var setTax = function(tax) {
    taxArea.find("span.tax-text").text(tax);
    taxArea.find("input[name*='tax']:hidden").val(tax);
};

var setEarlyBirdDiscount = function(price) {

    var container = earlyBirdDiscountArea.find("span.early-bird-discount-text");
    var oldPrice = parseFloat(container.text());

    if (parseInt(container.text()) != 0 || parseInt(price) != 0) {
        earlyBirdDiscountArea.show();

        price = parseFloat(price);
        price = price.toFixed(2);

        container.text(price);
    } else {
        earlyBirdDiscountArea.hide();
    }
};

var setSurcharge = function(price) {

    var container = surchargeArea.find("span.surcharge-text");
    var oldPrice = parseFloat(container.text());

    if (parseInt(container.text()) != 0 || parseInt(price) != 0) {
        surchargeArea.show();

        taxArea.find("input[name*='surcharge']:hidden").val(price);
        price = parseFloat(price);
        price = price.toFixed(2);

        container.text(price);
    } else {
        surchargeArea.hide();
    }
};

var generatePersonRange = function(range) {
    if (range) {
        $.each(range, function(key, value) {
            var input = guestTypeArea.find(".guest-type-" + key);
            var inputVal = parseInt(input.val());
            var label = input.parents("label.guest-type").eq(0);

            if (value[1] == 0) {
                input.spinner({
                    min: 0,
                    max: 0
                });

                input.data("min", 0);
                input.data("max", 0);
                input.val(0);

            } else {
                enableItem(label, true);
                input.spinner({
                    min: value[0],
                    max: value[1]
                });

                input.data("min", value[0]);
                input.data("max", value[1]);

                if (isNaN(inputVal)) {
                    input.val(0);
                    inputVal = 0;
                }

                if (parseInt(input.val()) > value[1]) {
                    input.val(value[1]);
                } else if (inputVal == 0
                    && (occupancyArea.find("input:checked").size() > 0 || occupancyArea.html().trim() == '')
                    && parseInt(value[0]) > 0
                ) {
                    input.val(value[0]);
                }
            }
        });
    }

    minMaxAvailable();
    
};

var enableItem = function(labelElement, enable) {

    var spinner = labelElement.find(".ui-spinner");
    var input = labelElement.children("input, select, textarea").eq(0);
    var error = labelElement.children("em.invalid");

    if (enable == undefined) {
        enable = true;
    }

    if (labelElement.hasClass("state-error")) {
        labelElement.removeClass("state-error");
    }
    if (input.hasClass("invalid")) {
        input.removeClass("invalid");
    }
    if (error.size()) {
        error.remove();
    }

    if (enable) {
        if (spinner.size() && spinner.hasClass("ui-spinner-disabled")) {
            spinner.removeClass("ui-spinner-disabled").removeClass("ui-state-disabled");
            spinner.find(".ui-spinner-button").removeClass("ui-button-disabled").removeClass("ui-state-disabled");
        }
        if (labelElement.hasClass("state-disabled")) {
            labelElement.removeClass("state-disabled");
        }
        if (input.prop("disabled")) {
            input.prop("disabled", false);
        }
    } else {
        if (spinner.size()) {
            spinner.addClass("ui-spinner-disabled").addClass("ui-state-disabled");
            spinner.find(".ui-spinner-button").addClass("ui-button-disabled").addClass("ui-state-disabled");
        }
        if (!labelElement.hasClass("state-disabled")) {
            labelElement.addClass("state-disabled");
        }
        if (!input.prop("disabled")) {
            input.prop("disabled", true);
        }
    }
};

var beforeShowDay = function(date) {

    date = new Date(date);

    if ((isAdmin != 1) && (date.getDay() == 6)) {
        return [false];
    }

    var formattedDate = $.datepicker.formatDate(dateFormat, date);

    if (basicDates[formattedDate]) {
        return [true, "hotel-date", basicDates[formattedDate]];
    } else if (packageDates[formattedDate]) {
        return [true, "package-date", packageDates[formattedDate]];
    } else {
        return [true, "", ""];
    }
};

var onSelect = function(point, startInput, endInput, selectedDate) {

    var formattedDate = $.datepicker.formatDate(dateFormat, new Date(selectedDate));

    var start = new Date(selectedDate);
    /*if (isAdmin != 1) {
        start.setDate(start.getDate() + 1);
    }*/

    if (point == 'start') {
        endInput.datepicker('option', 'minDate', start);

        startInput.val(formattedDate);

    } else if (point == 'end') {
        var end = new Date(selectedDate);
        /*if (isAdmin != 1) {
            end.setDate(end.getDate() - 1);
        }*/

        startInput.datepicker('option', 'maxDate', end);
        endInput.val(formattedDate);

        if (startInput.datepicker("getDate") === null) {
            programStartDateInput.datepicker("setDate", start);
        }
    }
};

var minMaxAvailable = function() {
    guestTypeItem.each(function() {

        var input = $(this).find("input.guest-type-input"),
            value = parseInt(input.val()),
            val = parseInt(input.val()),
            min = parseInt(input.data("min")),
            max = parseInt(input.data("max")),
            buttonUp = $(this).find(".fa-plus"),
            buttonDown = $(this).find(".fa-minus");

        if (isNaN(val)) {
            val = 0;
        }

        if (!buttonUp.hasClass("state-disabled") && val == max) {
            buttonUp.addClass("state-disabled");
        } else if (buttonUp.hasClass("state-disabled") && val != max) {
            buttonUp.removeClass("state-disabled");
        }
        if (!buttonDown.hasClass("state-disabled") && val == min) {
            buttonDown.addClass("state-disabled");
        } else if (buttonDown.hasClass("state-disabled") && val != min) {
            buttonDown.removeClass("state-disabled");
        }
    });
};

var ajaxReservationData = function(url, data) {
    
    
    if (data == undefined) {
        guestTypeInput.prop("disabled", false);

        var roomTypeDisabled = inputRoomType.prop("disabled");
        if (roomTypeDisabled) {
            inputRoomType.prop("disabled", false);
        }

        data = formArea.find("input, textarea, select").serialize();
        data += "&is_admin=" + encodeURIComponent(($('[name="is_admin"]').size() == 1) ? $('[name="is_admin"]').val() : 0);

        guestTypeInput.prop("disabled", true);

        if (roomTypeDisabled) {
            inputRoomType.prop("disabled", true);
        }
    }
    

    return $.ajax({
        url: baseUrl + url + window.location.search,
        type: "GET",
        dateType: "json",
        data: data
    });
};

$.fn.changeVal = function (v) {
    return $(this).val(v).trigger("change");
};

var datePickerInfo = function(selector) {
    var datePicker = $(selector),
        message = datePicker.data("info");
        $("#ui-datepicker-div").css({width: '520px'});
        $(".ui-datepicker-group").css({width: '260px',float:'left'});
        $(".ui-datepicker-row-break").css({clear: 'both'});

    if (message) {
        
        $("#ui-datepicker-div").append("<div id='ui-datepicker-div-message'>" + message + "</div>");
    }
};

var params = function() {
    function urldecode(str) {
        return decodeURIComponent((str+'').replace(/\+/g, '%20'));
    }

    function transformToAssocArray( prmstr ) {
        var params = {};
        var prmarr = prmstr.split("&");
        for ( var i = 0; i < prmarr.length; i++) {
            var tmparr = prmarr[i].split("=");
            params[tmparr[0]] = urldecode(tmparr[1]);
        }
        return params;
    }

    var prmstr = window.location.search.substr(1);
    return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
}();


