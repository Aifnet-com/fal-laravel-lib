<ul class="nav nav-tabs  sub-menu" role="tablist">
    <li @class(['active' => Route::is('fal.admin')])>
        <a href="{{ route('fal.admin') }}">Dashboard</a>
    </li>
    <li @class(['active' => Route::is('fal.admin.statistics')])>
        <a href="{{ route('fal.admin.statistics') }}">Statistics</a>
    </li>
</ul>