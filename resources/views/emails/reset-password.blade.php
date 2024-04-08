@component('mail::message')

<h3 style="text-align: center">
    <a href="{{ $clientUrl }}/reset-password/{{ $token }}">אנא לחצ.י כאן כדי לאפס את הסיסמה</a>
</h3>


@endcomponent
