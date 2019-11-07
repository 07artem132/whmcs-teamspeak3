<script type="text/javascript" src="/modules/addons/TeamSpeak3/templates/js/admin/admin.js?v={$smarty.now}"></script>
<script type="text/javascript"
        src="/modules/addons/TeamSpeak3/templates/js/shared/easy-alert.js?v={$smarty.now}"></script>
<form class="form-horizontal" onsubmit="return save(this);">
    <div class='row'>
        <div class='col-md-8 col-md-offset-2'>
            <fieldset>
                <legend class='text-center'>Сервер</legend>
                <div class="form-group">
                    <label for="servername" class="col-sm-3 control-label">Имя сервера</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='servername' id="servername"
                               value='{$settings.servername->val}'>
                    </div>
                </div>
                <div class="form-group">
                    <label for="servermsgwelcome" class="col-sm-3 control-label">Приветствие</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='servermsgwelcome' id="servermsgwelcome"
                               value='{$settings.servermsgwelcome->val}'>
                    </div>
                </div>
                <div class="form-group">
                    <label for="servermsg" class="col-sm-3 control-label">Сообщение хоста</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='servermsg' id="servermsg"
                               value='{$settings.servermsg->val}'>
                    </div>
                </div>
                <div class="form-group">
                    <label for="servermsgmode" class="col-sm-3 control-label">Режим сообщения</label>
                    <div class="col-sm-9">
                        <select name='servermsgmode' id="servermsgmode" class="form-control">
                            <option value='0'
                                    {if $settings.servermsgmode->val eq 0}
                                        selected
                                    {/if}
                            >Не показывать (NONE)
                            </option>
                            <option value='1'
                                    {if $settings.servermsgmode->val eq 1}
                            selected
                                    {/if}>Показать сообщение в чате (LOG)
                            </option>
                            <option value='2'
                                    {if $settings.servermsgmode->val eq 2}
                            selected
                                    {/if}>Показать модальное сообщение (MODAL)
                            </option>
                            <option value='3'
                                    {if $settings.servermsgmode->val eq 3}
                            selected
                                    {/if}>Показать модальное сообщение и отключить (MODALQUIT)
                            </option>
                        </select>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend class='text-center'>Баннер хоста</legend>
                <div class="form-group">
                    <label for="bannerlinkurl" class="col-sm-3 control-label">URL баннера</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='bannerlinkurl' id="bannerlinkurl"
                               value="{$settings.bannerlinkurl->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="bannerimgurl" class="col-sm-3 control-label">Ссылка на картинку</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='bannerimgurl' id="bannerimgurl"
                               value="{$settings.bannerimgurl->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="bannermode" class="col-sm-3 control-label">Изменить размер</label>
                    <div class="col-sm-9">
                        <select name='bannermode' id="bannermode" class="form-control">
                            <option value='0' {if $settings.bannermode->val eq 0}
                            selected
                                    {/if}>Не настраивать
                            </option>
                            <option value='1' {if $settings.bannermode->val eq 1}
                            selected
                                    {/if}>Настроить без учёта высоты и ширины объекта
                            </option>
                            <option value='2' {if $settings.bannermode->val eq 2}
                            selected
                                    {/if}>Настроить с учётом высоты и ширины объекта
                            </option>
                        </select>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend class='text-center'>Кнопка хоста</legend>
                <div class="form-group">
                    <label for="buttonlinkurl" class="col-sm-3 control-label">URL кнопки</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='buttonlinkurl' id="buttonlinkurl"
                               value="{$settings.buttonlinkurl->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="buttonimgurl" class="col-sm-3 control-label">Ссылка на значок</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='buttonimgurl' id="buttonimgurl"
                               value="{$settings.buttonimgurl->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="buttonmsgtootlip" class="col-sm-3 control-label">Всплывающая подсказка</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='buttonmsgtooltip' id="buttonmsgtooltip"
                               value="{$settings.buttonmsgtooltip->val}">
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend class='text-center'>Передача файлов</legend>
                <div class="form-group">
                    <label for="uploadquota" class="col-sm-3 control-label">Квота для выгрузки</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='uploadquota' id="uploadquota"
                               value="{$settings.uploadquota->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="downloadquota" class="col-sm-3 control-label">Квота для скачивания</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='downloadquota' id="downloadquota"
                               value="{$settings.downloadquota->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="uploadbandwidth" class="col-sm-3 control-label">Максимальная скорость выгрузки</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='uploadbandwidth' id="uploadbandwidth"
                               value="{$settings.uploadbandwidth->val}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="downloadbandwidth" class="col-sm-3 control-label">
                        Максимальная скорость скачивания
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name='downloadbandwidth' id="downloadbandwidth"
                               value="{$settings.downloadbandwidth->val}">
                    </div>
                </div>
            </fieldset>
            <hr>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-8">
                    <button type="submit" class="btn btn-default center-block">
                        <i class='fa fa-floppy-o'></i>
                        &nbsp;&nbsp;Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>