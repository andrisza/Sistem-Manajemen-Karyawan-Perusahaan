@php
    $toasts = [];

    if (session('success'))
        $toasts[] = ['msg' => session('success'), 'icon' => '✓', 'bg' => '#f0fdf4', 'border' => '#22c55e', 'icon_bg' => '#22c55e', 'text' => '#166534'];

    if (session('error'))
        $toasts[] = ['msg' => session('error'), 'icon' => '✕', 'bg' => '#fef2f2', 'border' => '#ef4444', 'icon_bg' => '#ef4444', 'text' => '#991b1b'];

    if (session('warning'))
        $toasts[] = ['msg' => session('warning'), 'icon' => '!', 'bg' => '#fffbeb', 'border' => '#f59e0b', 'icon_bg' => '#f59e0b', 'text' => '#92400e'];

    if (session('info'))
        $toasts[] = ['msg' => session('info'), 'icon' => 'i', 'bg' => '#eff6ff', 'border' => '#3b82f6', 'icon_bg' => '#3b82f6', 'text' => '#1e40af'];

    $statusVal = session('status');
    if ($statusVal) {
        $statusMap = [
            'profile-updated'          => 'Profil berhasil diperbarui.',
            'password-updated'         => 'Password berhasil diperbarui.',
            'verification-link-sent'   => 'Link verifikasi telah dikirim ke email Anda.',
        ];
        $msg = $statusMap[$statusVal] ?? $statusVal;
        $toasts[] = ['msg' => $msg, 'icon' => 'i', 'bg' => '#eff6ff', 'border' => '#3b82f6', 'icon_bg' => '#3b82f6', 'text' => '#1e40af'];
    }
@endphp

@if(count($toasts))
<div id="toast-container" style="position:fixed;top:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:12px;width:340px;pointer-events:none;">
    @foreach($toasts as $toast)
    <div class="hiro-toast" style="
        background:{{ $toast['bg'] }};
        border-left:4px solid {{ $toast['border'] }};
        border-radius:10px;
        padding:14px 16px;
        display:flex;
        align-items:center;
        gap:12px;
        box-shadow:0 8px 24px rgba(0,0,0,0.13),0 2px 8px rgba(0,0,0,0.08);
        pointer-events:all;
        opacity:1;
        transform:translateX(0);
        transition:opacity 0.35s ease,transform 0.35s ease;
    ">
        <div style="
            width:28px;height:28px;
            background:{{ $toast['icon_bg'] }};
            border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            color:white;font-weight:700;font-size:13px;
            flex-shrink:0;font-family:Arial,sans-serif;
        ">{{ $toast['icon'] }}</div>
        <span style="
            color:{{ $toast['text'] }};
            font-size:0.875rem;font-weight:500;
            flex:1;font-family:'Poppins',Arial,sans-serif;
            line-height:1.45;
        ">{{ $toast['msg'] }}</span>
        <button onclick="this.closest('.hiro-toast').remove()" style="
            background:none;border:none;
            color:#9ca3af;cursor:pointer;
            font-size:20px;line-height:1;
            padding:0;margin-left:4px;flex-shrink:0;
        ">&times;</button>
    </div>
    @endforeach
</div>
<script>
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.hiro-toast').forEach(function(el){
            setTimeout(function(){
                el.style.opacity = '0';
                el.style.transform = 'translateX(110%)';
                setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 360);
            }, 2000);
        });
    });
})();
</script>
@endif
