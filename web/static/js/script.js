var lblToggleOn = '<span class="glyphicon glyphicon-ok"></span>',
    lblToggleOff = '<span class="glyphicon glyphicon-minus-sign"></span>';

function bindTableEvents() {
    $('table#whitelist tr td button.toggle-state').each(function () {
        var $this = $(this);
        $this.html($this.hasClass('on') ? lblToggleOn : lblToggleOff);
    }).click(function () {
            var $this = $(this),
                newState = $this.hasClass('on') ? 0 : 1;

            if (newState === 0) {
                $this.removeClass('on').html(lblToggleOff);
            } else {
                $this.addClass('on').html(lblToggleOn);
            }

            var id = getEntryId($this);

            $.get(DW_SUBDIR + '/ajax/centry', {id: id, state: newState}, function (result) {
                if (!result || !result.success) {
                    alert('Operation not successful');
                }
            }, 'json');
        });

    $.editable.types.text.plugin = function (settings, original) {
        var $container = $(original).parents('div.dataTables_wrapper'),
            containerWidth = $container.width(),
            containerHeight = $container.height(),
            $form = $('form', original);

        $('<div class="edit-overlay"/>').css({
            width: containerWidth,
            height: containerHeight,
            top: 0,
            left: 0
        }).appendTo($container);

        $form.css({
            position: 'absolute',
            top: (containerHeight / 2) - ($form.height() / 2),
            left: (containerWidth / 2) - ($form.width() / 2)
        })
    };

    $('td:eq(3), td:eq(4), td:eq(5)', whitelistTable.fnGetNodes()).editable(function (value, settings) {
        var newValue = value,
            oldValue = this.revert,
            columns = whitelistTable.fnGetData(this.parentNode),
            col = whitelistTable.fnGetPosition(this)[2],
            colName = $('table#whitelist tr th:eq(' + col + ')').data('tbl'),
            params = {
                id: columns[2],
                col: colName,
                value: newValue
            },
            $this = $(this);

        $this.html('Saving...');

        $.ajax({
            url: DW_SUBDIR + '/ajax/eentry',
            type: 'post',
            async: false,
            data: params,
            dataType: 'json',
            success: function (result) {
                if (result.errors && result.errors.length > 0) {
                    alert('Errors: ' + result.errors.join(', '));
                    newValue = oldValue;
                } else {
                    whitelistTable.fnDraw(false);
                }
            },
            error: function (xhr, status, error) {
                alert('Request error: ' + error);
                newValue = oldValue;
            }
        });

        return newValue;
    }, {
        submit: '<button type="submit" class="btn btn-standard entry-edit"><span class="glyphicon glyphicon-ok"></span> Save</button>',
        cancel: '<button type="button" class="btn btn-standard entry-edit cancel"><span class="glyphicon glyphicon-remove"></span></button>',
        indicator: 'Saving...',
        cssclass: 'edit-tbl-data',
        onsubmit: cleanUpEdit,
        onerror: cleanUpEdit,
        onreset: cleanUpEdit
    });
}

function cleanUpEdit(o, el) {
    $('div.edit-overlay', $(el).parents('div.dataTables_wrapper')).remove();
}

function getEntryId(entry) {
    var id = parseInt(entry.parents('tr').data('id'));
    return !isNaN(id) ? id : 0;
}

$('body').on('click', 'button.delete-entry', function () {
    var delId = getEntryId($(this));

    if (delId > 0 && confirm('Really delete this entry?')) {
        $.get(DW_SUBDIR + '/ajax/dentry', {id: delId}, function (result) {
            if (!result || !result.success) {
                alert('Operation not successful');
            } else {
                whitelistTable.fnDraw(false);
            }
        }, 'json');
    }
});

var whitelistTable = $('table#whitelist').dataTable({
    'bProcessing': true,
    'bServerSide': true,
    'sAjaxSource': DW_SUBDIR + '/ajax/dsource/wl',
    "sPaginationType": 'full_numbers',
    "aaSorting": [
        [ 2, "asc" ]
    ],
    "aLengthMenu": [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
    ],
    'aoColumns': [
        {
            'bSearchable': false,
            'bSortable': false,
            'sWidth': '6%'
        },
        {
            'bSearchable': false,
            'sWidth': '7%'
        },
        {
            'sWidth': '7%'
        },
        null,
        null,
        null
    ],
    'fnDrawCallback': function () {
        bindTableEvents();
    },
    'fnRowCallback': function (nRow, aData) {
        $(nRow).data('id', aData[2]);
    }
});

$('table#whitelistlog').dataTable({
    'bProcessing': true,
    'bServerSide': true,
    'sAjaxSource': DW_SUBDIR + '/ajax/dsource/wll',
    "sPaginationType": 'full_numbers',
    "aaSorting": [
        [ 0, "asc" ]
    ],
    'aoColumns': [
        {
            'sWidth': '10%'
        },
        null,
        null,
        null,
        null
    ]
});
