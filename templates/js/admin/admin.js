/*
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 20.07.19 15:56
 *
 */

window.addEventListener("load", function () {
    var articles = jQuery("#articles-list").DataTable({
        "ordering": true,
        "dom": '<"listtable"fit>pl',
        "responsive": true,
        "oLanguage": {
            "sEmptyTable": "Записей не найдено",
            "sInfo": "Показано с _START_ по _END_ из _TOTAL_",
            "sInfoEmpty": "Показано с 0 по 0 из 0",
            "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
            "sInfoPostFix": "",
            "sInfoThousands": ",",
            "sLengthMenu": "Показать _MENU_ записей",
            "sLoadingRecords": "Загрузка...",
            "sProcessing": "Обработка...",
            "sSearch": "",
            "sZeroRecords": "Записей не найдено",
            "oPaginate": {
                "sFirst": "Первая",
                "sLast": "Последняя",
                "sNext": "Вперед",
                "sPrevious": "Назад"
            }
        },
        "pageLength": 100,
        "lengthMenu": [
            [50, 100, 500, -1],
            [50, 100, 500, "Все"]
        ], "stateSave": false
    });

    $('#articles-list tr td:nth-child(1) ').click(function () {
        var id = $.trim($($(this).parent().find('td')[0]).attr('data-id'));
        if (String(window.location).indexOf("index") === -1) {
            window.location = window.location + '&action=view&id=' + id;
        } else {
            window.location = String(window.location).replace("index", "view") + '&id=' + id;
        }
    });
    jQuery(".dataTables_filter input").attr("placeholder", "Условие для поиска...");
});

function save(form) {
    $.ajax({
        type: "POST",
        url: window.location.href,
        data: Object.assign({action: "saveSettings"}, getFormData($(form))),
        dataType: 'json',
        success: function (data) {
            if (data.status === 'error') {
                this.fail(data);
                return;
            }

            $.easyAlert({
                message: data.message,  //default message to be displayed
                alertType: 'success', //alert type (warning,info,danger,success)
                time: 3000, //the time to hide the alert if previous boolean is set to true in ms
                position: "t r", //preferred position
                showAnimation: 'slide', //preferred show animation if jQuery ui is included
                autoHide: true //set whether to automatically hide the alert after a period of time
            });
        },
        fail: function (data) {
            $.easyAlert({
                message: data.message,  //default message to be displayed
                alertType: 'danger', //alert type (warning,info,danger,success)
                time: 3000, //the time to hide the alert if previous boolean is set to true in ms
                position: "t r", //preferred position
                showAnimation: 'slide', //preferred show animation if jQuery ui is included
                autoHide: true //set whether to automatically hide the alert after a period of time
            });
        }
    });
    return false;
}

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}


