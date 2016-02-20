"use strict";
window.datesProperty = window.datesProperty || {
    dateFromLabel: 'Date from',
    errorMessage: 'attribute mus be set!',
    dateToLabel: 'Date to',
    emptyDatesMessage: 'Set at least one dates range first!',
    wrongRangeMessage: 'Days to value must be equal or greater than Days from!',
    daysFromLabel: 'Days from',
    daysToLabel: 'Days to',
    wrongDaysCount: 'Days to value must be equal or greater than Days from!',
    propertyKey: '',
    modelApplicableName: '',
    datesAlreadyExists: 'Dates range with given dates already exists!',
    daysAlreadyExists: 'Given days range already exists!'
};

(function ($) {
    const DATE_FROM_ID = 'date-from',
        DATE_FROM_NAME = 'date_from',
        DATE_TO_ID = 'date-to',
        DATE_TO_NAME = 'date_to',
        DAYS_FROM_ID = 'days-from',
        DAYS_FROM_NAME = 'days_from',
        DAYS_TO_ID = 'days-to',
        DAYS_TO_NAME = 'days_to',
        PRICE_NAME = 'price',
        ACTION_BUTTON_SELECTOR = '[data-range-action]',
        DATA_DAYS_FROM = 'dates-days-from',
        DATA_DAYS_TO = 'dates-days-to',
        DATA_DATE_FROM_TS = 'dates-date-from-ts',
        DATA_DATE_FROM_VAL = 'dates-date-from-val',
        DATA_DATE_TO_TS = 'dates-date-to-ts',
        DATA_DATE_TO_VAL = 'dates-date-to-val',
        DATA_RANGE_INDEX = 'range-index',
        DATA_RANGE_ACTION = 'range-action',
        PROPERTY_NAME = window.datesProperty.modelApplicableName + '[' + window.datesProperty.propertyKey + ']';

    var $rangesContainer = $('#ranges-container'),
        $datesGrid = $('#ranges-grid', $rangesContainer),
        $datesGridHead = $('thead tr', $datesGrid),
        $datesGridBody = $('tbody', $datesGrid),
        $datesModal = $('#add-col-modal'),
        $daysModal = $('#add-row-modal'),
        widgetIdAppendix = $('#' + DATE_FROM_ID).parents('[id^=' + DATE_FROM_ID + ']:eq(0)').attr('id').replace(DATE_FROM_ID, ''),
        $datesButton = $(ACTION_BUTTON_SELECTOR, $datesModal),
        $daysButton = $(ACTION_BUTTON_SELECTOR, $daysModal),
        $indexTr = $('td[data-' + DATA_RANGE_INDEX + ']:last'),
        index = $indexTr.length ? $indexTr.data(DATA_RANGE_INDEX) + 1 : 1,
        $inputs = {
            'datesFrom': $('<input type="hidden" value="" name="">'),
            'datesTo': $('<input type="hidden" value="" name="">'),
            'daysFrom': $('<input type="hidden" value="" name="">'),
            'daysTo': $('<input type="hidden" value="" name="">'),
            'price': $('<input type="text" value="" name="">')
        };

    //this function fires when TouchSpin::change event triggers
    $.changeSpinner = function () {
        addError($daysModal, '', true);
        var $this = $(this),
            val = parseInt($this.val());
        if ($this.attr('id') == DAYS_FROM_ID) {
            var $input = $('input#' + DAYS_TO_ID);
            if (true === isNaN(val)) {
                $input.trigger("touchspin.updatesettings", {min: 1});
                $daysButton.removeData(DATA_DAYS_FROM);
            } else {
                if ($input.val() < val) {
                    $input.val(val);
                    $daysButton.data(DATA_DAYS_TO, val);
                }
                $input.trigger("touchspin.updatesettings", {min: val});
                $daysButton.data(DATA_DAYS_FROM, val);
            }
        } else if ($this.attr('id') == DAYS_TO_ID) {
            $input = $('input#' + DAYS_FROM_ID);
            if (true === isNaN(val)) {
                $input.trigger("touchspin.updatesettings", {max: 100}); //100 is default max value for spinner config
                $daysButton.removeData(DATA_DAYS_TO);
            } else {
                $input.trigger("touchspin.updatesettings", {max: parseInt($this.val())});
                $daysButton.data(DATA_DAYS_TO, val);
            }
        }
    };

    //this functions fires when kvDatepicker::changeDate event triggers
    $.datePickerRules = function (e) {
        addError($datesModal, '', true);
        var $this = $(this),
            id = $this.attr('id'),
            $datesInput = $('input', this);
        if ('undefined' !== typeof e.date) {
            var ts = Math.round(e.date.valueOf() / 1000);
            if (-1 !== id.indexOf(DATE_FROM_ID)) {
                $('#' + DATE_TO_ID + widgetIdAppendix).kvDatepicker('setStartDate', e.date);
                $datesButton.data(DATA_DATE_FROM_TS, ts);
                $datesButton.data(DATA_DATE_FROM_VAL, $datesInput.val());
            } else if (-1 !== id.indexOf(DATE_TO_ID)) {
                $('#' + DATE_FROM_ID + widgetIdAppendix).kvDatepicker('setEndDate', e.date);
                $datesButton.data(DATA_DATE_TO_TS, ts);
                $datesButton.data(DATA_DATE_TO_VAL, $datesInput.val());
            }
        } else {
            if (-1 !== id.indexOf(DATE_FROM_ID)) {
                $('#' + DATE_TO_ID + widgetIdAppendix).kvDatepicker('setStartDate', false);
                $datesButton.removeData(DATA_DATE_FROM_TS);
                $datesButton.removeData(DATA_DATE_FROM_VAL);
            } else if (-1 !== id.indexOf(DATE_TO_ID)) {
                $('#' + DATE_FROM_ID + widgetIdAppendix).kvDatepicker('setEndDate', false);
                $datesButton.removeData(DATA_DATE_TO_TS);
                $datesButton.removeData(DATA_DATE_TO_VAL);
            }
        }
    };

    //main actions proxy function
    $('#add-col-modal, #add-row-modal, #ranges-container').on("click", ACTION_BUTTON_SELECTOR, function () {
        var $this = $(this),
            action = $this.data(DATA_RANGE_ACTION);
        switch (action) {
            case 'add-row' :
                addRow($this);
                break;
            case 'add-col':
                addCol($this);
                break;
            case 'delete-row' :
                deleteRow($this);
                break;
            case 'delete-col' :
                deleteCol($this);
                break;
            case 'reset-grid' :
                resetGrid();
                break;
        }
    });

    //MODALS STUFF
    $daysModal.on('shown.bs.modal', function () {
        var $this = $(this);
        if ($('th', $datesGridHead).length <= 1) {
            addError($daysModal, window.datesProperty.emptyDatesMessage);
            $('input, button:not(.close)', $this).attr('disabled', true);
        } else {
            addError($datesModal, '', true);
            $('input, button', $this).attr('disabled', false);
        }
    });
    $daysModal.on('hidden.bs.modal', function (e) {
        addError($daysModal, '', true);
        $('input#' + DAYS_FROM_ID).val('');
        $('input#' + DAYS_TO_ID).val('');
    });
    $datesModal.on('hidden.bs.modal', function (e) {
        addError($datesModal, '', true);
        $('#' + DATE_TO_ID + widgetIdAppendix).kvDatepicker('clearDates');
        $('#' + DATE_FROM_ID + widgetIdAppendix).kvDatepicker('clearDates');
    });

    //adds a new column to dates range grid. With date from and date to values
    function addCol($button) {
        var dateFrom = $button.data(DATA_DATE_FROM_TS),
            dateTo = $button.data(DATA_DATE_TO_TS),
            dateFromVal = $button.data(DATA_DATE_FROM_VAL),
            dateToVal = $button.data(DATA_DATE_TO_VAL),
            errors = false;
        if ('undefined' === typeof dateFrom) {
            errors = true;
            addError($datesModal, window.datesProperty.dateFromLabel + ' ' + window.datesProperty.errorMessage);
        }
        if ('undefined' === typeof dateTo) {
            errors = true;
            addError($datesModal, window.datesProperty.dateToLabel + ' ' + window.datesProperty.errorMessage);
        }
        if (false === errors) {
            if (dateFrom > dateTo) {
                addError($datesModal, window.datesProperty.wrongRangeMessage);
            } else {
                var $existing = $('th', $datesGridHead).filter(function (i, el) {
                    return $(this).data(DATA_DATE_FROM_TS) == dateFrom && $(this).data(DATA_DATE_TO_TS) == dateTo;
                });
                if ($existing.length) {
                    addError($datesModal, window.datesProperty.datesAlreadyExists);
                } else {
                    var $appendData = $('<th>' + dateFromVal + '&nbsp;&rarr;&nbsp' + dateToVal
                        + "&nbsp;<button type='button' class='btn btn-danger btn-xs pull-right' data-range-action='delete-row'><i class='fa fa-close'></i></button>"
                        + '</th>').data(DATA_DATE_FROM_TS, dateFrom).data(DATA_DATE_TO_TS, dateTo);
                    $datesGridHead.append($appendData);
                    var $rows = $('tr', $datesGridBody);
                    if ($rows.length) {
                        $.each($rows, function (i, el) {
                            var $this = $(this),
                                $dataTd = $('td:eq(0)', $this),
                                daysFrom = $dataTd.data(DATA_DAYS_FROM),
                                daysTo = $dataTd.data(DATA_DAYS_TO);
                            appendInputs($this, dateFrom, dateTo, daysFrom, daysTo);
                            index++;
                        });
                    }
                    $datesModal.modal('hide');
                }
            }
        }
        return false;
    }

    //removes selected grid col
    function deleteCol($button) {
        var $self = $button.parents('th:eq(0)'),
            $allTh = $('th', $datesGridHead),
            colIndex = $allTh.index($self),
            $rows = $('tr', $datesGridBody);
        $self.remove();
        if ($allTh.length > 2) {
            $.each($rows, function (i, el) {
                var $this = $(this);
                $('td:eq(' + colIndex + ')', $this).remove();
            });
        } else {
            resetGrid();
        }
    }

    //deletes all grid data
    function resetGrid() {
        $('th:not(:eq(0))', $datesGridHead).remove();
        $('tr', $datesGridBody).remove();
    }

    //adds a new row to dates range grid with given days range
    function addRow($button) {
        var daysFrom = $button.data(DATA_DAYS_FROM),
            daysTo = $button.data(DATA_DAYS_TO),
            errors = false;
        if ('undefined' === typeof daysFrom) {
            errors = true;
            addError($daysModal, window.datesProperty.daysFromLabel + ' ' + window.datesProperty.errorMessage);
        }
        if ('undefined' === typeof daysTo) {
            errors = true;
            addError($daysModal, window.datesProperty.daysToLabel + ' ' + window.datesProperty.errorMessage);
        }
        if (false === errors) {
            if (daysFrom > daysTo) {
                addError($daysModal, window.datesProperty.wrongDaysCount);
            } else {
                var $existing = $('td', $datesGridBody).filter(function (i, el) {
                    return $(this).data(DATA_DAYS_FROM) == daysFrom && $(this).data(DATA_DAYS_TO) == daysTo;
                });
                if ($existing.length) {
                    addError($daysModal, window.datesProperty.daysAlreadyExists);
                } else {
                    var $ranges = $('th:not(:eq(0))', $datesGridHead),
                        $tpl = $('<tr><td>' + daysFrom + '&nbsp;&rarr;&nbsp' + daysTo
                        + "&nbsp;<button type='button' class='btn btn-danger btn-xs pull-right' data-range-action='delete-row'><i class='fa fa-close'></i></button>"
                        + '</td></tr>');
                    $('td', $tpl).data(DATA_DAYS_FROM, daysFrom).data(DATA_DAYS_TO, daysTo);
                    if ($ranges.length > 0) {
                        $ranges.each(function () {
                            var $this = $(this),
                                dateFromTs = $this.data(DATA_DATE_FROM_TS),
                                dateToTs = $this.data(DATA_DATE_TO_TS);
                            appendInputs($tpl, dateFromTs, dateToTs, daysFrom, daysTo);
                            index++;
                        });
                    }
                    $datesGridBody.append($tpl);
                    $daysModal.modal('hide');
                }
            }
        }
        return false;
    }

    //removes selected grid row
    function deleteRow($button) {
        $button.parents('tr:eq(0)').remove();
    }

    //adds or removes validation errors
    function addError($modal, message, flush) {
        var $errorContainer = $('.help-block', $modal);
        if ('undefined' !== typeof flush) {
            $errorContainer.html('').parent().removeClass('has-error');
        } else {
            $errorContainer.append('<span>' + message + '</span><br>').parent().addClass('has-error');
        }
    }

    function appendInputs($element, dateFromTs, dateToTs, daysFrom, daysTo) {
        var $subTpl = $('<td></td>');
        $subTpl.data(DATA_RANGE_INDEX, index);
        $subTpl.append(
            $inputs.datesFrom.clone().attr('name', PROPERTY_NAME + '[' + index + '][' + DATE_FROM_NAME + ']')
                .val(dateFromTs),
            $inputs.datesTo.clone().attr('name', PROPERTY_NAME + '[' + index + '][' + DATE_TO_NAME + ']')
                .val(dateToTs),
            $inputs.daysFrom.clone().attr('name', PROPERTY_NAME + '[' + index + '][' + DAYS_FROM_NAME + ']')
                .val(daysFrom),
            $inputs.daysTo.clone().attr('name', PROPERTY_NAME + '[' + index + '][' + DAYS_TO_NAME + ']')
                .val(daysTo),
            $inputs.price.clone().attr('name', PROPERTY_NAME + '[' + index + '][' + PRICE_NAME + ']')
        );
        $element.append($subTpl);
    }
})(jQuery);
