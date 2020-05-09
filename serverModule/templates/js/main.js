/*
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 28.09.19 15:39
 *
 */
async function autoSettings($form) {
    if (!confirm('Внимание! Все группы и каналы будут удалены, затем будут установлены новые. Подтвердить?')) {
        return false;
    }

    var default_template_id = $('select[name=default_template_id] option:selected').val();
    var full_template_id = $('select[name=full_template_id] option:selected').val();

    console.log('userid->' + window.teamspeak.userid);
    console.log('serviceid->' + window.teamspeak.serviceid);
    console.log('sign->' + window.teamspeak.sign);
    console.log('default_template_id->' + default_template_id);
    console.log('full_template_id->' + full_template_id);

    $("#autoSettingsWidget #preLoader").show();
    $("#autoSettingsWidget #autoSettingContent").hide();

    DeleteAllIcon().done(function (p) {
        console.log(p);
        DeleteAllTokens().done(function (p) {
            console.log(p);
            DeleteAllGroupsWithoutAGuest().done(function (p) {
                console.log(p);
                DeleteAllChannelWithoutADefault().done(function (p) {
                    console.log(p);
                    ApplyGroupTemplate(default_template_id, 1, 1).done(function (p) {
                        console.log(p);
                        ApplyFullTemplate(full_template_id).done(function (p) {
                            console.log(p);
                            $("#autoSettingsWidget #preLoader").hide();
                            $("#autoSettingsWidget #autoSettingContent").show();
                            resetPrivilegeKeyWidget();
                            alert('Настройка завершена!')
                        });
                    });
                });
            });
        });
    });

}

function ApplyFullTemplate(full_template_id) {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_apply_full_template',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            template_id: full_template_id,
        },
        dataType: 'json',
    })
}

function ApplyGroupTemplate(default_template_id, create_admin_token, apply_guest_group) {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_apply_group_template',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            template_id: default_template_id,
            create_admin_token: create_admin_token,
            apply_guest_group: apply_guest_group,
        },
        dataType: 'json',
    })
}

