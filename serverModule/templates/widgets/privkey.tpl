<div id="PrivilegeKeyWidget" class="col-md-7">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Ключи привилегий
                <i class="fas fa-question-circle"
                   title="Ключи привилегий используются для назначения в группы сервера без участия пользователей из группы Server Admin"></i>
            </h3>
        </div>
        <div id="PrivilegeKeyContent" class="panel-body" style="display: none;">
                <div class="table-responsive table-overflow">
                    <table id="privilegeKeyList" class="table table-condensed">
                        <thead>
                        <tr>
                            <th>Группа</th>
                            <th>Ключ</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            <hr>
                <div class="form-group form-inline text-center">
                    <label for="groupid">Выберите группу:</label>
                    <select name="privilegeKeyGroup"
                            id="privilegeKeyGroup"
                            class="form-control"
                            title="Выберите нужную вам группу сервера(группа с правами администратора обычно называется «Server Admin»)">
                    </select>
                    <button class="btn btn-success" onclick="createPrivilegeKey()" title="Создать ключ привилегии для данной группы сервера">
                        <i class="fa fa-key"></i>&nbsp;&nbsp;Создать
                    </button>
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