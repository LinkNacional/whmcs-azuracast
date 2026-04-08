<?php
/**
 * WHMCS Azuracast Provisoioning Module
 *
 * This module allows you to provision AzuraCast instances from WHMCS
 *
 * When setting up a new AzuraCast product in WHMCS, you can set the following Custom Fiels:
 * Field Name: Station Name
 * Field Type: Text Box
 * Field Description: The Station Name - English Characters, Numbers and Spaces Only.
 * Validation: /^[A-Za-z0-9 ]+$/
 * Optional field. If empty, the module auto-generates a deterministic station name.
 *
 * @written_by Yahav [DOT] Shasha [AT] gmail [DOT] com
 * @license Within the Lib folder there are some modified files from the [official AzuraCast PHP SDK](https://github.com/AzuraCast/php-api-client) (Apache-2.0 license)
 * @license The rest is under "Do whatever you want" License
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Module\Server\AzuraCast\Client;
use WHMCS\Module\Server\AzuraCast\Dto\RoleDto;
use WHMCS\Module\Server\AzuraCast\Service;
use WHMCS\Database\Capsule;
/**
 * Define module related meta data.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function azuracast_MetaData()
{
    return array(
        'DisplayName' => 'AzuraCast',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => 'Login as User',
        'AdminSingleSignOnLabel' => 'Login as Admin',

    );
}

/**
 * Define product configuration options.
 *
 * @see https://developers.whmcs.com/provisioning-modules/config-options/
 *
 * @return array
 */
function azuracast_ConfigOptions()
{
    return array(
        'Maximum Bitrate' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '128',
            'Description' => 'Enter in Kbps',
        ],
        'Maximum Mounts' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '2',
            'Description' => 'Maximum allowed Mount Points',
        ],
        'Maximum HLS Streams' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '2',
            'Description' => 'Maximum allowed HLS Streams',
        ],
        'Media Storage Limit' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '1000',
            'Description' => 'Enter in Mb',
        ],
        'Recordings Storage Limit' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '1000',
            'Description' => 'Enter in Mb',
        ],
        'Podcasts Storage Limit' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '1000',
            'Description' => 'Enter in Mb',
        ],
        'Maximum Listeners' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '100',
            'Description' => 'Maximum Number of Listeners',
        ],
        'Server Type' => [
            "FriendlyName" => "Server Type",
            "Type" => "dropdown",
            "Options" => "icecast,shoutcast",
            "Description" => "The Frontend Type of the Station",
            "Default" => "icecast",
        ],
        'Clone Source Station ID' => [
            'Type' => 'text',
            'Size' => '10',
            'Default' => '0',
            'Description' => 'Numeric ID of the OWH_ template station to clone. Enter 0 (or leave blank) to create a new station from scratch.',
        ],
        'User Language' => [
            'FriendlyName' => 'User Language',
            'Type' => 'dropdown',
            'Options' => ',en_US,pt_BR,es_ES,de_DE,fr_FR,it_IT,nl_NL,pl_PL,tr_TR,ru_RU,ja_JP,ko_KR,zh_CN,cs_CZ,nb_NO,el_GR,sv_SE,uk_UA',
            'Description' => 'Optional. If empty, do not send locale and let AzuraCast use its default.',
            'Default' => '',
        ],
        'User Time Display' => [
            'FriendlyName' => 'User Time Display',
            'Type' => 'dropdown',
            'Options' => ',12,24',
            'Description' => 'Optional. If empty, do not send show_24_hour_time and let AzuraCast use its default.',
            'Default' => '',
        ]
    );
}

