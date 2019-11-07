<div class="col-md-12">
    <div id="tableBackground" class="tablebg">
        <table id="servers-list" width="100%" class="datatable no-margin ">
            <thead>
            <tr>
                <th>
                    ip:port
                </th>
                <th>
                    status
                </th>
            </tr>
            </thead>
            <tbody>
            {foreach $servers as  $server}
                <tr>
                    <td>
                        {$server.address}
                    </td>
                    <td>
                        {$server.status}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>

