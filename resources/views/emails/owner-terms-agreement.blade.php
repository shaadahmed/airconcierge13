<p>
    Owner terms {{ $agreement ? 'accepted' : 'declined' }} by
    {{ $firstname }} ({{ $email }}) at {{ $datetime }}.
</p>
@if (! empty($properties))
    <p>Properties: {{ $properties }}</p>
@endif
@if (! empty($termsOfUseUrl))
    <p><a href="{{ $termsOfUseUrl }}">Terms of use</a></p>
@endif
