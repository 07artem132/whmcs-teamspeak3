<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Информация об услуге</h3>
        </div>
        <div class="panel-body text-center">
            <div class="col-md-4 ts3-product">
                <em>{$groupname}</em>
                <h3 style="margin:0;">{$product}</h3>

                {if $rawstatus === 'suspended'}
                    <div>
                        <strong class="ts3-offline">Сервер остановлен за неуплату</strong></br>
                        <a href="/viewinvoice.php?id={$invoiceId}" class="btn btn-success">Продлить</a>
                    </div>
                {elseif  $rawstatus === 'active'}
                    {if $instanceStatus eq true}
                        <div id="serverStatusBarOnline"
                                {if $virtualServerStatus eq false}
                                    style="display: none"
                                {/if}
                        >
                            <strong class="ts3-online">Сервер онлайн</strong>
                            <button class="btn btn-danger onoff" onclick="serverOff()">
                                <i class="fa fa-stop"></i>
                            </button>
                        </div>
                        <div id="serverStatusBarOffline"
                                {if $virtualServerStatus eq true}
                                    style="display: none"
                                {/if}
                        >
                            <strong class="ts3-offline">Сервер оффлайн</strong>
                            <button class="btn btn-success onoff" onclick="serverOn()">
                                <i class="fa fa-play"></i>
                            </button>
                        </div>
                    {else}
                        <div>
                            <strong class="ts3-offline">Нет связи с сервером</strong>
                        </div>
                    {/if}
                {/if}
            </div>
            <div class="col-md-4 ts3-info">
                <div class="row" id="nextDueDate">
                    <div class="col-xs-6 text-right">
                        Следующий платёж:
                    </div>
                    <div class="col-xs-6 text-left">
                        {$nextduedate}
                    </div>
                </div>

                {if $billingcycle != $LANG.orderpaymenttermonetime && $billingcycle != $LANG.orderfree}
                    <div class="row" id="recurringAmount">
                        <div class="col-xs-6 text-right">
                            {$LANG.recurringamount}:
                        </div>
                        <div class="col-xs-6 text-left">
                            {$recurringamount}
                        </div>
                    </div>
                {/if}

                <div class="row" id="billingCycle">
                    <div class="col-xs-6 text-right">
                        {$LANG.orderbillingcycle}:
                    </div>
                    <div class="col-xs-6 text-left">
                        {$billingcycle}
                    </div>
                </div>

                {foreach from=$configurableoptions item=configoption}
                    <div class="row">
                        <div class="col-xs-6 text-right">
                            Максимум слотов:
                        </div>
                        <div class="col-xs-6 text-left">
                            {$configoption.selectedqty}
                            {if $instanceStatus eq true}
                                <a href="upgrade.php?type=configoptions&id={$serviceid}">
                                    <i class="fa fa-pencil"></i></a>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="col-md-4 ts3-address">
                <p>Базовый адрес сервера:</p>
                <a style="display: none" id="linkConnectForBase" href=""
                   class="btn btn-info btn-sm" target="_top"
                   title="Нажмите чтобы подключиться к серверу по базовому адресу"></a>
                <br>
                <br>
                <p>Лёгкий адрес<i class="fas fa-question-circle"
                                  title="Модуль «Лёгкий адрес» позволяет использовать альтернативный уникальный адрес без порта для подключения к серверу"></i>
                </p>

                <a id="linkConnectForDomain" style="display: none"
                   href="ts3server://{$domain}/?nickname={$clientsdetails.firstname}"
                   class="btn btn-info btn-sm" target="_top"
                   title="Нажмите чтобы подключиться к серверу по лёгкому адресу">{$domain}</a>
                <a id="editDomain" onclick="showEditDomain()" style="display: none"
                   title="Нажмите чтобы изменить лёгкий адрес">
                    <i class="fa fa-pencil"></i>
                </a>

                <a id="installDomain" onclick="showEditDomain()" style="display: none" class="btn btn-info btn-sm"
                   target="_top"
                   title="Нажмите чтобы установить лёгкий адрес">Установить</a>
                <table id="domainEditForm" style="display: none" class="ts3-address">
                    <thead>
                    <tr>
                        <th class="col-md-5"></th>
                        <th class="col-md-5"></th>
                        <th class="col-md-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <input type="text" class="form-control" id="subDomain" value=""
                                   placeholder="clan-name"
                                   title="Cубдомен может состоять из латинских букв «A-Z», цифр «0-9» и символа дефиса «-»"
                                   required/>
                        </td>
                        <td>
                            <select name="domain" id="domain" class="form-control"
                                    title="Выберите домен, который будет составляющей частью вашего лёгкого адреса">
                            </select>
                        </td>
                        <td>
                            <a onclick="saveEditDomain()" style="cursor: pointer" class="ok"
                               title="Установить лёгкий адрес"><i class="fas fa-check-circle"></i></a>
                            <a onclick="hiddenEditDomain()" style="cursor: pointer" class="cancel"
                               title="Отменить действие"><i class="fas fa-ban"></i></a>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>