<nav class="navbar navbar-expand-lg bg-white">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
            <img src="{{ asset('images/logo.png') }}" alt="myBox Logo" height="36" class="me-2">
            myBox
        </a>

        <!-- TOGGLER -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- LEFT MENU -->
            <ul class="navbar-nav me-auto ms-4">
                <li class="nav-item">
                    <a class="nav-link fw-semibold active" href="#">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="{{ route('history.index') }}">History</a>
                </li>
            </ul>

            <!-- RIGHT MENU -->
            <div class="d-flex align-items-center gap-3">

                <!-- NOTIFICATION -->
                <a href="#" class="position-relative text-secondary">
                    <i class="bi bi-bell fs-4"></i>

                    <!-- BADGE (STATIC / DUMMY) -->
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </a>

                <!-- PROFILE -->
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center"
                         style="width:38px;height:38px;">
                        👤
                    </div>
                    <span class="fw-semibold text-secondary">
                        Kadek Artika
                    </span>
                </div>

            </div>
        </div>
    </div>
</nav>