/**
 * Provision a new instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_CreateAccount(array $params)
{
    $service = new Service($params);
    $azuracast = azuracast_ApiClient($params);

    // Rollback tracking: record what was successfully created so we can undo on failure.
    $createdStationId           = null;
    $createdRoleId              = null;
    $createdUserId              = null;  // set only when a NEW user is created

    try {
        // Create or clone a Station depending on whether a template station ID is configured
        /** @var \WHMCS\Module\Server\AzuraCast\Dto\StationDto $station */
        $cloneSourceId = $service->getCloneSourceStationId();
        if ($cloneSourceId !== null) {
            // Clone the template station and copy selected components.
            // Storage locations are intentionally excluded so each client gets isolated storage.
            // Permissions are excluded because the module creates a fresh role for this service.
            $station = $azuracast->admin()->stations()->clone(
                $cloneSourceId,
                $service->getStationName(),
                $service->getStationShortName(),
                ['playlists', 'mounts', 'remotes', 'streamers', 'webhooks']
            );
            $createdStationId = $station->getId();

            // Stage IDs in-memory (not yet saved to DB) so StorageClient can read them
            $service->setStationId($station->getId());
            $service->setMediaStorageId($station->getMediaStorageId());
            $service->setRecordingsStorageId($station->getRecordingsStorageId());
            $service->setPodcastsStorageId($station->getPodcastsStorageId());

            // Override plan limits — clone copied the template's limits, which must be replaced
            $azuracast->admin()->stations()->update($service);
        } else {
            $station = $azuracast->admin()->stations()->create($service);
            $createdStationId = $station->getId();

            // Stage IDs in-memory (not yet saved to DB) so StorageClient can read them
            $service->setStationId($station->getId());
            $service->setMediaStorageId($station->getMediaStorageId());
            $service->setRecordingsStorageId($station->getRecordingsStorageId());
            $service->setPodcastsStorageId($station->getPodcastsStorageId());
        }

        // Modify Station's Storage Quota for each type
        $azuracast->admin()->storage()->update($service);

        // Create a role for this station
        $role = $azuracast->admin()->roles()->create("Station {$station->getId()} Role", [], [$station->getId() => ["manage station automation", "manage station profile", "manage station broadcasting", "manage station media", "delete station media", "manage station mounts", "manage station podcasts", "manage station remotes", "manage station streamers", "manage station web hooks", "view station management", "view station reports", "view station logs"]]);
        $createdRoleId = $role->getId();
        $service->setRoleId($role->getId());

        // Create a dedicated user for this service only.
        $user = $azuracast->admin()->users()->create(
            $service->getTechnicalEmail(),
            $service->getPassword(),
            $service->getUserFullName(),
            [['id' => $role->getId()]],
            $service->getUserLocale(),
            $service->getUserShow24HourTime()
        );
        $createdUserId = $user->getId();
        $service->setUserId($user->getId());

        // All API calls succeeded — now atomically persist all IDs to the database
        $service->commitIds();

    } catch (Exception $e) {
        // Compensating transactions: undo AzuraCast resources in reverse creation order.
        // Each step is isolated so a rollback failure does not prevent subsequent rollbacks.

        if ($createdUserId !== null) {
            try {
                $azuracast->admin()->users()->delete($createdUserId);
            } catch (Exception $rollbackEx) {
                logModuleCall('azuracast', 'CreateAccount_rollback_user', azuracast_SanitizeParams($params), $rollbackEx->getMessage(), $rollbackEx->getTraceAsString());
            }
        }

        if ($createdRoleId !== null) {
            try {
                $azuracast->admin()->roles()->delete($createdRoleId);
            } catch (Exception $rollbackEx) {
                logModuleCall('azuracast', 'CreateAccount_rollback_role', azuracast_SanitizeParams($params), $rollbackEx->getMessage(), $rollbackEx->getTraceAsString());
            }
        }

        if ($createdStationId !== null) {
            try {
                $azuracast->admin()->stations()->delete($createdStationId);
            } catch (Exception $rollbackEx) {
                logModuleCall('azuracast', 'CreateAccount_rollback_station', azuracast_SanitizeParams($params), $rollbackEx->getMessage(), $rollbackEx->getTraceAsString());
            }
        }

        // Record the original error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Suspend an instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_SuspendAccount(array $params)
{
    try {
        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);

        // Update the station
        $azuracast->admin()->stations()->update($service, false);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_UnsuspendAccount(array $params)
{
    try {
        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);

        // Update the station
        $azuracast->admin()->stations()->update($service, true);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_TerminateAccount(array $params)
{
    try {

        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);

        // Remove User Role
        $azuracast->admin()->roles()->delete($service->getRoleId());

        // Remove Station
        $azuracast->admin()->stations()->delete($service->getStationId());

        // Remove the dedicated user for this service.
        $azuracast->admin()->users()->delete($service->getUserId());

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Change the password for an instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_ChangePassword(array $params)
{
    try {
        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);

        $currentUser = $azuracast->admin()->users()->get($service->getUserId());

        // Update the user's password
        $user = $azuracast->admin()->users()->update(
            $currentUser->getId(),
            null,
            $service->getPassword(),
            $currentUser->getName(),
            azuracast_GetCurrentUserRolesArray($currentUser->getRoles()),
            $currentUser->getCreatedAt(),
            $currentUser->getLocale() !== '' ? $currentUser->getLocale() : null,
            null
        );

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Upgrade or downgrade an instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function azuracast_ChangePackage(array $params)
{
    try {
        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);

        // Update the station with the new service
        $azuracast->admin()->stations()->update($service, true);

        // Modify Station's Storage Quota for each type
        $storage = $azuracast->admin()->storage()->update($service);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Test connection with the given server parameters.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function azuracast_TestConnection(array $params)
{
    try {
        $azuracast = azuracast_ApiClient($params);
        $azuracast->admin()->serverStats()->get();

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * @param array $params common module parameters
 *
 * @return array
 *@see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 */

function azuracast_ServiceSingleSignOn(array $params)
{
    $return = array(
        'success' => false,
    );

    try {

        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);
        $loginUrl = $azuracast->admin()->users()->getLoginLink($service->getUserId());
        azuracast_ValidateSsoRedirectUrl($loginUrl, $params['serverhostname']);

        $return = array(
            'success' => true,
            'redirectTo' => $loginUrl,
        );

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $return['error'] = $e->getMessage();
        return $return;
    }

    return $return;
}

/**
 * Perform single sign-on for a server.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function azuracast_AdminSingleSignOn(array $params)
{
    $return = array(
        'success' => false,
    );

    try {

        $service = new Service($params);
        $azuracast = azuracast_ApiClient($params);
        $administratorUserId = $azuracast->admin()->users()->getAdministratorUserIdFromToken();
        $loginUrl = $azuracast->admin()->users()->getLoginLink($administratorUserId);
        azuracast_ValidateSsoRedirectUrl($loginUrl, $params['serverhostname']);

        $return = array(
            'success' => true,
            'redirectTo' => $loginUrl,
        );

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'azuracast',
            __FUNCTION__,
            azuracast_SanitizeParams($params),
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $return['error'] = $e->getMessage();
        return $return;
    }

    return $return;
}

function azuracast_ClientArea($params)
{
    $service = new Service($params);
    $productConfigOptions = [
        'Maximum Bitrate' => $service->getMaxBitrate() . ' Kbps',
        'Maximum Mounts' => $service->getMaxMounts() . ' Mounts',
        'Maximum HLS Streams' => $service->getMaxHlsStreams() . ' HLS Streams',
        'Media Storage Limit' => $service->getMediaStorage() . ' MB',
        'Recordings Storage Limit' => $service->getRecordingsStorage() . ' MB',
        'Podcasts Storage Limit' => $service->getPodcastsStorage() . ' MB',
        'Maximum Listeners' => $service->getMaxListeners() . ' Listeners',
        'Server Type' => $service->getServerType(),
    ];
    
    return array(
        'templatefile' => 'clientarea',
        'vars' => array(
            'params' => $params,
            'productConfigOptions' => $productConfigOptions,
        ),
    );
}

function azuracast_ApiClient($params) : Client
{
    $host = 'https://' . $params['serverhostname'];
    $apiKey = $params['serveraccesshash'];
    return Client::create($host, $apiKey);
}

/**
 * @param RoleDto[] $existingUserRoles
 * @return array
 */
function azuracast_GetCurrentUserRolesArray(array $existingUserRoles)
{
    $roles = [];
    foreach ($existingUserRoles as $existingUserRole) {
        $roles[] = ['id' => $existingUserRole->getId()];
    }

    return $roles;
}

function azuracast_GetOtherActiveServicesAtSameServerForServiceModel(WHMCS\Service\Service $serviceModel): \Illuminate\Database\Eloquent\Collection
{
    $currentServerId = $serviceModel->server;
    $currentServiceId = $serviceModel->id;

    return $serviceModel->client->services()->whereIn('domainStatus', ['Active', 'Suspended'])->where('server', $currentServerId)->where('id', '!=', $currentServiceId)->get();
}

/**
 * Redacts sensitive fields from $params before passing to logModuleCall.
 * Removes secrets (API key, passwords) and PII (client details) from log output.
 */
function azuracast_SanitizeParams(array $params): array
{
    $sensitiveKeys = ['serveraccesshash', 'password', 'serverpassword', 'clientsdetails'];
    foreach ($sensitiveKeys as $key) {
        if (array_key_exists($key, $params)) {
            $params[$key] = '[REDACTED]';
        }
    }
    return $params;
}

/**
 * Validates that a login URL returned by the AzuraCast API points to the expected server host.
 * Prevents open redirect attacks if the remote API is compromised or misconfigured.
 * Note: if AzuraCast runs behind a CDN/proxy with a different hostname than $expectedHost,
 * this check will fail. In that case, update $expectedHost to match the actual redirect hostname.
 *
 * @throws \RuntimeException if the URL host does not match the expected server hostname.
 */
function azuracast_ValidateSsoRedirectUrl(string $url, string $expectedHost): void
{
    $parsed = parse_url($url);
    if ($parsed === false || !isset($parsed['host'])) {
        throw new \RuntimeException('SSO login URL returned by AzuraCast is invalid or malformed.');
    }
    if (strcasecmp($parsed['host'], $expectedHost) !== 0) {
        throw new \RuntimeException(
            sprintf('SSO login URL host "%s" does not match the configured server hostname "%s".', $parsed['host'], $expectedHost)
        );
    }
}
