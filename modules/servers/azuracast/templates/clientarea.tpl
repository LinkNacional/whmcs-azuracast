<style>
    .azuracast-dashboard {
        --az-accent: #0f766e;
        --az-accent-soft: #dff7f1;
        --az-ink: #12212b;
        --az-muted: #667684;
        --az-surface: #ffffff;
        --az-surface-alt: #f4f7f8;
        --az-border: #d9e3e6;
        --az-live: #0f766e;
        --az-warn: #b7791f;
        --az-offline: #b42318;
        color: var(--az-ink);
    }

    .azuracast-hero {
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.16), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #eef7f5 100%);
        border: 1px solid var(--az-border);
        border-radius: 22px;
        box-shadow: 0 20px 45px rgba(16, 31, 43, 0.08);
        overflow: hidden;
        padding: 26px;
        position: relative;
    }

    .azuracast-hero:before {
        background: linear-gradient(90deg, #0f766e, #14b8a6);
        content: "";
        height: 4px;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
    }

    .azuracast-status-pill {
        border-radius: 999px;
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        padding: 7px 12px;
        text-transform: uppercase;
    }

    .azuracast-status-pill.is-live,
    .azuracast-card-accent.is-live,
    .azuracast-meter-fill.is-live {
        background: rgba(15, 118, 110, 0.12);
        color: var(--az-live);
    }

    .azuracast-status-pill.is-warning,
    .azuracast-card-accent.is-warning,
    .azuracast-meter-fill.is-warning {
        background: rgba(183, 121, 31, 0.14);
        color: var(--az-warn);
    }

    .azuracast-status-pill.is-offline,
    .azuracast-card-accent.is-offline,
    .azuracast-meter-fill.is-offline {
        background: rgba(180, 35, 24, 0.12);
        color: var(--az-offline);
    }

    .azuracast-status-pill.is-muted,
    .azuracast-card-accent.is-muted,
    .azuracast-meter-fill.is-muted {
        background: rgba(102, 118, 132, 0.12);
        color: var(--az-muted);
    }

    .azuracast-heading {
        font-size: 34px;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.05;
        margin: 12px 0 10px;
    }

    .azuracast-subheading {
        color: var(--az-muted);
        font-size: 15px;
        line-height: 1.7;
        margin: 0;
        max-width: 62ch;
    }

    .azuracast-panel {
        background: var(--az-surface);
        border: 1px solid var(--az-border);
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(16, 31, 43, 0.05);
        height: 100%;
        margin-top: 24px;
        padding: 22px;
    }

    .azuracast-panel-title {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.1em;
        margin: 0 0 18px;
        text-transform: uppercase;
    }

    .azuracast-artwork {
        background: linear-gradient(160deg, #d7ece8, #f7fafb);
        border-radius: 18px;
        height: 210px;
        object-fit: cover;
        width: 100%;
    }

    .azuracast-artwork-placeholder {
        align-items: center;
        background: linear-gradient(160deg, #dce9ec, #f7fafb);
        border-radius: 18px;
        color: var(--az-muted);
        display: flex;
        font-size: 14px;
        font-weight: 700;
        height: 210px;
        justify-content: center;
        text-transform: uppercase;
    }

    .azuracast-track-title {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin: 18px 0 8px;
    }

    .azuracast-track-meta,
    .azuracast-copy,
    .azuracast-mini-meta,
    .azuracast-package-value {
        color: var(--az-muted);
    }

    .azuracast-copy {
        font-size: 14px;
        line-height: 1.7;
        margin: 0;
    }

    .azuracast-kpi {
        background: var(--az-surface-alt);
        border: 1px solid var(--az-border);
        border-radius: 16px;
        margin-top: 16px;
        padding: 14px 16px;
    }

    .azuracast-kpi-label,
    .azuracast-package-label {
        color: var(--az-muted);
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .azuracast-kpi-value {
        display: block;
        font-size: 21px;
        font-weight: 800;
        margin-top: 5px;
    }

    .azuracast-shortcuts {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .azuracast-shortcut {
        border-radius: 999px;
        display: inline-block;
        font-size: 13px;
        font-weight: 700;
        padding: 11px 16px;
        text-decoration: none !important;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .azuracast-shortcut:hover,
    .azuracast-shortcut:focus {
        box-shadow: 0 10px 18px rgba(16, 31, 43, 0.1);
        transform: translateY(-1px);
    }

    .azuracast-shortcut.is-primary {
        background: var(--az-accent);
        color: #ffffff !important;
    }

    .azuracast-shortcut.is-secondary {
        background: var(--az-accent-soft);
        color: var(--az-accent) !important;
    }

    .azuracast-card-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .azuracast-card {
        background: linear-gradient(180deg, #ffffff, #f8fbfb);
        border: 1px solid var(--az-border);
        border-radius: 18px;
        min-height: 138px;
        overflow: hidden;
        padding: 16px;
        position: relative;
    }

    .azuracast-card-accent {
        border-radius: 999px;
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        margin-bottom: 16px;
        padding: 5px 9px;
        text-transform: uppercase;
    }

    .azuracast-card-value {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .azuracast-mini-meta {
        font-size: 13px;
        line-height: 1.5;
        margin: 0;
    }

    .azuracast-stream-box {
        background: linear-gradient(135deg, #12212b, #1f4c54);
        border-radius: 18px;
        color: #f4ffff;
        min-height: 220px;
        padding: 20px;
    }

    .azuracast-stream-label {
        color: rgba(255, 255, 255, 0.72);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .azuracast-stream-value {
        color: #ffffff;
        display: block;
        font-size: 16px;
        font-weight: 700;
        margin-top: 6px;
        overflow-wrap: anywhere;
    }

    .azuracast-stream-link {
        color: #9af3e4 !important;
        text-decoration: none !important;
    }

    .azuracast-player {
        margin-top: 18px;
        width: 100%;
    }

    .azuracast-meter {
        background: #eaf1f2;
        border-radius: 999px;
        height: 10px;
        margin: 14px 0 8px;
        overflow: hidden;
    }

    .azuracast-meter-fill {
        display: block;
        height: 100%;
    }

    .azuracast-quota-copy {
        display: flex;
        justify-content: space-between;
        gap: 16px;
    }

    .azuracast-package-row {
        border-top: 1px solid var(--az-border);
        padding: 12px 0;
    }

    .azuracast-package-row:first-child {
        border-top: 0;
        padding-top: 0;
    }

    .azuracast-warning {
        background: #fff7e8;
        border: 1px solid #f3ddaa;
        border-radius: 16px;
        color: #7d5b12;
        margin-top: 18px;
        padding: 14px 16px;
    }

    @media (max-width: 991px) {
        .azuracast-card-grid {
            grid-template-columns: 1fr;
        }

        .azuracast-heading {
            font-size: 28px;
        }
    }
</style>

<div class="azuracast-dashboard">
    <div class="azuracast-hero">
        <span class="azuracast-status-pill is-{$dashboard.statusVariant|escape:'html'}">{$dashboard.statusText|escape:'html'}</span>
        <h1 class="azuracast-heading">{$dashboard.stationName|escape:'html'}</h1>
        <p class="azuracast-subheading">
            {if $dashboard.description}
                {$dashboard.description|escape:'html'}
            {else}
                Station overview for {$dashboard.shortName|escape:'html'}.
            {/if}
        </p>

        {if $dashboard.warning}
            <div class="azuracast-warning">{$dashboard.warning|escape:'html'}</div>
        {/if}
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">On The Air</h2>
                <div class="row">
                    <div class="col-sm-5">
                        {if $dashboard.artworkUrl}
                            <img class="azuracast-artwork" src="{$dashboard.artworkUrl|escape:'html'}" alt="Album art for {$dashboard.currentTrackTitle|escape:'html'}">
                        {else}
                            <div class="azuracast-artwork-placeholder">No Artwork</div>
                        {/if}
                    </div>
                    <div class="col-sm-7">
                        <div class="azuracast-track-title">{$dashboard.currentTrackTitle|escape:'html'}</div>
                        <p class="azuracast-track-meta">{$dashboard.currentTrackArtist|escape:'html'}</p>
                        <p class="azuracast-copy">
                            {if $dashboard.hasLiveBroadcast && $dashboard.liveStreamerName}
                                Live DJ connected: {$dashboard.liveStreamerName|escape:'html'}.
                            {elseif $dashboard.upcomingShow}
                                Upcoming show: {$dashboard.upcomingShow|escape:'html'}.
                            {else}
                                Live metadata will appear here as soon as AzuraCast reports it.
                            {/if}
                        </p>

                        <div class="row">
                            <div class="col-xs-6">
                                <div class="azuracast-kpi">
                                    <span class="azuracast-kpi-label">Listeners</span>
                                    <span class="azuracast-kpi-value">{$dashboard.listeners|escape:'html'}</span>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="azuracast-kpi">
                                    <span class="azuracast-kpi-label">Station Code</span>
                                    <span class="azuracast-kpi-value">{$dashboard.shortName|escape:'html'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">Quick Shortcuts</h2>
                <div class="azuracast-shortcuts">
                    {foreach from=$dashboard.shortcuts item=shortcut}
                        <a
                            class="azuracast-shortcut is-{$shortcut.accent|escape:'html'}"
                            href="{$shortcut.url|escape:'html'}"
                            {if $shortcut.external}target="_blank" rel="noopener noreferrer"{/if}
                        >
                            {$shortcut.label|escape:'html'}
                        </a>
                    {/foreach}
                </div>
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">Station Status</h2>
                <div class="azuracast-card-grid">
                    {foreach from=$dashboard.serviceCards item=card}
                        <div class="azuracast-card">
                            <span class="azuracast-card-accent is-{$card.variant|escape:'html'}">{$card.label|escape:'html'}</span>
                            <div class="azuracast-card-value">{$card.value|escape:'html'}</div>
                            <p class="azuracast-mini-meta">{$card.meta|escape:'html'}</p>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">Stream On-Air</h2>
                <div class="azuracast-stream-box">
                    <div>
                        <span class="azuracast-stream-label">Primary Stream</span>
                        {if $dashboard.streamUrl}
                            <a class="azuracast-stream-value azuracast-stream-link" href="{$dashboard.streamUrl|escape:'html'}" target="_blank" rel="noopener noreferrer">{$dashboard.streamUrl|escape:'html'}</a>
                        {else}
                            <span class="azuracast-stream-value">No public stream URL is currently available.</span>
                        {/if}
                    </div>

                    <div style="margin-top:18px;">
                        <span class="azuracast-stream-label">Public Page</span>
                        {if $dashboard.publicPageUrl}
                            <a class="azuracast-stream-value azuracast-stream-link" href="{$dashboard.publicPageUrl|escape:'html'}" target="_blank" rel="noopener noreferrer">Open public station page</a>
                        {else}
                            <span class="azuracast-stream-value">No public page published for this station.</span>
                        {/if}
                    </div>

                    <div style="margin-top:18px;">
                        <span class="azuracast-stream-label">Stream Player</span>
                        {if $dashboard.playerUrl}
                            <audio class="azuracast-player" controls preload="none" src="{$dashboard.playerUrl|escape:'html'}">
                                Your browser does not support the audio element.
                            </audio>
                        {else}
                            <span class="azuracast-stream-value">Player unavailable until AzuraCast exposes a listen URL.</span>
                        {/if}
                    </div>
                </div>
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">Storage Usage</h2>
                {if $dashboard.quotaCards}
                    {foreach from=$dashboard.quotaCards item=quota}
                        <div class="azuracast-card" style="min-height:0; margin-bottom:14px;">
                            <span class="azuracast-card-accent is-{$quota.variant|escape:'html'}">{$quota.label|escape:'html'}</span>
                            <div class="azuracast-quota-copy">
                                <div>
                                    <div class="azuracast-card-value" style="font-size:24px;">{$quota.used|escape:'html'}</div>
                                    <p class="azuracast-mini-meta">Used of {$quota.quota|escape:'html'}</p>
                                </div>
                                <div class="text-right">
                                    <div class="azuracast-card-value" style="font-size:24px;">{$quota.usedPercent|escape:'html'}%</div>
                                    {if $quota.meta}
                                        <p class="azuracast-mini-meta">{$quota.meta|escape:'html'} free</p>
                                    {/if}
                                </div>
                            </div>
                            <div class="azuracast-meter">
                                <span class="azuracast-meter-fill is-{$quota.variant|escape:'html'}" style="width: {$quota.usedPercent|escape:'html'}%;"></span>
                            </div>
                        </div>
                    {/foreach}
                {else}
                    <p class="azuracast-copy">Quota metrics are not available from the API right now.</p>
                {/if}
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">Package Information</h2>
                {foreach from=$productConfigOptions key=configName item=configValue}
                    <div class="azuracast-package-row">
                        <span class="azuracast-package-label">{$configName|escape:'html'}</span>
                        <span class="azuracast-package-value">{$configValue|escape:'html'}</span>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>