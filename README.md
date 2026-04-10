
# WHMCS AzuraCast Server Provisioning Module

A complete AzuraCast provisioning module for WHMCS.
Requested at: https://features.azuracast.com/suggestions/65007/whmcs-module

![Module Settings Screenshot](https://files.catbox.moe/lzskpn.png)

## Features

- Full service lifecycle automation:
    - Station creation
    - Suspension and unsuspension
    - Termination
    - Package upgrade and downgrade
    - Password changes
- Product-level resource limits:
    - Server Type (`icecast` or `shoutcast`)
    - Maximum Bitrate
    - Maximum Mounts
    - Maximum HLS Streams
    - Maximum Listeners
    - Media Storage Limit
    - Recordings Storage Limit
    - Podcasts Storage Limit
- Template-based provisioning:
    - Optional `Clone Source Station ID` to clone an existing template station
    - Copies selected station components (playlists, mounts, remotes, streamers, webhooks)
    - Reapplies the WHMCS plan limits after clone
- Per-product user preferences:
    - Optional `User Language` (locale)
    - Optional `User Time Display` (12h/24h)
- Built-in SSO:
    - Client SSO (`Login as User`)
    - Admin SSO (`Login as Admin`)
    - Redirect host validation for safer login links
- Improved client area dashboard:
    - Live now-playing data
    - Service state (broadcast/frontend/backend)
    - Listener snapshot and live DJ status
    - Quick links (SSO, Public Page, Listen URL, Schedule)
    - Per-storage usage cards (media, recordings, podcasts)
- Provisioning reliability and safer logging:
    - Compensating rollback on failed account creation
    - Atomic persistence of generated IDs after successful provisioning
    - Sensitive data redaction in module logs

## Installation

1. Copy the `azuracast` module directory into your WHMCS installation:

     `modules/servers/azuracast`

2. In WHMCS, go to `System Settings > Servers` and add your AzuraCast server.

3. Create or edit a product and set the module to `AzuraCast`.

4. Add the required product custom field:
     - Field Name: `Station Name`
     - Field Type: `Text Box`
     - Validation: `/^[A-Za-z0-9 ]+$/`
     - Required Field: enabled
     - Show on Order Form: enabled

## WHMCS Setup

### 1) Add a New AzuraCast Server in WHMCS

Go to `System Settings > Servers > Add New Server` and configure:

- Type/Module: `AzuraCast`
- Hostname: your AzuraCast host only (no protocol and no path), for example:
    - `radio.example.com`
- Access Hash: an AzuraCast API key with permissions to manage:
    - Stations
    - Users
    - Roles
    - Storage locations
    - Login tokens
    - Server stats

Then click `Test Connection` and save.

Notes:
- The module builds the API URL as `https://<hostname>/api/`.
- Authentication is sent as `Authorization: Bearer <Access Hash>`.

### 2) Configure the Product Module Settings

In the product `Module Settings` tab:

- Choose module: `AzuraCast`
- Assign the server or server group
- Configure these options:
    - `Maximum Bitrate` (Kbps)
    - `Maximum Mounts`
    - `Maximum HLS Streams`
    - `Media Storage Limit` (MB)
    - `Recordings Storage Limit` (MB)
    - `Podcasts Storage Limit` (MB)
    - `Maximum Listeners`
    - `Server Type` (`icecast` or `shoutcast`)
    - `Clone Source Station ID` (optional)
    - `User Language` (optional)
    - `User Time Display` (optional: `12` or `24`)

Behavior details:
- `Clone Source Station ID`:
    - `0` or blank creates a station from scratch
    - Any valid station ID clones from that template station
- `User Language` and `User Time Display` are optional:
    - Leave blank to let AzuraCast defaults apply

### 3) Custom Field for Station Name

For each AzuraCast product, create this custom field:

- Field Name: `Station Name`
- Field Type: `Text Box`
- Validation: `/^[A-Za-z0-9 ]+$/`
- Required Field: enabled
- Show on Order Form: enabled

If `Station Name` is empty in some provisioning flows, the module can generate a fallback station name automatically.

### 4) SSO Labels in WHMCS

The module exposes both buttons in WHMCS:

- `Login as User`
- `Login as Admin`

## Important Behavior Notes

- One dedicated AzuraCast user is created per WHMCS service.
- On failed create operations, the module attempts to roll back created resources (user, role, station) to avoid partial provisioning leftovers.
- Storage quotas are managed per storage type (media, recordings, podcasts) according to product settings.

## License

"Do whatever you want" license.

Inside the `lib` folder there are modified files from the [official AzuraCast PHP SDK](https://github.com/AzuraCast/php-api-client), licensed under Apache-2.0.

