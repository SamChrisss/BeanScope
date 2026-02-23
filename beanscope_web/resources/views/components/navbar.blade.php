<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="background: #FDF8F5; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('Image/BeanScopelogoo.png') }}" alt="BeanScope Logo" style="height: 45px; margin-right: 12px;">
            <span class="fw-bold fs-4">BeanScope</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" data-nav="home">
                        <i class="bi bi-house-fill"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#about" data-nav="about">
                        <i class="bi bi-info-circle-fill"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#features" data-nav="features">
                        <i class="bi bi-star-fill"></i> Features
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('predict.index') ? 'active' : '' }}" href="{{ route('predict.index') }}" data-nav="prediction">
                        <i class="bi bi-magic"></i> Analyze
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
