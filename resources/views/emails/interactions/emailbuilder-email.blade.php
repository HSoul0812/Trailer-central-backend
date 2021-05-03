<!-- BEGIN EMAIL BUILDER TEMPLATE! -->
{!! $body !!}
<!-- END EMAIL BUILDER TEMPLATE! -->


<!-- BEGIN UNSUBSCRIBE LINK -->
@if (!empty($unsubscribeLink))
    <br /><br />
    <p>To unsubscribe from this mailing list <a href="{{ $unsubscribeLink }}">click here.</a></p>
@endif
<!-- END UNSUBSCRIBE LINK -->