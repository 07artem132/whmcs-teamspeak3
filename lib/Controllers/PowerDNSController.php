<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 21.09.19 16:10
 *
 */

namespace WHMCS\Module\Addon\TeamSpeak3\Controllers;

use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use stdClass;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\DomainEditNotMatchDomainFromUrlException;
use WHMCS\Module\Addon\TeamSpeak3\Exceptions\PowerDnsClientException;

/**
 * Class PowerDNS
 * @package Api\Services\Domain
 */
class PowerDNSController
{
    /**
     * @var string Адрес API сервера с PowerDNS
     */
    private $url;
    /**
     * @var string Ключ для работы с PowerDns
     */
    private $key;
    /**
     * @var string уникальный идентификатор сервера на PowerDNS
     */
    private $server_id;
    /**
     * @var HTTPClient Клиент для запросов к PowerDNS
     */
    private $pdns_client;
    /**
     * @var array Параметры/Заголовки которые передаются в месте с запросом ( HEADER)
     */
    private $request_option = [];

    /**
     * PowerDNS constructor.
     * @param $url
     * @param $key
     * @param string $server_id
     */
    function __construct($url, $key, $server_id = 'localhost')
    {
        $this->url = $url;
        $this->key = $key;
        $this->server_id = $server_id;

        $this->pdns_client = new HTTPClient([
            // Base URI is used with relative requests
            'base_url' => $this->url,
            // You can set any number of default request options.
            'timeout' => 2.0,
            'defaults' => [
                'headers' => [
                    'X-API-Key' => $this->key,
                    'content-type' => 'application/json'
                ]
            ]
        ]);
    }

