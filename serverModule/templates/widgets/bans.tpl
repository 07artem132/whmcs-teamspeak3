<div id="BansWidget" class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Баны</h3>
        </div>
        <div id="banContent" style="display: none" class="panel-body">
            <div class="table-responsive table-overflow">
                <table id="bansList" class="table table-condensed bans">
                    <thead>
                    <tr>
                        <th>IP/Имя/UID</th>
                        <th>Дата/Время</th>
                        <th>Причина</th>
                        <th>Срок</th>
                        <th>Инициатор</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <hr>
            <div class="form-group form-horizontal text-center">
                <table class="ts3-ban-create">
                    <thead>
                    <tr>
                        <th class="col-md-1"><label for="banType" class="control-label">Тип</label></th>
                        <th class="col-md-4"><label for="banValue" class="control-label">Значение</label></th>
                        <th class="col-md-4"><label for="banReason" class="control-label">Причина</label></th>
                        <th class="col-md-1"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <select name="banType" id="banType" class="form-control">
                                <option value="ip">IP</option>
                                <option value="name">Имя</option>
                                <option value="uid">UID</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="banValue" id="banValue" value="" required/>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="banReason" id="banReason" value=""/>
                        </td>
                        <td>
                            <button class="btn btn-warning" onclick="createBan()">
                                <i class="fa fa-ban"></i> Создать
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="preLoader" class="panel-body">
            <div class="lds-roller center-block">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
</div>