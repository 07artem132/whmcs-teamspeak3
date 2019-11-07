<a href="clientarea.php?action=productdetails&id={$serviceid}" class="btn btn-primary pull-right"><i
            class="fa fa-arrow-left"></i>&nbsp;&nbsp;Назад</a>
<div class="clearfix"></div>
<hr>
<div class="panel panel-default">
    <div class="panel-heading">Настройки</div>
    <div class="panel-body">
        <form method="get" action="clientarea.php" class="form-horizontal text-center">
            <input type="hidden" name="action" value="productdetails"/>
            <input type="hidden" name="id" value="{$serviceid}"/>
            <input type="hidden" name="modop" value="custom"/>
            <input type="hidden" name="a" value="settings"/>
            <div class="form-group">
                <label for="hostname" class="control-label col-sm-2">Название сервера</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="hostname" id="hostname"
                           value="{$serverinfo.virtualserver_name}"/>
                </div>
            </div>
            <div class="form-group">
                <label for="welcomemessage" class="control-label col-sm-2">Приветственное сообщение</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="welcomemessage" id="welcomemessage"
                           value="{$serverinfo.virtualserver_welcomemessage}"/>
                </div>
            </div>
            <div class="form-group">
                <label for="pw" class="control-label col-sm-2">Пароль:</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="pw" id="pw" value="" placeholder="Пароль"/>
                    <input type="text" class="form-control" name="confirmpw" id="confirmpw" value=""
                           placeholder="Подтвердите пароль"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-5">
                    <button type="submit" class="btn btn-warning" name="custom" value="save"><i
                                class="fa fa-floppy-o"></i>&nbsp;&nbsp;Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>