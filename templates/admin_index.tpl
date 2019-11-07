<script type="text/javascript"
        src="/modules/addons/TeamSpeak3/templates/js/shared/easy-alert.js?v={$smarty.now}"></script>
<script type="text/javascript" src="/modules/addons/TeamSpeak3/templates/js/admin/admin.js?v={$smarty.now}"></script>
<div class="col-md-12">
    <div id="tableBackground" class="tablebg">
        <table id="servers-list" width="100%" class="datatable no-margin ">
            <thead>
            <tr>
                <th>
                    Имя
                </th>
                <th>
                    IP
                </th>
            </tr>
            </thead>
            <tbody>
            {foreach $servers as $id => $server}
                <tr>
                    <td data-id="{$id}">
                        {$server.name}
                    </td>
                    <td>
                        {$server.ip}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>

