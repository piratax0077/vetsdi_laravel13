@if(isset($registroPresion->presion_pas, $registroPresion->presion_pad, $registroPresion->presion_pam))
    <small class="d-block text-muted">
        PAS {{ $registroPresion->presion_pas }} · PAD {{ $registroPresion->presion_pad }} · PAM {{ $registroPresion->presion_pam }} mmHg
        @if($registroPresion->presion_metodo)
            <br>{{ $registroPresion->presion_metodo }}
        @endif
    </small>
@endif