function DeleteAllChannelWithoutADefault() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_delete_all_channels_without_a_default',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function DeleteAllGroupsWithoutAGuest() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_delete_all_groups_without_a_guest',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function DeleteAllTokens() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_delete_all_privilege_keys',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function DeleteAllIcon() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_delete_all_icons',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function createBackup() {
    PreloaderBackupCreate();
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'backup_create',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            backupCheckStatus();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function restoreBackup() {
    var selectBackup = $("#backupWidget input[type=radio]:checked");

    if (selectBackup.length === 0) {
        alert('Ошибка вы не выбрали резервную копию');
        return;
    }

    PreloaderBackupRestore();

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'backup_restore',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            tag: selectBackup.data('tag'),
            backup_date: selectBackup.data('backup-date'),
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            backupCheckStatus();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function removeBackup() {
    var selectBackup = $("#backupWidget input[type=radio]:checked");

    if (selectBackup.length === 0) {
        alert('Ошибка вы не выбрали резервную копию');
        return;
    }

    $("#backupWidget #preLoader").toggle();
    $("#backupWidget #BackupContent").toggle();

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'backup_delete',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            tag: selectBackup.data('tag'),
            backup_date: selectBackup.data('backup-date'),
        },
        dataType: 'json',
        success: function (data) {
            $(selectBackup).parent().parent().remove();
            $("#backupWidget #preLoader").toggle();
            $("#backupWidget #BackupContent").toggle();
            console.log(data);
            if ($('#backupList > tbody:last-child > tr').length === 0) {
                $('#backupList > tbody:last-child').append(
                    $('<tr>').append(
                        $('<td>').text("Пока вы не создали ни одного бекапа").css("text-align", "center")
                    )
                );
            }
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function PreloaderBackupCreate() {
    $("#backupWidget #preLoader").hide();

    $("#backupWidget #BackupContent").hide();
    $("#backupWidget #BackupCreateInfoMessage").show();
    $("#autoSettingsWidget #autoSettingContent").hide();
    $("#autoSettingsWidget #BackupCreateInfoMessage").show();
}


function PreloaderBackupRestore() {
    $("#backupWidget #preLoader").hide();

    $("#backupWidget #BackupContent").hide();
    $("#backupWidget #BackupRestoreInfoMessage").show();
    $("#autoSettingsWidget #autoSettingContent").hide();
    $("#autoSettingsWidget #BackupRestoreInfoMessage").show();
}

async function loadBackupList() {
    $("#backupWidget #BackupCreateInfoMessage").hide();
    $("#backupWidget #BackupRestoreInfoMessage").hide();

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'backup_list',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            $('#backupList > tbody:last-child').empty();
            applyBackupList(data.data);
            $("#backupWidget #preLoader").hide();
            $("#backupWidget #BackupContent").show();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function applyBackupList(data) {
    console.log('apply_backup_list');
    console.log(data);

    if (data.length === 0) {
        $('#backupList > tbody:last-child').append(
            $('<tr>').append(
                $('<td>').text("Пока вы не создали ни одного бекапа").css("text-align", "center")
            )
        );
        return;
    }

    //Сортировка от новых к старым
    data.sort((a, b) => (a.create_at < b.create_at) ? 1 : -1);

    $.each(data, function (key, value) {
        console.log(value);
        $('#backupList > tbody:last-child').append($('<tr>')
            .append(
                $('<td>').text(backupDecodeDate(value.create_at))
            ).append(
                $('<td>').text(backupDecodeTag(value.tag))
            ).append(
                $('<td>').append($('<input>')
                    .attr('data-tag', value.tag)
                    .attr('data-uid', value.uid)
                    .attr('data-backup-date', value.backupDate)
                    .attr('type', 'radio')
                    .attr('name', 'backup')
                )
            )
        );
    });
}

function backupDecodeDate(create_at) {
    var date = new Date(create_at * 1000);
    return ("0" + date.getDate()).slice(-2) + '-' +
        ("0" + (date.getMonth() + 1)).slice(-2) + '-' +
        date.getFullYear() + ' ' +
        ("0" + date.getHours()).slice(-2) + ':' +
        ("0" + date.getMinutes()).slice(-2)
}

function backupDecodeTag(tag) {
    switch (tag) {
        case 'manual':
            return 'Ручной';
        case 'auto':
            return 'Автоматический';
        case 'preDelete':
            return 'До приостановки';
        default:
            return 'Не известный тип';
    }
}

function CheckIsBackupRunning() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'is_backup_running',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function CheckIsBackupRestoreRunning() {
    return $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'is_backup_restore_running',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

async function loadPrivilegeKeysList() {
    getServerGroupList();
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_privilege_keys_list',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            applyPrivilegeKeysList(data.data);
            $("#PrivilegeKeyWidget #preLoader").hide();
            $("#PrivilegeKeyWidget #PrivilegeKeyContent").show();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

async function loadBansList() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_bans_list',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            applyBansList(data.data);
            $("#BansWidget #preLoader").toggle();
            $("#BansWidget #banContent").toggle();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function createBan() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_ban_create',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            type: $('#banType option:selected').val(),
            value: $('#banValue').val(),
            reason: $('#banReason').val(),
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            resetBanWidget();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function resetBanWidget() {
    $("#BansWidget #preLoader").toggle();
    $("#BansWidget #banContent").toggle();
    $('#bansList > tbody:last-child').empty();
    loadBansList()
}

function applyPrivilegeKeysList(data) {
    console.log('applyPrivilegeKeysList');
    console.log(data);

    if (data.length === 0) {
        $('#privilegeKeyList > tbody:last-child').append(
            $('<tr>').append(
                $('<td>').text("Ключи привилегий отсутствуют")
                    .attr('colspan', 2)
                    .css("text-align", "center")
            )
        );
        return;
    }

    $.each(data, function (key, value) {
        console.log(value);
        $('#privilegeKeyList > tbody:last-child').append($('<tr>')
            .append(
                $('<td>').text(value.token_id1)
            ).append(
                $('<td>').text(value.token)
            ).append(
                $('<td>').append($('<a>')
                    .attr('href', "ts3server://" + window.teamspeak.serverHostname + ":" + window.teamspeak.serverPort + "/?nickname=" + window.teamspeak.nickname + "&token=" + value.token)
                    .attr('class', "btn btn-success btn-xs")
                    .attr('name', 'token')
                    .attr('onclick', "$(this).parent().parent().remove()")
                    .attr('title', 'Открыть программу, подключиться к серверу и активировать ключ привилегии на ваш основной идентификатор')
                    .text("Активировать")
                )
            ).append(
                $('<td>').append($('<button>')
                    .attr('class', "btn btn-danger btn-xs")
                    .attr('data-token', value.token)
                    .attr('onclick', "removePrivilegeKey(this)")
                    .attr('title', 'Удалить данный ключ привилегии')
                    .text("Удалить")
                )
            )
        );
    });
}

function buildLinkConnectForBase() {
    $('#linkConnectForBase').show()
        .text(window.teamspeak.serverHostname + ":" + window.teamspeak.serverPort)
        .attr('href', "ts3server://" + window.teamspeak.serverHostname + ":" + window.teamspeak.serverPort + "/?nickname=" + window.teamspeak.nickname);
}

function applyBansList(data) {
    console.log('applyBansList');
    console.log(data);

    if (data.length === 0) {
        $('#bansList > tbody:last-child').append(
            $('<tr>').append(
                $('<td>').text("Пока что вы никого не банили")
                    .attr('colspan', 7)
                    .css("text-align", "center")
            )
        );
        return;
    }

    $.each(data, function (key, value) {
        console.log(value);
        $('#bansList > tbody:last-child').append($('<tr>')
            .append(
                $('<td>').text(decodeBanOptions(value))
            ).append(
                $('<td>').text(banDecodeDate(value.created))
            ).append(
                $('<td>').text(value.reason == null ? '' : value.reason)
            ).append(
                $('<td>').text(banDecodeDuration(value.created, value.duration))
            ).append(
                $('<td>').text(value.invokername)
            ).append(
                $('<td>').append($('<button>')
                    .attr('class', "btn btn-danger btn-xs")
                    .attr('data-ban-id', value.banid)
                    .attr('onclick', "removeBan(this)")
                    .attr('title', 'Удалить данный бан')
                    .text("Удалить")
                )
            )
        );
    });
}

function banDecodeDate(create_at) {
    var date = new Date(create_at * 1000);
    return ("0" + date.getDate()).slice(-2) + '-' +
        ("0" + (date.getMonth() + 1)).slice(-2) + '-' +
        date.getFullYear() + ' ' +
        ("0" + date.getHours()).slice(-2) + ':' +
        ("0" + date.getMinutes()).slice(-2)
}

function banDecodeDuration(create_at, duration) {
    if (duration === 0) {
        return 'Навсегда';
    }

    var date = new Date((create_at * 1000) + duration * 1000);

    return ("0" + date.getDate()).slice(-2) + '-' +
        ("0" + (date.getMonth() + 1)).slice(-2) + '-' +
        date.getFullYear() + ' ' +
        ("0" + date.getHours()).slice(-2) + ':' +
        ("0" + date.getMinutes()).slice(-2)
}

function decodeBanOptions(ban) {
    let banOptions = '';

    if (ban.ip !== null) {
        banOptions += 'ip=' + ban.ip + ';';
    }

    if (ban.mytsid !== null) {
        banOptions += 'mytsid=' + ban.mytsid + ';';
    }

    if (ban.name !== null) {
        banOptions += 'name=' + ban.name + ';';
    }

    if (ban.uid !== null) {
        banOptions += 'uid=' + ban.uid + ';';
    }
    console.log('banOptions' + banOptions);
    return banOptions;
}

function removePrivilegeKey(object) {
    var button = $(object);

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_privilege_key_delete',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            privilege_key: button.attr("data-token"),
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            button.parent().parent().remove();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function removeBan(object) {
    var button = $(object);

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_ban_delete',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            ban_id: button.attr("data-ban-id"),
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            button.parent().parent().remove();
            if ($('#bansList > tbody:last-child > tr').length === 0) {
                $('#bansList > tbody:last-child').append(
                    $('<tr>').append(
                        $('<td>').text("Пока что вы никого не банили")
                            .attr('colspan', 7)
                            .css("text-align", "center")
                    )
                );
            }
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

async function getServerGroupList() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_group_list',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            applyServerGroupListPrivilegeKeyWidget(data.data)
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function applyServerGroupListPrivilegeKeyWidget(data) {
    console.log('applyServerGroupListPrivilegeKeyWidget');
    console.log(data);
    $.each(data, function (key, value) {
        console.log(value);
        $('#privilegeKeyGroup').append(new Option(value.name, value.sgid));
    })
}

function createPrivilegeKey() {

    $("#PrivilegeKeyWidget #preLoader").toggle();
    $("#PrivilegeKeyWidget #PrivilegeKeyContent").toggle();

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        async: true,
        data: {
            action: 'virtual_server_privilege_key_add',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            group_id: $('#privilegeKeyGroup option:selected').val(),
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            resetPrivilegeKeyWidget();
        },
        fail: function (data) {
            console.log(data);
        },
    })
}

function resetPrivilegeKeyWidget() {
    $('#privilegeKeyList > tbody:last-child').empty();
    loadPrivilegeKeysList();
}

function backupCheckStatus() {
    $.when(CheckIsBackupRunning(), CheckIsBackupRestoreRunning()).then(function (a1, a2) {
        if (parseInt(a1[0].message) === 0 && parseInt(a2[0].message) === 0) {
            $("#autoSettingsWidget #BackupCreateInfoMessage").hide();
            $("#autoSettingsWidget #BackupRestoreInfoMessage").hide();
            $("#autoSettingsWidget #autoSettingContent").show();
            $("#backupWidget #preLoader").show();
            loadBackupList();
        } else {
            setTimeout(backupCheckStatus, 3000);
        }
    });

}

function serverOff() {
    $("#serverStatusBarOffline").show();
    $("#serverStatusBarOnline").hide();
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_stop',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}

function serverOn() {
    $("#serverStatusBarOnline").show();
    $("#serverStatusBarOffline").hide();
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_start',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
    })
}


function loadAllowDomain() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'get_allowed_product_domain',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            pid: window.teamspeak.pid
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            $.each(data.data, function (key, value) {
                $('#domain').append(new Option(value, value));
            });
        }
    })
}

function showEditDomain() {
    $('#installDomain').hide();
    $('#linkConnectForDomain').hide();
    $('#editDomain').hide();
    $('#domainEditForm').show();
}

function hiddenEditDomain() {
    if (window.teamspeak.subDomain !== "" && window.teamspeak.domain !== "") {
        $('#linkConnectForDomain').show();
        $('#editDomain').show();
    } else {
        $('#installDomain').show();
    }
    $('#domainEditForm').hide();
}

function saveEditDomain() {
    let regTest = /^[a-zA-Z0-9-]+$/;
    let subDomain = $('#subDomain').val();
    let domain = $('#domain').val();

    if (!regTest.test(subDomain)) {
        alert('Для поле "суб домен" допустимы только символы a-Z,0-9 и -');
        return false;
    }

    if (domain === window.teamspeak.domain && subDomain === window.teamspeak.subDomain) {
        $('#linkConnectForDomain').show();
        $('#editDomain').show();
        $('#installDomain').hide();
        $('#domainEditForm').hide();
        return;
    }

    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'update_domain_service',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            domain: domain,
            subDomain: subDomain,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            $('#linkConnectForDomain').show()
                .text(subDomain + "." + domain)
                .attr('href', "ts3server://" + subDomain + "." + domain + "/?nickname=" + window.teamspeak.nickname);
            $('#editDomain').show();
            $('#installDomain').hide();
            $('#domainEditForm').hide();
            window.teamspeak.domain = domain;
            window.teamspeak.subDomain = subDomain;
        }
    })

}

