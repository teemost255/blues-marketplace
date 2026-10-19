@php $whatsappSupportNumber = preg_replace('/\D+/', '', \App\Models\Setting::get('whatsapp_number', '')); @endphp
@if($whatsappSupportNumber !== '')
<style>
    .wa-btn {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: .625rem;
        background: #25D366;
        color: #fff;
        border-radius: 9999px;
        box-shadow: 0 4px 24px rgba(37,211,102,.45);
        cursor: pointer;
        text-decoration: none;
        padding: .75rem 1.25rem .75rem .875rem;
        font-size: .875rem;
        font-weight: 600;
        transition: transform .2s, box-shadow .2s, padding .35s, max-width .35s;
        max-width: 220px;
        overflow: hidden;
        white-space: nowrap;
    }
    .wa-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 32px rgba(37,211,102,.55);
    }
    .wa-btn svg { flex-shrink: 0; width: 1.5rem; height: 1.5rem; }
    .wa-label { transition: opacity .25s, max-width .35s; max-width: 160px; overflow: hidden; }
    .wa-pulse {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9998;
        width: 56px;
        height: 56px;
        border-radius: 9999px;
        background: rgba(37,211,102,.3);
        animation: wa-ring 2.2s ease-out infinite;
        pointer-events: none;
    }
    @keyframes wa-ring {
        0% { transform: scale(1); opacity: .7; }
        70%, 100% { transform: scale(1.55); opacity: 0; }
    }
    @media (max-width: 480px) {
        .wa-btn { padding: .75rem; max-width: 56px; border-radius: 9999px; }
        .wa-label { max-width: 0; opacity: 0; }
    }
</style>

<div class="wa-pulse" aria-hidden="true"></div>
<a href="https://wa.me/{{ $whatsappSupportNumber }}?text={{ urlencode('Hello! I need support with Blues Marketplace.') }}"
   target="_blank"
   rel="noopener noreferrer"
   class="wa-btn"
   title="Chat with us on WhatsApp"
   aria-label="Chat with Blues Marketplace support on WhatsApp">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.847L.057 23.882l6.204-1.448A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.891 0-3.667-.5-5.208-1.378l-.374-.217-3.872.904.951-3.768-.243-.389A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
    </svg>
    <span class="wa-label">Chat with us</span>
</a>
@endif
