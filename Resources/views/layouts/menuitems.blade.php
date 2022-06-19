<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-tachometer-alt" aria-hidden="true"></i>
    <p> Dashboard <i class="fas fa-angle-left right" aria-hidden="true"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-user-cog" aria-hidden="true"></i>
        <p> C4 <i class="fas fa-angle-left right" aria-hidden="true"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="{{ route('DashboardC4') }}" class="nav-link">
            <i class="fas fa-chalkboard-teacher nav-icon" aria-hidden="true"></i>
            <p>Real Time</p>
          </a>
        </li>
      </ul>
    </li>
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-user-friends" aria-hidden="true"></i>
        <p> T2 <i class="fas fa-angle-left right" aria-hidden="true"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="{{ route('DashboardT2') }}" class="nav-link">
            <i class="fas fa-chalkboard-teacher nav-icon" aria-hidden="true"></i>
            <p>Real Time</p>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</li>