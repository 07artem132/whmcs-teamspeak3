<link href="modules/addons/TeamSpeak3/serverModule/templates/teamspeak.css" rel="stylesheet">

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Основные настройки TS3PO</h3>
        </div>
        <div class="panel-body text-center">
            <div class="col-md-4">
                <img src="modules/servers/teamspeak3/templates/img/ts3po.png"
                     alt="TS3PO - Музыкальный бот для TeamSpeak 3" title="В разработке" width="200" height="120">
                {foreach from=$configurableoptions item=configoption}
                    <div class="row">
                        <div class="col-xs-6 text-right">
                            Ботов активно:
                        </div>
                        <div class="col-xs-6 text-left">
                            {$configoption.selectedqty}
                            {if $instanceStatus eq true}
                                <a href=""><i class="fa fa-pencil"></i></a>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="col-md-4">
                <form id="fastplugins">
                    <div class="row">
                        <div class="col-xs-6 text-right">
                            <i class="fas fa-question-circle" title="В разработке"></i> Громкость при разговоре:
                        </div>
                        <div class="col-xs-6 text-left">
                            <select name="talkvol">
                                <option value="off">100%</option>
                                <option value="0.5">50%</option>
                                <option value="0.25">25%</option>
                                <option value="0.15">15%</option>
                                <option value="0.5">5%</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <a class="btn btn-success btn-sm">Скоро</a>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">TS3PO #1</h3>
        </div>
        <div class="panel-body text-center">
            <form>
                <div class="row">
                    {if $rawstatus === 'suspended'}
                        <div>
                            <strong class="ts3-offline">Бот остановлен за неуплату</strong></br>
                            <a href="/viewinvoice.php?id={$invoiceId}" class="btn btn-success">Продлить</a>
                        </div>
                    {elseif  $rawstatus === 'active'}
                        {if $instanceStatus eq true}
                            <div id="serverStatusBarOnline"
                                    {if $virtualServerStatus eq false}
                                        style="display: none"
                                    {/if}
                            >
                                <strong class="ts3-online">Бот онлайн</strong>
                                <button class="btn btn-danger onoff" onclick="serverOff()">
                                    <i class="fa fa-stop"></i>
                                </button>
                            </div>
                            <div id="serverStatusBarOffline"
                                    {if $virtualServerStatus eq true}
                                        style="display: none"
                                    {/if}
                            >
                                <strong class="ts3-offline">Бот оффлайн</strong>
                                <button class="btn btn-success onoff" onclick="serverOn()">
                                    <i class="fa fa-play"></i>
                                </button>
                            </div>
                        {else}
                            <div>
                                <strong class="ts3-offline">Нет связи с ботом</strong>
                            </div>
                        {/if}
                    {/if}
                </div>
                <div class="row">
                    <div class="col-xs-6 text-right">
                        <i class="fas fa-question-circle" title="В разработке"></i> Канал:
                    </div>
                    <div class="col-xs-6 text-left">
                        <select name="defchannel">
                            <option value="x1">Name of channel 1</option>
                            <option value="xn">Name of channel n</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6 text-right">
                        <i class="fas fa-question-circle" title="В разработке"></i> Громкость:
                    </div>
                    <div class="col-xs-6 text-left">
                        <input name="volume" type="range" min="0" max="100" step="1" value="50">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>