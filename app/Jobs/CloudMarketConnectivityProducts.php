<?php

namespace App\Jobs;

use Exception;
use JsonMachine\Items;
use App\Helpers\Prices\PriceRules;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Api\CloudMarketAddress;
use Spatie\Multitenancy\Jobs\TenantAware;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CloudMarketConnectivityProducts implements ShouldQueue, TenantAware
{
    use Queueable;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $path, protected $filename, protected $connectivityType, protected CloudMarketAddress $address)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("Fetching produts for: " . $this->address->toString());
        $res = Http::withOptions(['stream' => true])->timeout(360)->withToken(env('ALPNET_API_TOKEN'))
            ->get(
                env('PARTNER_QUOTE_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com') . '/PartnerQuoteService/' . env('PARTNER_QUOTE_SERVICE_ENDPOINT', 'TestNoParams'),
                [
                    'ConnectivityType' => $this->connectivityType,
                    'Address1.Postcode' => $this->address->postCode,
                    'Address1.Latitude' => $this->address->lat,
                    'Address1.Longitude' => $this->address->lon,

                    "Address1.UPRN" =>  $this->address->uprn,
                    "Address1.AddressKey" =>  $this->address->addressKey,
                    "Address1.BuildingName" =>  $this->address->buildingName,
                    "Address1.BuildingNumber" =>  $this->address->buildingNumber,
                    "Address1.SubPremises" =>  $this->address->subPremises,
                    "Address1.Street" =>  $this->address->street,
                    "Address1.Town" =>  $this->address->town,

                    "ManagedOption" => 1,

                    'SupplierApiConnections.Its_Connection.Its_AccessKey' => env('ITS_CONNECTION_ITS_ACCESSKEY', '328736h87y78g823'),
                    'SupplierApiConnections.Virtual1_Connection.Username' => env('VIRTUAL1_CONNECTION_USERNAME', 'JenniferLawrence'),
                    'SupplierApiConnections.Virtual1_Connection.Password' => env('VIRTUAL1_CONNECTION_PASSWORD', 'secretpass'),
                    'SupplierApiConnections.Gamma_Connection.Username' => env('GAMMA_CONNECTION_USERNAME', 'AdamSandler'),
                    'SupplierApiConnections.Gamma_Connection.Password' => env('GAMMA_CONNECTION_PASSWORD', 'specialpass'),
                    'SupplierApiConnections.Gamma_Connection.ClientId' => env('GAMMA_CONNECTION_CLIENTID', '134213'),
                    'SupplierApiConnections.Giacom_Connection.Username' => env('GIACOM_CONNECTION_USERNAME', 'MasterWoo'),
                    'SupplierApiConnections.Giacom_Connection.Password' => env('GIACOM_CONNECTION_PASSWORD', '2345678'),
                ]
            );

        $started_at = now();
        if (!file_exists($this->path . $this->filename)) {
            throw new Exception("File " . $this->path . $this->filename . " not Found!");
        }

        $prices = [];
        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            if (empty($value->providerKey)) {
                continue;
            }

            $prices[] = (array)$value;

            $data = json_encode([
                'wating_task' => false,
                'started_at' => $started_at,
                'finished_at' => null,
                'total' => count($prices),
                'data' => $prices,
            ]);

            $this->write($data,);
        }

        $finalData = json_encode([
            'wating_task' => false,
            'started_at' => $started_at,
            'finished_at' => now(),
            'total' => count($prices),
            'data' => $prices,
        ]);

        $this->write($finalData,);
    }
    private function write($data,)
    {
        Cache::put($this->filename, $data, 86400);

        if (!env('APP_DEBUG')) {
            return;
        }

        if (!file_exists($this->path . $this->filename)) {
            throw new Exception("File  $this->filename not Found!");
        }
        file_put_contents($this->path . $this->filename, $data);
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        Log::error("CloudMarketConnectivityProducts failed with message: " . $exception->getMessage());
    }
}
