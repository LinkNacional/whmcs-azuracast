<style>
    #domain > .row,
    #domain > br {
        display: none !important;
    }
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

    .azuracast-hero-grid {
        align-items: end;
        display: grid;
        gap: 24px;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
    }

    .azuracast-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .azuracast-meta-chip {
        background: rgba(255, 255, 255, 0.84);
        border: 1px solid rgba(15, 118, 110, 0.12);
        border-radius: 999px;
        color: var(--az-ink);
        font-size: 12px;
        font-weight: 700;
        padding: 9px 12px;
    }

    .azuracast-sidecar {
        background: linear-gradient(160deg, #12333b, #1e6161);
        border-radius: 22px;
        color: #f7ffff;
        min-height: 100%;
        overflow: hidden;
        padding: 22px;
        position: relative;
    }

    .azuracast-sidecar:after {
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 40%);
        content: "";
        inset: 0;
        position: absolute;
        pointer-events: none;
    }

    .azuracast-sidecar-label {
        color: rgba(247, 255, 255, 0.72);
        display: block;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .azuracast-sidecar-value {
        display: block;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.05;
        margin-top: 8px;
    }

    .azuracast-sidecar-copy {
        color: rgba(247, 255, 255, 0.74);
        font-size: 14px;
        line-height: 1.6;
        margin: 12px 0 0;
        position: relative;
        z-index: 1;
    }

    .azuracast-signal {
        align-items: end;
        display: flex;
        gap: 8px;
        height: 54px;
        margin-top: 26px;
        position: relative;
        z-index: 1;
    }

    .azuracast-signal span {
        animation: azuracastSignal 1.4s ease-in-out infinite;
        background: linear-gradient(180deg, #8ff3de, #ffffff);
        border-radius: 999px;
        display: block;
        flex: 1;
        opacity: 0.95;
    }

    .azuracast-signal span:nth-child(1) { height: 28%; animation-delay: 0.1s; }
    .azuracast-signal span:nth-child(2) { height: 72%; animation-delay: 0.3s; }
    .azuracast-signal span:nth-child(3) { height: 45%; animation-delay: 0.15s; }
    .azuracast-signal span:nth-child(4) { height: 88%; animation-delay: 0.45s; }
    .azuracast-signal span:nth-child(5) { height: 55%; animation-delay: 0.25s; }
    .azuracast-signal span:nth-child(6) { height: 96%; animation-delay: 0.4s; }

    @keyframes azuracastSignal {
        0%, 100% { transform: scaleY(0.78); }
        50% { transform: scaleY(1); }
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
        /*height: 100%;*/
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
        overflow-wrap: anywhere;
    }

    .azuracast-track-title.is-empty {
        font-size: 22px;
        line-height: 1.2;
    }

    .azuracast-onair-grid {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
    }

    .azuracast-onair-artwork,
    .azuracast-onair-details {
        text-align: left !important;
    }

    .azuracast-onair-details {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .azuracast-onair-details .azuracast-track-title {
        margin-top: 0;
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

    .azuracast-kpi-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 16px;
    }

    .azuracast-kpi {
        height: 100%;
        margin-top: 0;
    }

    @media (max-width: 575px) {
        .azuracast-kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .azuracast-kpi {
        background: var(--az-surface-alt);
        border: 1px solid var(--az-border);
        border-radius: 16px;
        margin-top: 16px;
        padding: 14px 16px;
    }

    .azuracast-onair-metrics {
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(0, 1.8fr) minmax(140px, 0.8fr);
        margin-top: 16px;
    }

    .azuracast-onair-metrics .azuracast-kpi {
        margin-top: 0;
    }

    .azuracast-onair-metrics .azuracast-kpi-value {
        overflow-wrap: anywhere;
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
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .azuracast-shortcut {
        border: 1px solid var(--az-border);
        border-radius: 18px;
        display: block;
        font-size: 13px;
        font-weight: 700;
        min-height: 92px;
        padding: 16px 18px;
        text-decoration: none !important;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .azuracast-shortcut-label {
        display: block;
        font-size: 15px;
        line-height: 1.3;
    }

    .azuracast-shortcut-meta {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-top: 16px;
        opacity: 0.72;
        text-transform: uppercase;
    }

    .azuracast-shortcut:hover,
    .azuracast-shortcut:focus {
        box-shadow: 0 10px 18px rgba(16, 31, 43, 0.1);
        transform: translateY(-1px);
    }

    .azuracast-shortcut.is-primary {
        background: linear-gradient(135deg, var(--az-accent), #14b8a6);
        border-color: transparent;
        color: #ffffff !important;
    }

    .azuracast-shortcut.is-secondary {
        background: linear-gradient(180deg, #ffffff, var(--az-accent-soft));
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

    .azuracast-stream-actions {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 16px;
    }

    .azuracast-stream-tile {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        min-height: 86px;
        padding: 14px;
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
        .azuracast-hero-grid,
        .azuracast-shortcuts,
        .azuracast-stream-actions {
            grid-template-columns: 1fr;
        }

        .azuracast-card-grid {
            grid-template-columns: 1fr;
        }

        .azuracast-heading {
            font-size: 28px;
        }

        .azuracast-onair-artwork {
            margin-bottom: 16px;
        }

        .azuracast-onair-details .azuracast-track-title {
            margin-top: 4px;
        }

        .azuracast-onair-metrics {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="azuracast-dashboard">
    <!-- div class="azuracast-hero">
        <div class="azuracast-hero-grid">
            <div>
                <span class="azuracast-status-pill is-{$dashboard.statusVariant|escape:'html'}">{$dashboard.statusText|escape:'html'}</span>
                <h1 class="azuracast-heading">{$dashboard.stationName|escape:'html'}</h1>
                <p class="azuracast-subheading">
                    {if $dashboard.description}
                        {$dashboard.description|escape:'html'}
                    {else}
                        {$i18n.stationOverviewFor|escape:'html'} {$dashboard.shortName|escape:'html'}.
                    {/if}
                </p>

                <div class="azuracast-hero-meta">
                    <span class="azuracast-meta-chip">{$i18n.shortCode|escape:'html'} {$dashboard.shortName|escape:'html'}</span>
                    <span class="azuracast-meta-chip">{$i18n.listeners|escape:'html'} {$dashboard.listeners|escape:'html'}</span>
                    <span class="azuracast-meta-chip">{$i18n.liveDj|escape:'html'} {if $dashboard.hasLiveBroadcast}{$i18n.connected|escape:'html'}{else}{$i18n.idle|escape:'html'}{/if}</span>
                </div>

                {if $dashboard.warning}
                    <div class="azuracast-warning">{$dashboard.warning|escape:'html'}</div>
                {/if}
            </div>

            <div class="azuracast-sidecar">
                <span class="azuracast-sidecar-label">{$i18n.broadcastSnapshot|escape:'html'}</span>
                <span class="azuracast-sidecar-value">{if $dashboard.hasLiveBroadcast && $dashboard.liveStreamerName}{$dashboard.liveStreamerName|escape:'html'}{elseif $dashboard.upcomingShow}{$dashboard.upcomingShow|escape:'html'}{else}{$i18n.autoDjStandby|escape:'html'}{/if}</span>
                <p class="azuracast-sidecar-copy">
                    {if $dashboard.hasLiveBroadcast && $dashboard.liveStreamerName}
                        {$i18n.liveFeedDrivenBy|escape:'html'} {$dashboard.liveStreamerName|escape:'html'}.
                    {elseif $dashboard.upcomingShow}
                        {$i18n.nextScheduledContent|escape:'html'} {$dashboard.upcomingShow|escape:'html'}.
                    {else}
                        {$i18n.stationReadyMetadata|escape:'html'}
                    {/if}
                </p>
                <div class="azuracast-signal" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div -->

    {if $dashboard.warning}
        <div class="azuracast-warning">{$dashboard.warning|escape:'html'}</div>
    {/if}

    <div class="row">
        <div class="col-lg-7">
            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">{$i18n.quickShortcuts|escape:'html'}</h2>
                <div class="azuracast-shortcuts">
                    {foreach from=$dashboard.shortcuts item=shortcut}
                        <a
                            class="azuracast-shortcut is-{$shortcut.accent|escape:'html'}"
                            href="{$shortcut.url|escape:'html'}"
                            {if $shortcut.external}target="_blank" rel="noopener noreferrer"{/if}
                        >
                            <span class="azuracast-shortcut-label">{$shortcut.label|escape:'html'}</span>
                            <span class="azuracast-shortcut-meta">{if $shortcut.external}{$i18n.openExternalPage|escape:'html'}{else}{$i18n.openSecureSession|escape:'html'}{/if}</span>
                        </a>
                    {/foreach}
                    <a class="azuracast-shortcut is-secondary" href="https://translate.google.com/translate?sl=en&tl=pt&u=https://control.radio.owh.com.br/docs/" target="_blank" rel="noopener noreferrer">
                            <span class="azuracast-shortcut-label">Documentação</span>
                            <span class="azuracast-shortcut-meta">Abrir página externa</span>
                        </a>
                </div>
            </div>
            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">{$i18n.onTheAir|escape:'html'}</h2>
                <div class="row azuracast-onair-grid">
                    <div class="col-sm-5 azuracast-onair-artwork">
                        {if $dashboard.artworkUrl}
                            <img class="azuracast-artwork" src="{$dashboard.artworkUrl|escape:'html'}" alt="{$i18n.albumArt|escape:'html'}">
                        {else}
                            <div class="azuracast-artwork-placeholder">{$i18n.noArtwork|escape:'html'}</div>
                        {/if}
                    </div>
                    <div class="col-sm-7 azuracast-onair-details">
                        <div class="azuracast-track-title{if $dashboard.currentTrackTitle == $i18n.noTrackPlaying} is-empty{/if}">{$dashboard.currentTrackTitle|escape:'html'}</div>
                        <p class="azuracast-track-meta">{$dashboard.currentTrackArtist|escape:'html'}</p>
                        <p class="azuracast-copy">
                            {if $dashboard.hasLiveBroadcast && $dashboard.liveStreamerName}
                                {$i18n.liveDjConnected|escape:'html'} {$dashboard.liveStreamerName|escape:'html'}.
                            {elseif $dashboard.upcomingShow}
                                {$i18n.upcomingShow|escape:'html'} {$dashboard.upcomingShow|escape:'html'}.
                            {else}
                                {$i18n.liveMetadataSoon|escape:'html'}
                            {/if}
                        </p>
                    </div>
                </div>
                <div class="azuracast-onair-metrics">
                    <div class="azuracast-kpi">
                        <span class="azuracast-kpi-label">{$i18n.stationCode|escape:'html'}</span>
                        <span class="azuracast-kpi-value">{$dashboard.shortName|escape:'html'}</span>
                    </div>
                    <div class="azuracast-kpi">
                        <span class="azuracast-kpi-label">{$i18n.kpiListeners|escape:'html'}</span>
                        <span class="azuracast-kpi-value">{$dashboard.listeners|escape:'html'}</span>
                    </div>
                </div>
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">{$i18n.stationStatus|escape:'html'}</h2>
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
                <h2 class="azuracast-panel-title">{$i18n.streamOnAir|escape:'html'}</h2>
                <div class="azuracast-stream-box">
                    <div>
                        <span class="azuracast-stream-label">{$i18n.primaryStream|escape:'html'}</span>
                        {if $dashboard.streamUrl}
                            <a class="azuracast-stream-value azuracast-stream-link" href="{$dashboard.streamUrl|escape:'html'}" target="_blank" rel="noopener noreferrer">{$dashboard.streamUrl|escape:'html'}</a>
                        {else}
                            <span class="azuracast-stream-value">{$i18n.noPublicStreamUrl|escape:'html'}</span>
                        {/if}
                    </div>

                    <div style="margin-top:18px;">
                        <span class="azuracast-stream-label">{$i18n.publicPage|escape:'html'}</span>
                        {if $dashboard.publicPageUrl}
                            <a class="azuracast-stream-value azuracast-stream-link" href="{$dashboard.publicPageUrl|escape:'html'}" target="_blank" rel="noopener noreferrer">{$i18n.openPublicStationPage|escape:'html'}</a>
                        {else}
                            <span class="azuracast-stream-value">{$i18n.noPublicPagePublished|escape:'html'}</span>
                        {/if}
                    </div>

                    <div style="margin-top:18px;">
                        <span class="azuracast-stream-label">{$i18n.streamPlayer|escape:'html'}</span>
                        {if $dashboard.playerUrl}
                            <audio class="azuracast-player" controls preload="none" src="{$dashboard.playerUrl|escape:'html'}">
                                {$i18n.audioElementUnsupported|escape:'html'}
                            </audio>
                        {else}
                            <span class="azuracast-stream-value">{$i18n.playerUnavailable|escape:'html'}</span>
                        {/if}
                    </div>

                    <div class="azuracast-stream-actions">
                        <div class="azuracast-stream-tile">
                            <span class="azuracast-stream-label">{$i18n.currentSource|escape:'html'}</span>
                            <span class="azuracast-stream-value">{if $dashboard.hasLiveBroadcast}{$i18n.liveDjSource|escape:'html'}{else}{$i18n.autoDjSource|escape:'html'}{/if}</span>
                        </div>
                        <div class="azuracast-stream-tile">
                            <span class="azuracast-stream-label">{$i18n.status|escape:'html'}</span>
                            <span class="azuracast-stream-value">{$dashboard.statusText|escape:'html'}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">{$i18n.storageUsage|escape:'html'}</h2>
                {if $dashboard.quotaCards}
                    {foreach from=$dashboard.quotaCards item=quota}
                        <div class="azuracast-card" style="min-height:0; margin-bottom:14px;">
                            <span class="azuracast-card-accent is-{$quota.variant|escape:'html'}">{$quota.label|escape:'html'}</span>
                            <div class="azuracast-quota-copy">
                                <div>
                                    <div class="azuracast-card-value" style="font-size:24px;">{$quota.used|escape:'html'}</div>
                                    <p class="azuracast-mini-meta">{$i18n.usedOf|escape:'html'} {$quota.quota|escape:'html'}</p>
                                </div>
                                <div class="text-right">
                                    <div class="azuracast-card-value" style="font-size:24px;">{$quota.usedPercent|escape:'html'}%</div>
                                    {if $quota.meta}
                                        <p class="azuracast-mini-meta">{$quota.meta|escape:'html'} {$i18n.free|escape:'html'}</p>
                                    {/if}
                                </div>
                            </div>
                            <div class="azuracast-meter">
                                <span class="azuracast-meter-fill is-{$quota.variant|escape:'html'}" style="width: {$quota.usedPercent|escape:'html'}%;"></span>
                            </div>
                        </div>
                    {/foreach}
                {else}
                    <p class="azuracast-copy">{$i18n.quotaMetricsUnavailable|escape:'html'}</p>
                {/if}
            </div>

            <div class="azuracast-panel">
                <h2 class="azuracast-panel-title">{$i18n.packageInformation|escape:'html'}</h2>
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