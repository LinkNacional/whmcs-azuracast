<?php
declare(strict_types=1);

namespace WHMCS\Module\Server\AzuraCast\Admin;

use WHMCS\Module\Server\AzuraCast\AbstractClient;
use WHMCS\Module\Server\AzuraCast\Dto;
use WHMCS\Module\Server\AzuraCast\Dto\ApiKeyDto;
use WHMCS\Module\Server\AzuraCast\Dto\RoleDto;
use WHMCS\Module\Server\AzuraCast\Dto\StationDto;
use WHMCS\Module\Server\AzuraCast\Exception;
use WHMCS\Module\Server\AzuraCast\Exception\AccessDeniedException;
use WHMCS\Module\Server\AzuraCast\Exception\ClientRequestException;
use WHMCS\Module\Server\AzuraCast\Service;

class StationsClient extends AbstractClient
{
    /**
     * @return Dto\StationDto[]
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function list(): array
    {
        $stationsData = $this->request('GET', 'admin/stations');

        $stations = [];
        foreach ($stationsData as $stationData) {
            $stations[] = Dto\StationDto::fromArray($stationData);
        }
        return $stations;
    }

    /**
     * @param int $stationId
     *
     * @return Dto\StationDto
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function get(int $stationId): Dto\StationDto
    {
        $stationData = $this->request(
            'GET',
            sprintf('admin/station/%s', $stationId)
        );

        return Dto\StationDto::fromArray($stationData);
    }

    /**
     * @param Service $serviceDetails
     *
     * @return Dto\StationDto
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function create(Service $serviceDetails): Dto\StationDto
    {
        $stationData = [
            'name' => $serviceDetails->getStationName(),
            'short_name' => $serviceDetails->getStationShortName(),
            'is_enabled' => true,
            'frontend_type' => $serviceDetails->getServerType(),
            'frontend_config' => ['max_listeners' => $serviceDetails->getMaxListeners()],
            'backend_type' => 'liquidsoap',
            'backend_config' => [
                'record_streams_bitrate' => $serviceDetails->getMaxBitrate()
            ],
            'enable_hls' => true,
            'api_history_items' => 5,
            'timezone' => 'UTC',
            'max_bitrate' => $serviceDetails->getMaxBitrate(),
            'max_mounts' => $serviceDetails->getMaxMounts(),
            'max_hls_streams' => $serviceDetails->getMaxHlsStreams()
        ];

        $newStationData =  $this->request(
            'POST',
            'admin/stations',
            ['json' => $stationData]
        );

        return Dto\StationDto::fromArray($newStationData);
    }

    /**
     * @param Service $serviceDetails
     * @param bool $isEnabled
     * @return void
     *
     * @throws AccessDeniedException
     * @throws ClientRequestException
     */
    public function update(Service $serviceDetails, bool $isEnabled = true): void
    {
        $stationData = [
            'id' => $serviceDetails->getStationId(),
            'is_enabled' => $isEnabled,
            'frontend_type' => $serviceDetails->getServerType(),
            'frontend_config' => ['max_listeners' => $serviceDetails->getMaxListeners()],
            'backend_config' => [
                'record_streams_bitrate' => $serviceDetails->getMaxBitrate()
            ],
            'max_bitrate' => $serviceDetails->getMaxBitrate(),
            'max_mounts' => $serviceDetails->getMaxMounts(),
            'max_hls_streams' => $serviceDetails->getMaxHlsStreams()
        ];

        $newStationData =  $this->request(
            'PUT',
            sprintf('admin/station/%s', $serviceDetails->getStationId()),
            ['json' => $stationData]
        );
    }

    /**
     * @param int $stationId
     *
     * @return void
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function delete(int $stationId): void
    {
        $this->request(
            'DELETE',
            sprintf('admin/station/%s', $stationId)
        );
    }

    /**
     * Clone an existing station, then apply the plan's station name and limits.
     *
     * The clone endpoint returns {"status":"created"} without the new station's ID.
     * We locate the cloned station by its short_name, which AzuraCast enforces as unique,
     * so this lookup is race-condition-safe even under concurrent provisioning.
     *
     * @param int      $sourceStationId  ID of the OWH_ template station to clone from
     * @param string   $stationName      Final station name for the new station
     * @param string   $shortName        Derived short_name (must match AzuraCast's own derivation)
     * @param string[] $cloneItems       Components to copy (e.g. playlists, mounts, streamers…)
     *
     * @return Dto\StationDto
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function clone(int $sourceStationId, string $stationName, string $shortName, array $cloneItems): Dto\StationDto
    {
        $response = $this->request(
            'POST',
            sprintf('admin/station/%d/clone', $sourceStationId),
            ['json' => [
                'name'  => $stationName,
                'clone' => $cloneItems,
            ]]
        );

        if (!isset($response['success']) || $response['success'] !== true) {
            throw new ClientRequestException(
                sprintf('Clone request failed for source station %d: %s', $sourceStationId, $response['message'] ?? 'unknown error')
            );
        }

        // Locate by unique short_name and retry briefly for eventual consistency in station listing.
        $attempts = 3;
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $stations = $this->list();
            foreach ($stations as $station) {
                if ($station->getShortName() === $shortName) {
                    return $station;
                }
            }

            if ($attempt < $attempts) {
                usleep($attempt * 300000);
            }
        }

        throw new ClientRequestException(
            sprintf('Cloned station with short_name "%s" not found after %d lookup attempts', $shortName, $attempts)
        );
    }

    /**
     * ----------------------------------------------------------------------------------
     * THIS DOESN'T WORK YET AS AZURACAST DOESN'T HAVE AN API ENDPOINT FOR USER LOGIN
     * ----------------------------------------------------------------------------------
     *
     * @param int $stationId
     *
     * @return Dto\StationDto
     *
     * @throws Exception\AccessDeniedException
     * @throws Exception\ClientRequestException
     */
    public function login(int $stationId): string
    {
        $stationData = $this->request(
            'POST',
            sprintf('station/%s/login', $stationId)
        );

        if (!isset($stationData['login_url'])) {
            throw new ClientRequestException('Station login URL not found in response');
        }

        return $stationData['login_url'];
    }
}
