<div id="autoSettingsWidget" class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Автонастройка <i class="fas fa-question-circle"
                                                     title="Модуль «Автонастройка» настроит ваш сервер. Сперва все группы сервера и каналы будут удалены, затем начнётся установка настройки."></i>
            </h3><!--будет создан бекап текущего состояния сервера, после чего -->
        </div>
        <div id="autoSettingContent" class="panel-body text-center body-auto form-inline ">
            {if $version.build > 1562003443}
                <div class="col-md-5">
                    <label for="default_template_id">Выберите стиль <img
                                src="modules/servers/teamspeak3/templates/img/preview.png"
                                alt="Стиль автонастройки" width="52" height="16"/> :</label>
                    <select name="default_template_id" id="default_template_id" class="form-control">
                        <option value="1">Золотой</option>
                        <option value="2">Белый</option>
                        <option value="3">Зелёный(Неон)</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="full_template_id">Выберите игру:</label>
                    <select name="full_template_id" id="full_template_id" class="form-control">
                        <option value="1">CrossFire</option>
                        <option value="2">Warface</option>
                        <option value="3">World Of Tanks</option>
                        <option value="4">Другая игра</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="autoSettings();"
                            class="btn btn-success">
                        <i class="fas fa-inbox-out"></i>
                        Установить
                    </button>
                </div>
            {else}
                Для вашей версии сервера настройки не доступны
            {/if}
        </div>
        <div id="preLoader" class="panel-body" style="display: none;">
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