function loadDomainService() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'get_domain_service',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
        },
        dataType: 'json',
        success: function (data) {
            if (data.data.domain === '') {
                $('#installDomain').show();
            } else {
                window.teamspeak.subDomain = data.data.subDomain;
                window.teamspeak.domain = "." + data.data.domain;

                $("#domain option[value='" + window.teamspeak.domain + "']").prop("selected", true);
                $('#subDomain').val(window.teamspeak.subDomain);
                $('#linkConnectForDomain').show();
                $('#editDomain').show();
            }
        }
    })
}

var charConfig = {
    type: 'line',
    data: {
        datasets: [
            {
                label: 'Онлайн',
                borderColor: "red",
                backgroundColor: "red",
                data: [],
                showLine: true,
                fill: false,
                pointRadius: 0,
            }
        ]
    },
    options: {
        scales: {
            xAxes: [
                {
                    type: 'time',
                    time: {
                        unit: 'statsFormat',
                        displayFormats: {
                            statsFormat: 'dd HH:mm'
                        }
                    },
                    distribution: 'series',
                    ticks: {
                        source: 'data',
                        autoSkip: true,
                        display: true,
                        maxTicksLimit: 8
                    },
                    gridLines: {
                        display: true // линии на фоне
                    }
                }
            ],
            yAxes: [
                {
                    scaleLabel: {
                        display: true,
                        labelString: 'Онлайн'
                    },
                    ticks: {
                        display: true,
                    },
                    gridLines: {
                        display: true // линии на фоне
                    }
                }
            ]
        },
        responsive: true,
        title: {
            display: false,
            text: ''
        },
        tooltips: {
            intersect: false,
            mode: 'index',
            callbacks: {
                title: function (tooltipItems, data) {
                    let time = data.datasets[tooltipItems[0].datasetIndex].data[tooltipItems[0].index].t;
                    return moment(time).format('YYYY-MM-DD HH:mm:ss');
                },
                label: function (tooltipItem, myData) {
                    var label = myData.datasets[tooltipItem.datasetIndex].label || '';
                    if (label) {
                        label += ': ';
                    }
                    label += tooltipItem.value;
                    return label;
                }
            }
        },
        legend: {
            display: false
        },
        hover: {
            mode: "index",
            intersect: false
        },
    }
};


function loadStats() {
    $.ajax({
        type: "POST",
        url: "/?m=TeamSpeak3",
        data: {
            action: 'virtual_server_online_stats',
            user_id: window.teamspeak.userid,
            service_id: window.teamspeak.serviceid,
            sign: window.teamspeak.sign,
            type: "last_week",
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);
            $.each(data.data, function (key, value) {
                charConfig.data.datasets[0].data.push(
                    {
                        t: Date.parse(key),
                        y: value.online
                    }
                );
            });
            let char = document.getElementById('online').getContext('2d');
            window.teamspeak.onlineChar = new Chart(char, charConfig);
        },
    })
}