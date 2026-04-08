<?php
/**
 * WHMCS Azuracast Provisoioning Module
 *
 * This module allows you to provision AzuraCast instances from WHMCS
 *
 * When setting up a new AzuraCast product in WHMCS, you will need to set the following Custom Fiels:
 * Field Name: Station Name
 * Field Type: Text Box
 * Field Description: The Station Name - English Characters, Numbers and Spaces Only.
 * Validation: /^[A-Za-z0-9 ]+$/
 * Required Field, Show on Order Form
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

const AZURACAST_UPDATE_USER_PASSWORD_ON_ANOTHER_STATION_CREATION = false;
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
    $existingUserIdWithAddedRole = null; // set when an EXISTING user was updated
    $rolesBeforeUpdate          = null; // the existing user's roles before we modified them

    try {
        // Create a new Station
        /** @var \WHMCS\Module\Server\AzuraCast\Dto\StationDto $station */
        $station = $azuracast->admin()->stations()->create($service);
        $createdStationId = $station->getId();

        // Stage IDs in-memory (not yet saved to DB) so StorageClient can read them
        $service->setStationId($station->getId());
        $service->setMediaStorageId($station->getMediaStorageId());
        $service->setRecordingsStorageId($station->getRecordingsStorageId());
        $service->setPodcastsStorageId($station->getPodcastsStorageId());

        // Modify Station's Storage Quota for each type
        $azuracast->admin()->storage()->update($service);

        // Create a role for this station
        $role = $azuracast->admin()->roles()->create("Station {$station->getId()} Role", [], [$station->getId() => ["manage station automation", "manage station profile", "manage station broadcasting", "manage station media", "delete station media", "manage station mounts", "manage station podcasts", "manage station remotes", "manage station streamers", "manage station web hooks", "view station management", "view station reports", "view station logs"]]);
        $createdRoleId = $role->getId();
        $service->setRoleId($role->getId());

        // Look for other provisioned services at the same server
        // (Which means there's already an AzuraCast user associated with the client)
        $user = null;
        $otherServices = azuracast_GetOtherActiveServicesAtSameServerForServiceModel($service->getModel());
        if ($otherServices->isNotEmpty())
        {
            $azuracastUserId = $otherServices->first()->serviceProperties->get('userId');
            $user = $azuracast->admin()->users()->get($azuracastUserId);
        }

//        if ($user === null) {
//            // Look for existing user with the same email address
//            $user = $azuracast->admin()->users()->searchByEmail($service->getUserEmail());
//        }

        // If user doesn't exists, create it
        if ($user === null) {
            $user = $azuracast->admin()->users()->create(
                $service->getUserEmail(),
                $service->getPassword(),
                $service->getUserFullName(),
                'en_US',
                [['id' => $role->getId()]]
            );
            $createdUserId = $user->getId();
        }
        else {
            // Capture the current roles before modifying, for rollback purposes
            $rolesBeforeUpdate = azuracast_GetCurrentUserRolesArray($user->getRoles());
            $existingUserIdWithAddedRole = $user->getId();

            // Update user's role
            $newRoles = $rolesBeforeUpdate;
            $newRoles[] = ['id' => $role->getId()];
            $user = $azuracast->admin()->users()->update(
                $user->getId(),
                $service->getUserEmail(),
                AZURACAST_UPDATE_USER_PASSWORD_ON_ANOTHER_STATION_CREATION ? $service->getPassword() : '',
                $service->getUserFullName(),
                'en_US',
                $newRoles,
                $user->getCreatedAt(),
            );

            if (AZURACAST_UPDATE_USER_PASSWORD_ON_ANOTHER_STATION_CREATION)
            {
                // Update the new password for all other related services
                // This means the existing AzuraCast user's password will be changed
                // This is inconvinient, but we need to do it IF we want to keep the password in WHMCS in sync with AzuraCast
                $otherServices->each(function (WHMCS\Service\Service $otherService) use ($service) {
                    /** @var \Illuminate\Database\Eloquent\Model $otherService */
                    $otherService->serviceProperties->save(['Password' => $service->getPassword()]);
                });
            }
        }
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

        if ($existingUserIdWithAddedRole !== null && $rolesBeforeUpdate !== null) {
            try {
                // Restore the user's roles to their state before we modified them
                $currentUser = $azuracast->admin()->users()->get($existingUserIdWithAddedRole);
                $azuracast->admin()->users()->update(
                    $currentUser->getId(),
                    $currentUser->getEmail(),
                    '',
                    $currentUser->getName(),
                    $currentUser->getLocale(),
                    $rolesBeforeUpdate,
                    $currentUser->getCreatedAt(),
                );
            } catch (Exception $rollbackEx) {
                logModuleCall('azuracast', 'CreateAccount_rollback_user_role', azuracast_SanitizeParams($params), $rollbackEx->getMessage(), $rollbackEx->getTraceAsString());
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

        // Check if WHMCS client has another service
        // If he doesn't, remove the user
        $otherServices = azuracast_GetOtherActiveServicesAtSameServerForServiceModel($service->getModel());
        if ($otherServices->isEmpty())
        {
            $azuracast->admin()->users()->delete($service->getUserId());
        }

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
            $currentUser->getEmail(),
            $service->getPassword(),
            $currentUser->getName(),
            $currentUser->getLocale(),
            azuracast_GetCurrentUserRolesArray($currentUser->getRoles()),
            $currentUser->getCreatedAt(),
        );

        // Update the new password for all other related services
        $newPassword = $service->getPassword();
        $otherServices = azuracast_GetOtherActiveServicesAtSameServerForServiceModel($service->getModel());
        if ($otherServices->isNotEmpty())
        {
            $otherServices->each(function (WHMCS\Service\Service $otherService) use ($newPassword) {
                /** @var \Illuminate\Database\Eloquent\Model $otherService */
                $otherService->serviceProperties->save(['Password' => $newPassword]);
            });
        }

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