    /**
     * Удалить домен
     * @param string $domain домен
     * @throws  PowerDnsClientException
     *
     */
    public function DomainDelete(string $domain)
    {
        $this->SendHttpRequest('DELETE', 'servers/' . $this->server_id . '/zones/' . $domain);

        return;
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function AddRequestOption(string $key, string $value)
    {
        $this->request_option[$key] = $value;

        return;
    }

    /**
     * @param string $domain изменяемый домен
     * @param string $name Полное доменное имя (включая суб домен)
     * @param string $type тип записи
     * @param int $ttl ttl записи
     * @param array $records Содержимое записи
     *
     * @throws PowerDnsClientException
     * @throws DomainEditNotMatchDomainFromUrlException
     */
    public function DomainRecordCreate(string $domain, string $name, string $type, int $ttl, array $records): void
    {
        $this->VerifiEditDomain($domain, $name);

        $this->AddRequestOption('body', $this->BildJsonRecordCreateOrEdit($name, $type, $ttl, $records));

        $this->SendHttpRequest('PATCH', 'servers/' . $this->server_id . '/zones/' . $domain);

        return;
    }

    /**
     * @param string $domain Домен из url
     * @param string $name полная запись которую хотят изменить
     *
     * @throws DomainEditNotMatchDomainFromUrlException Возникает в том случае если домен из URL не совпадает с доменом в записи которую необходимо изменить.
     */
    private function VerifiEditDomain(string $domain, string $name)
    {
        $re = '/(' . quotemeta($domain) . '\.)$/';

        preg_match($re, $name, $matches, PREG_OFFSET_CAPTURE, 0);

        if (empty($matches)) {
            throw new DomainEditNotMatchDomainFromUrlException($domain, $name);
        }

        return;
    }

    /**
     * @param string $domain изменяемый домен
     * @param string $name Полное доменное имя (включая суб домен)
     * @param string $type тип записи
     * @param int $ttl ttl записи
     * @param array $records Содержимое записи
     *
     * @return string закодированные данные в json
     * @throws DomainEditNotMatchDomainFromUrlException
     * @throws PowerDnsClientException
     */
    public function DomainRecordDelete(string $domain, string $name, string $type, int $ttl, array $records)
    {
        $this->VerifiEditDomain($domain, $name);

        $this->AddRequestOption('body', $this->BildJsonRecordDelete($name, $type, $ttl, $records));

        $this->SendHttpRequest('PATCH', 'servers/' . $this->server_id . '/zones/' . $domain);

        return;
    }

    /**
     * @param string $name Полное доменное имя (включая суб домен)
     * @param string $type тип записи
     * @param int $ttl ttl записи
     * @param array $records Содержимое записи
     *
     * @return string закодированные данные в json
     */
    private function BildJsonRecordCreateOrEdit(string $name, string $type, int $ttl, array $records): string
    {
        $array['rrsets'][0]['name'] = $name;
        $array['rrsets'][0]['comments'] = [];
        $array['rrsets'][0]['type'] = $type;
        $array['rrsets'][0]['ttl'] = $ttl;
        $array['rrsets'][0]['changetype'] = 'REPLACE';
        $array['rrsets'][0]['records'] = $records;
//dd($array);
        return json_encode($array);
    }

    /**
     * @param string $name Полное доменное имя (включая суб домен)
     * @param string $type тип записи
     * @param int $ttl ttl записи
     * @param array $records Содержимое записи
     *
     * @return string закодированные данные в json
     */
    private function BildJsonRecordDelete(string $name, string $type, int $ttl, array $records): string
    {
        $array['rrsets'][0]['name'] = $name;
        $array['rrsets'][0]['type'] = $type;
        $array['rrsets'][0]['ttl'] = $ttl;
        $array['rrsets'][0]['changetype'] = 'DELETE';
        $array['rrsets'][0]['records'] = $records;

        return json_encode($array);
    }

    /**
     * @param string $domain Домен
     * @param string $kind Тип домена (мастер/слейв/натив)
     * @param array $nameservers Массив с нейм серверами
     *
     * @return string закодированные данные в json
     */
    private function BildJsonDomainCreate(string $domain, string $kind, array $nameservers): string
    {
        $array['name'] = $domain;
        $array['kind'] = $kind;
        $array['nameservers'] = $nameservers;

        return json_encode($array);
    }

    /**
     * @param string $domain Домен
     * @param string $kind Тип домена (мастер/слейв/натив)
     * @param array $nameservers Массив с нейм серверами
     *
     * @return stdClass
     * @throws  PowerDnsClientException
     */
    public function DomainCreate(string $domain, string $kind, array $nameservers): stdClass
    {
        $this->AddRequestOption('body', $this->BildJsonDomainCreate($domain, $kind, $nameservers));

        $Response = $this->SendHttpRequest('POST', 'servers/' . $this->server_id . '/zones');

        unset($Response->url);
        unset($Response->account);

        return $Response;
    }

    /**
     * @param string $domain доменное имя
     *
     * @return Collection[] Список записей домена
     * @throws  PowerDnsClientException
     */
    public function DomainRecordList(string $domain): Collection
    {
        $DomainRecordList = collect([]);
        $Response = $this->SendHttpRequest('GET', 'servers/' . $this->server_id . '/zones/' . $domain);

        for ($i = 0; $i < count($Response->rrsets); $i++) {
            $DomainRecordList->push(collect([
                'type' => $Response->rrsets[$i]->type,
                'name' => $Response->rrsets[$i]->name,
                'records' => $Response->rrsets[$i]->records,
                'ttl' => $Response->rrsets[$i]->ttl,
                'comments' => $Response->rrsets[$i]->comments,
            ]));
        }

        return $DomainRecordList;

    }

    /**
     * @param string $domain
     *
     * @return array
     * @throws PowerDnsClientException
     */
    public function DomainRecordFormatedList(string $domain): array
    {
        $DomainRecordList = [];

        $Response = $this->SendHttpRequest('GET', 'servers/' . $this->server_id . '/zones/' . $domain);

        for ($i = 0; $i < count($Response->rrsets); $i++) {
            $DomainRecordList[$Response->rrsets[$i]->type][] = [
                'name' => $Response->rrsets[$i]->name,
                'records' => $this->RecordsContentFormatted((string)$Response->rrsets[$i]->type, (array)$Response->rrsets[$i]->records),
                'ttl' => $Response->rrsets[$i]->ttl,
                'comments' => $Response->rrsets[$i]->comments,
            ];
        }

        return $DomainRecordList;
    }

    /**
     * @param string $Type Тип записи
     * @param array $Records Содержимое записи
     *
     * @return array
     */
    private function RecordsContentFormatted(string $Type, array $Records): array
    {
        $formatted = [];

        switch ($Type) {
            case 'SRV';
                for ($i = 0; $i < count($Records); $i++) {
                    $result = explode(" ", $Records[$i]->content);
                    $formatted[$i]['Priority'] = $result[0];
                    $formatted[$i]['Weight'] = $result[1];
                    $formatted[$i]['Port'] = $result[2];
                    $formatted[$i]['Target'] = $result[3];
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'A';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['ipv4'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'AAAA';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['ipv6'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'CNAME';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['CanonicalName'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'NS';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['NameServer'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'MX';
                for ($i = 0; $i < count($Records); $i++) {
                    $result = explode(" ", $Records[$i]->content);
                    $formatted[$i]['Priority'] = $result[0];
                    $formatted[$i]['MailRelay'] = $result[1];
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'PTR';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['HostName'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;
            case 'TXT';
                for ($i = 0; $i < count($Records); $i++) {
                    $formatted[$i]['Text'] = $Records[$i]->content;
                    $formatted[$i]['disabled'] = $Records[$i]->disabled;
                }
                break;

            default;
                $formatted = $Records;
        }

        return $formatted;
    }

    /**
     * @return stdClass
     * @throws PowerDnsClientException
     */
    public function DomainList(): stdClass
    {
        $Response = $this->SendHttpRequest('GET', 'servers/' . $this->server_id . '/zones');

        foreach ($Response as &$item) {
            unset($item->account);
            unset($item->url);
        }

        return $Response;
    }

    /**
     * @param string $Method Метод запроса
     * @param string $Url URL к которому необходимо выполнить запрос
     *
     * @return mixed Декодированный из json'a ответ
     * @throws PowerDnsClientException
     *
     */
    private function SendHttpRequest(string $Method, string $Url): stdClass
    {
        try {
            $req = $this->pdns_client->createRequest($Method, $Url, $this->request_option);
            $res = $this->pdns_client->send($req);
        } catch (RequestException  $e) {
            throw new PowerDnsClientException($e->getResponse()->getBody()->getContents());
        }

        $return = (object)json_decode($res->getBody()->getContents());

        return $return;
    }

}