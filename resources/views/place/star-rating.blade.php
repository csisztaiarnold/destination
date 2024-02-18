<div class="rating @if(Auth::id() !== NULL) logged-in @endif">
    <span class="material-icons vote @if($single_place->rating > 0.5) active @endif" data-vote="1">star_rate</span>
    <span class="material-icons vote @if($single_place->rating > 1.5) active @endif" data-vote="2">star_rate</span>
    <span class="material-icons vote @if($single_place->rating > 2.5) active @endif" data-vote="3">star_rate</span>
    <span class="material-icons vote @if($single_place->rating > 3.5) active @endif" data-vote="4">star_rate</span>
    <span class="material-icons vote @if($single_place->rating > 4.5) active @endif" data-vote="5">star_rate</span>
    <span class="material-icons done">done</span>
</div>

@if(Auth::id() !== NULL && isset($user_vote))
    <div class="rating your-vote">
        Your vote:<br />
        <span class="material-icons @if($user_vote > 0) active @endif">star_rate</span>
        <span class="material-icons @if($user_vote > 1) active @endif">star_rate</span>
        <span class="material-icons @if($user_vote > 2) active @endif">star_rate</span>
        <span class="material-icons @if($user_vote > 3) active @endif">star_rate</span>
        <span class="material-icons @if($user_vote > 4) active @endif">star_rate</span>
        <span class="material-icons done">done</span>
    </div>
@endif
