<div id="backupWidget" class="col-md-5">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Бекапы
                <i class="fas fa-question-circle" title="На данный момент бекапы перерабатываются."></i>
            </h3>
        </div>
        <div id="BackupContent" class="panel-body" style="display: none;">
            <div class="table-overflow">
                <table id="backupList" class="table table-condensed">
                    <thead>
                    <tr>
                        <th>Дата/Время</th>
                        <th>Тип</th>
                        <th><i class="fa fa-hand-o-down"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <hr>
            <div class="btn-group pull-right">
                <button class="btn btn-success" name="createBackup" onclick="createBackup()">
                    <i class="fas fa-save"></i>
                    Создать
                </button>
                <button class="btn btn-warning" name="RestoreBackup" onclick="restoreBackup()">
                    <i class="fa fa-undo"></i>
                    Восстановить
                </button>
                <button class="btn btn-danger" name="DeleteBackup" onclick="removeBackup()">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                    Удалить
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
        <div id="BackupCreateInfoMessage" class="panel-body"
             style="display: none;text-align: center; vertical-align: middle; padding-top: 60px; height: 155px;">
            Резервная копия выполняется (в зависимости от кол-ва иконок до 30 минут), затем доступ возобновится
        </div>
        <div id="BackupRestoreInfoMessage" class="panel-body"
             style="display: none;text-align: center; vertical-align: middle; padding-top: 60px; height: 155px;">
            Востанавливается резервная копия (в зависимости от кол-ва иконок до 30 минут), затем доступ возобновится
        </div>

    </div>
</div>