<script src="/modules/servers/teamspeak3/templates/easy-alert/easy-alert.js?v={$smarty.now}"></script>

<link href="/modules/servers/teamspeak3/templates/teamspeak.css?v={$smarty.now}" rel="stylesheet">
<script src="/modules/servers/teamspeak3/templates/js/chartMoment.min.js"></script>
<script src="/modules/servers/teamspeak3/templates/js/chartMomentWithLocales.js"></script>
<script src="/modules/servers/teamspeak3/templates/js/chart_2.8.0.min.js"></script>
<script src="/modules/servers/teamspeak3/templates/js/main.js?v={$smarty.now}"></script>

<script>
    moment.locale('ru');

    window.teamspeak = {
        userid:{$userid},
        serviceid:{$serviceid},
        pid:{$pid},
        sign: "{$sign}",
        subDomain: "",//задается на этапе loadDomainService
        domain: "",//задается на этапе loadDomainService
        serverHostname: "{$serverdata.hostname}",
        serverPort: "{$port}",
        nickname: "{$clientsdetails.firstname}",
        virtualServerStatus: "{$virtualServerStatus}",
        instanceStatus: "{$instanceStatus}",
        onlineChar: null,
    };

    $(document).ready(function () {
        loadAllowDomain();
        loadDomainService();
        buildLinkConnectForBase();
    });

    if (window.teamspeak.instanceStatus == true) {
        $(document).ready(function () {
            loadStats();
            loadBansList();
            loadPrivilegeKeysList();
            $.when(CheckIsBackupRunning(), CheckIsBackupRestoreRunning()).then(function (a1, a2) {
                if (parseInt(a1[0].message) !== 0) {

                    PreloaderBackupCreate();
                    backupCheckStatus();
                } else if (parseInt(a2[0].message) !== 0) {
                    PreloaderBackupRestore();
                    backupCheckStatus();
                } else {
                    loadBackupList();
                }
            });
        });
    }
</script>

<div class="row">
    {include file="`$tpl_dir`./widgets/serviceInfo.tpl"}

    {if $rawstatus !== 'suspended'}
        {if $instanceStatus eq true}
            {include file="`$tpl_dir`./widgets/serverOnline.tpl"}
            {include file="`$tpl_dir`./widgets/addons.tpl"}
            {include file="`$tpl_dir`./widgets/autoSettings.tpl"}
            {include file="`$tpl_dir`./widgets/privkey.tpl"}
            {include file="`$tpl_dir`./widgets/backup.tpl"}
            {include file="`$tpl_dir`./widgets/bans.tpl"}
        {/if}
    {/if}
</div>



